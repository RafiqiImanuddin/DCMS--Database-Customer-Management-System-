import json
import sys
from pathlib import Path
import pandas as pd

root = Path(__file__).resolve().parent
source = Path(sys.argv[1])
period = sys.argv[2] if len(sys.argv) > 2 else ""

try:
    if source.suffix.lower() == ".csv":
        df = pd.read_csv(source)
    else:
        df = pd.read_excel(source, sheet_name=0)
except Exception as exc:
    print(json.dumps({"success": False, "message": str(exc)}))
    raise SystemExit(1)

required = ["Cabang", "Posting Date", "Document Number", "Customer/Vendor Code"]
missing = [column for column in required if column not in df.columns]
if "Total" not in df.columns and "Harga Jual" not in df.columns:
    missing.append("Total atau Harga Jual")
if missing:
    print(json.dumps({"success": False, "message": "Kolom wajib tidak ditemukan", "missing": missing}))
    raise SystemExit(1)

df["Posting Date"] = pd.to_datetime(df["Posting Date"], errors="coerce")
df = df[df["Posting Date"].notna()].copy()
if "Total" in df.columns:
    df["_total"] = pd.to_numeric(df["Total"], errors="coerce").fillna(0)
else:
    quantity = pd.to_numeric(df["Quantity"], errors="coerce").fillna(0) if "Quantity" in df.columns else 1
    selling_price = pd.to_numeric(df["Harga Jual"], errors="coerce").fillna(0)
    df["_total"] = quantity * selling_price
if "Name" in df.columns:
    df["_customer_name"] = df["Name"].fillna("").astype(str).str.strip()
else:
    df["_customer_name"] = ""

def classify_device(row):
    text = " ".join(str(row.get(column, "")) for column in ("Brand", "Dscription", "Item/Service Description") if column in row.index)
    text = text.upper()
    rules = [
        ("APPLE / IPHONE", ("IPHONE", "APPLE")),
        ("SAMSUNG", ("SAMSUNG", "GALAXY")),
        ("XIAOMI", ("XIAOMI", "MI ", "REDMI", "POCO")),
        ("OPPO", ("OPPO",)),
        ("VIVO", ("VIVO",)),
        ("REALME", ("REALME",)),
        ("INFINIX", ("INFINIX",)),
        ("TECNO", ("TECNO",)),
        ("HUAWEI", ("HUAWEI",)),
        ("HONOR", ("HONOR",)),
        ("ASUS", ("ASUS", "ROG")),
        ("NOKIA", ("NOKIA",)),
        ("LENOVO", ("LENOVO",)),
    ]
    for device, keywords in rules:
        if any(keyword in text for keyword in keywords):
            return device
    return "LAINNYA / TAK TERKLASIFIKASI"

df["_device"] = df.apply(classify_device, axis=1)

device_spend = df.groupby(["Cabang", "Customer/Vendor Code", "_device"], as_index=False, dropna=False)["_total"].sum()
phone_candidates = [c for c in df.columns if any(k in str(c).lower() for k in ("no hp", "whatsapp", "telepon", "telephone", "phone", "mobile", "contact"))]
phone_col = phone_candidates[0] if phone_candidates else None
if phone_col:
    df["_phone"] = df[phone_col].astype(str).str.replace(r"[^0-9]", "", regex=True)
    df.loc[df["_phone"].isin(["", "nan"]), "_phone"] = None
else:
    df["_phone"] = None
max_date = df["Posting Date"].max()

invoice_agg = {
    "last_date": ("Posting Date", "max"),
    "invoice_total": ("_total", "sum"),
    "sales": ("Sales Employee Name", "last") if "Sales Employee Name" in df.columns else ("SlpName", "last") if "SlpName" in df.columns else ("Name", "last") if "Name" in df.columns else ("Document Number", "first"),
    "category": ("Group Name", "first") if "Group Name" in df.columns else ("Group", "first") if "Group" in df.columns else ("ItmsGrpNam", "first") if "ItmsGrpNam" in df.columns else ("Document Number", "first"),
    "phone": ("_phone", "first"),
    "customer_name": ("_customer_name", "first"),
}
invoice = df.groupby(["Cabang", "Customer/Vendor Code", "Document Number"], as_index=False, dropna=False).agg(**invoice_agg)
invoice = invoice[invoice["invoice_total"] > 0]
rows = []

for (branch, code), group in invoice.groupby(["Cabang", "Customer/Vendor Code"], sort=False, dropna=False):
    name_values = group["customer_name"].dropna().astype(str)
    name_values = name_values[~name_values.str.strip().str.lower().isin(["", "nan", "none"])]
    name = name_values.iloc[0].strip() if len(name_values) else ""
    recency = int((max_date - group["last_date"].max()).days)
    frequency = int(group["Document Number"].nunique())
    spend = float(group["invoice_total"].sum())
    avg = float(group["invoice_total"].mean())
    last = group["last_date"].max()
    first = group["last_date"].min()
    category_totals = group.groupby("category", dropna=False)["invoice_total"].sum()
    top_category = category_totals.sort_values(ascending=False).index[0] if len(category_totals) else "-"
    sales_totals = group.groupby("sales", dropna=False)["invoice_total"].sum()
    top_sales = sales_totals.sort_values(ascending=False).index[0] if len(sales_totals) else "-"

    score = 35 if frequency >= 5 else 22 if frequency >= 3 else 12 if frequency >= 2 else 3
    score += 25 if recency <= 3 else 20 if recency <= 7 else 12 if recency <= 14 else 0
    score += 20 if spend >= 5000000 else 12 if spend >= 1000000 else 5
    score = min(score, 100)
    status = "Siap repeat" if score >= 65 else "Potensi repeat" if score >= 45 else "Reaktivasi"

    if str(code).startswith(("C9", "C910", "C990", "C300", "C100")):
        status = "Perlu verifikasi"
        score = min(score, 55)

    phone_values = group["phone"].dropna().astype(str) if "phone" in group else pd.Series(dtype=str)
    phone = next((value for value in phone_values if value and value.lower() != "nan"), "")
    device_rows = device_spend[(device_spend["Cabang"] == branch) & (device_spend["Customer/Vendor Code"] == code)].copy()
    device_rows = device_rows[device_rows["_total"] > 0].sort_values("_total", ascending=False)
    device_total = device_rows["_total"].sum()
    devices = [
        {"name": str(row["_device"]), "percent": round(float(row["_total"]) / device_total * 100, 1)}
        for _, row in device_rows.iterrows()
    ] if device_total else []
    top_device = devices[0]["name"] if devices else "LAINNYA / TAK TERKLASIFIKASI"
    rows.append({
        "branch": str(branch), "code": str(code), "name": str(name).strip(),
        "frequency": frequency, "spend": round(spend), "avg": round(avg),
        "recency": recency, "score": int(score), "status": status,
        "last": last.strftime("%Y-%m-%d"), "first": first.strftime("%Y-%m-%d"),
        "category": str(top_category), "sales": str(top_sales),
        "suggested_days": 7 if recency <= 7 else 14 if recency <= 14 else 30,
        "phone": phone,
        "devices": devices,
        "top_device": top_device,
    })

branches = sorted(df["Cabang"].dropna().astype(str).unique().tolist())
branch_summary = []
for branch in branches:
    subset = [row for row in rows if row["branch"] == branch]
    if not subset:
        continue
    branch_summary.append({
        "branch": branch,
        "customers": len(subset),
        "orders": sum(row["frequency"] for row in subset),
        "spend": sum(row["spend"] for row in subset),
        "high_priority": sum(row["score"] >= 65 for row in subset),
        "avg_score": round(sum(row["score"] for row in subset) / len(subset), 1),
    })

daily = df.groupby(["Cabang", "Posting Date"], as_index=False)["_total"].sum().rename(columns={"_total": "total"})
trend = []
for branch in branches:
    subset = daily[daily["Cabang"] == branch].sort_values("Posting Date")
    trend.append({
        "branch": branch,
        "series": [{"date": value.strftime("%Y-%m-%d"), "total": round(float(total))} for value, total in zip(subset["Posting Date"], subset["total"])]
    })

result = {
    "generated_at": pd.Timestamp.now().strftime("%Y-%m-%d %H:%M:%S"),
    "data_through": max_date.strftime("%Y-%m-%d"),
    "source": source.name,
    "period": period,
    "branches": branches,
    "branch_summary": branch_summary,
    "trend": trend,
    "customers": rows,
}
output = root / "assets" / "data.json"
output.parent.mkdir(parents=True, exist_ok=True)
output.write_text(json.dumps(result, ensure_ascii=False), encoding="utf-8")
print(json.dumps({"success": True, "rows": len(df), "customers": len(rows), "branches": len(branches), "period": period}))
