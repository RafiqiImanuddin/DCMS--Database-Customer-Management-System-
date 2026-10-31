import json
import sys
import os
from pathlib import Path
import pandas as pd
import pymysql

root = Path(__file__).resolve().parent
source = Path(sys.argv[1])
period = sys.argv[2] if len(sys.argv) > 2 else ""
source_filename = sys.argv[3] if len(sys.argv) > 3 else source.name

column_map = {
    "Cabang": "cabang",
    "Info": "info",
    "Keterangan": "keterangan",
    "Group": "group_name",
    "Posting Date": "posting_date",
    "Waktu": "waktu",
    "Document Number": "document_number",
    "Customer/Vendor Code": "customer_vendor_code",
    "Customer/Vendor Name": "customer_vendor_name",
    "ItemCode": "item_code",
    "Dscription": "description",
    "ItmsGrpNam": "item_group_name",
    "Brand": "brand",
    "WhsCode": "warehouse_code",
    "Quantity": "quantity",
    "Harga Jual": "selling_price",
    "Harga Sebelum Potongan": "price_before_discount",
    "Selisih/Total Potongan": "discount_difference",
    "P_Bonus": "p_bonus",
    "P_Voucher": "p_voucher",
    "P_Nego": "p_nego",
    "P_Leasing": "p_leasing",
    "P_Program": "p_program",
    "P_EDC": "p_edc",
    "P_OTORISASI POSVIT": "p_authorization_posvit",
    "Price List": "price_list",
    "Remarks": "remarks",
    "SC/HL Code": "schl_code",
    "SlpName": "sales_employee_name",
    "PROMO": "promo",
    "User Input": "user_input",
    "No HP Customer": "customer_phone",
    "HARGA MODAL": "cost_price",
    "GROSS PROFIT": "gross_profit",
    "RESELLER": "reseller",
    "Beban Subsidi Leasing": "leasing_subsidy",
    "TRANSAKSI CUSTOMER": "customer_transaction",
    "Name": "name",
    "NoReff": "reference_number",
    "NO_PO": "purchase_order_number",
}


def clean_text(value):
    if value is None or pd.isna(value):
        return None
    text = str(value).strip()
    return text if text and text.lower() not in ("nan", "none", "nat") else None


def clean_number(value, default=0):
    if value is None or pd.isna(value):
        return default
    try:
        return float(value)
    except (TypeError, ValueError):
        return default


def save_cross_selling_to_mysql(raw_df):
    host = os.getenv("DB_HOST", "127.0.0.1")
    port = int(os.getenv("DB_PORT", "3306"))
    database = os.getenv("DB_NAME", "dcms")
    user = os.getenv("DB_USER", "root")
    password = os.getenv("DB_PASS", "")

    connection = pymysql.connect(
        host=host,
        port=port,
        user=user,
        password=password,
        database=database,
        charset="utf8mb4",
        autocommit=False,
        connect_timeout=15,
    )

    database_columns = list(column_map.values())
    insert_columns = ["source_period"] + database_columns + ["source_file", "source_row"]
    placeholders = ", ".join(["%s"] * len(insert_columns))
    column_names = ", ".join(f"`{name}`" for name in insert_columns)
    insert_sql = f"INSERT INTO cross_selling ({column_names}) VALUES ({placeholders})"

    rows = []
    for source_row, (_, source) in enumerate(raw_df.iterrows(), start=2):
        values = [period]
        for source_name, database_name in column_map.items():
            value = source.get(source_name)
            if source_name == "Posting Date":
                posting_date = pd.to_datetime(value, errors="coerce")
                values.append(posting_date.strftime("%Y-%m-%d") if not pd.isna(posting_date) else None)
            elif source_name == "Document Number":
                values.append(clean_text(value))
            elif source_name == "No HP Customer":
                phone = clean_text(value)
                values.append(phone.replace(".0", "") if phone else None)
            elif source_name in {
                "Quantity", "Harga Jual", "Harga Sebelum Potongan",
                "Selisih/Total Potongan", "P_Bonus", "P_Voucher", "P_Nego",
                "P_Leasing", "P_Program", "P_EDC", "P_OTORISASI POSVIT",
                "HARGA MODAL", "GROSS PROFIT", "Beban Subsidi Leasing",
            }:
                values.append(clean_number(value))
            else:
                values.append(clean_text(value))
        values.extend([source_filename, source_row])
        rows.append(values)

    try:
        with connection.cursor() as cursor:
            cursor.execute(
                "DELETE FROM cross_selling WHERE source_period = %s AND source_file = %s",
                (period, source_filename),
            )
            for start in range(0, len(rows), 500):
                cursor.executemany(insert_sql, rows[start:start + 500])
        connection.commit()
        return len(rows)
    except Exception:
        connection.rollback()
        raise
    finally:
        connection.close()

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

def classify_product_category(item_name, item_code=""):
    text = f"{item_name} {item_code}".upper()
    if any(word in text for word in ("ANTI GORES", "ANTI-GORES", "ANTIGORES", "TEMPERED", "SCREEN PROTECTOR", "GLASS", "HYDRO GEL", "HYDROGEL", "KACA")):
        return "Anti Gores"
    if any(word in text for word in ("CASE", "CASING", "CESAN", "CESING", "COVER", "SOFTCASE", "SOFT CASE", "HARDCASE", "HARD CASE", "SILICON", "SILIKON", "BUMPER")):
        return "Case"
    return "Lainnya"

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

    purchase_rows = df[(df["Cabang"] == branch) & (df["Customer/Vendor Code"] == code)].copy()
    purchase_items = []
    if len(purchase_rows):
        purchase_name = "Dscription" if "Dscription" in purchase_rows.columns else "Item/Service Description" if "Item/Service Description" in purchase_rows.columns else None
        purchase_code = "ItemCode" if "ItemCode" in purchase_rows.columns else "Item No." if "Item No." in purchase_rows.columns else None
        if purchase_name or purchase_code:
            purchase_rows["_item_name"] = purchase_rows[purchase_name].fillna("").astype(str).str.strip() if purchase_name else ""
            purchase_rows["_item_code"] = purchase_rows[purchase_code].fillna("").astype(str).str.strip() if purchase_code else ""
            purchase_rows["_quantity"] = pd.to_numeric(purchase_rows["Quantity"], errors="coerce").fillna(0) if "Quantity" in purchase_rows.columns else 0
            purchase_rows["_exclude_item"] = (
                purchase_rows["_item_code"].str.upper().str.startswith(("JAS.", "INS.")) |
                purchase_rows["_item_name"].str.upper().str.contains("JASA|SERVICE|GMART CARE|INSURANCE|ASURANSI|GARANSI", regex=True, na=False)
            )
            grouped_purchases = purchase_rows[(purchase_rows["_quantity"] > 0) & (~purchase_rows["_exclude_item"])].groupby(["_item_code", "_item_name", "Posting Date", "Document Number"], as_index=False).agg(quantity=("_quantity", "sum"), spend=("_total", "sum"))
            grouped_purchases = grouped_purchases.sort_values("Posting Date", ascending=False)
            for _, purchase in grouped_purchases.head(20).iterrows():
                item_name = str(purchase["_item_name"] or purchase["_item_code"] or "").strip()
                if not item_name:
                    continue
                purchase_items.append({
                    "item_name": item_name,
                    "item_code": str(purchase["_item_code"] or ""),
                    "category": classify_product_category(item_name, str(purchase["_item_code"] or "")),
                    "quantity": round(float(purchase["quantity"]), 2),
                    "spend": round(float(purchase["spend"])),
                    "date": purchase["Posting Date"].strftime("%Y-%m-%d") if not pd.isna(purchase["Posting Date"]) else "",
                })
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
        "previous_items": purchase_items,
    })

item_name_col = "Dscription" if "Dscription" in df.columns else "Item/Service Description" if "Item/Service Description" in df.columns else None
item_code_col = "ItemCode" if "ItemCode" in df.columns else "Item No." if "Item No." in df.columns else None
item_rows = df.copy()
if item_name_col:
    item_rows["_item_name"] = item_rows[item_name_col].fillna("").astype(str).str.strip()
else:
    item_rows["_item_name"] = ""
if item_code_col:
    item_rows["_item_code"] = item_rows[item_code_col].fillna("").astype(str).str.strip()
else:
    item_rows["_item_code"] = ""
item_rows["_quantity"] = pd.to_numeric(item_rows["Quantity"], errors="coerce").fillna(0) if "Quantity" in item_rows.columns else 0
item_rows["_exclude_item"] = (
    item_rows["_item_code"].str.upper().str.startswith(("JAS.", "INS.")) |
    item_rows["_item_name"].str.upper().str.contains("JASA|SERVICE|GMART CARE|INSURANCE|ASURANSI|GARANSI", regex=True, na=False)
)
item_summary = item_rows[(item_rows["_quantity"] > 0) & (~item_rows["_exclude_item"])].groupby(["Cabang", "_item_code", "_item_name"], as_index=False, dropna=False).agg(quantity=("_quantity", "sum"), spend=("_total", "sum"), transactions=("Document Number", "nunique"))
item_summary["quantity"] = item_summary["quantity"].round(2)
item_summary["spend"] = item_summary["spend"].round()
item_summary["item_name"] = item_summary["_item_name"]
item_summary["item_code"] = item_summary["_item_code"]
item_summary["branch"] = item_summary["Cabang"]
item_summary = item_summary[["branch", "item_code", "item_name", "quantity", "spend", "transactions"]]
item_ranking = []
for branch in sorted(df["Cabang"].dropna().astype(str).unique().tolist()):
    rows_for_branch = item_summary[item_summary["branch"] == branch].sort_values(["quantity", "spend"], ascending=False).head(20)
    item_ranking.extend(rows_for_branch.to_dict("records"))

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

monthly_result = {
    "generated_at": pd.Timestamp.now().strftime("%Y-%m-%d %H:%M:%S"),
    "data_through": max_date.strftime("%Y-%m-%d"),
    "source": source.name,
    "period": period,
    "branches": branches,
    "branch_summary": branch_summary,
    "trend": trend,
    "customers": rows,
    "item_ranking": item_ranking,
}

history_dir = root / "uploads" / "DCMS" / "history"
history_dir.mkdir(parents=True, exist_ok=True)
safe_period = str(period or "unknown-period").replace("..", "_").replace("/", "-").replace("\\", "-")
(history_dir / f"{safe_period}.json").write_text(json.dumps(monthly_result, ensure_ascii=False), encoding="utf-8")

history_files = sorted(history_dir.glob("*.json"))
history_results = []
for history_file in history_files:
    try:
        history_results.append(json.loads(history_file.read_text(encoding="utf-8")))
    except Exception:
        continue

data_through = max((item.get("data_through", "") for item in history_results), default=max_date.strftime("%Y-%m-%d"))
combined_customers = []
customer_groups = {}
for item in history_results:
    for customer in item.get("customers", []):
        key = (str(customer.get("branch", "")), str(customer.get("code", "")))
        customer_groups.setdefault(key, []).append(customer)

max_overall_date = pd.to_datetime(data_through, errors="coerce")
for (branch, code), records in customer_groups.items():
    frequency = sum(int(record.get("frequency", 0)) for record in records)
    spend = sum(float(record.get("spend", 0)) for record in records)
    last_values = [pd.to_datetime(record.get("last"), errors="coerce") for record in records]
    first_values = [pd.to_datetime(record.get("first"), errors="coerce") for record in records]
    valid_last = [value for value in last_values if not pd.isna(value)]
    valid_first = [value for value in first_values if not pd.isna(value)]
    last = max(valid_last).strftime("%Y-%m-%d") if valid_last else ""
    first = min(valid_first).strftime("%Y-%m-%d") if valid_first else ""
    recency = int((max_overall_date - pd.to_datetime(last)).days) if last and not pd.isna(max_overall_date) else 0

    score = 35 if frequency >= 5 else 22 if frequency >= 3 else 12 if frequency >= 2 else 3
    score += 25 if recency <= 3 else 20 if recency <= 7 else 12 if recency <= 14 else 0
    score += 20 if spend >= 5000000 else 12 if spend >= 1000000 else 5
    score = min(score, 100)
    status = "Siap repeat" if score >= 65 else "Potensi repeat" if score >= 45 else "Reaktivasi"
    if str(code).startswith(("C9", "C910", "C990", "C300", "C100")):
        status = "Perlu verifikasi"
        score = min(score, 55)

    latest_record = sorted(records, key=lambda record: record.get("last", ""), reverse=True)[0]
    categories = [record.get("category") for record in records if record.get("category")]
    sales = [record.get("sales") for record in records if record.get("sales")]
    phones = [str(record.get("phone", "")).strip() for record in records if str(record.get("phone", "")).strip()]
    previous_items = []
    seen_previous_items = set()
    for record in sorted(records, key=lambda record: record.get("last", ""), reverse=True):
        for purchase in record.get("previous_items", []) or []:
            item_name = str(purchase.get("item_name", "") or "").strip()
            item_key = (item_name.upper(), str(purchase.get("item_code", "") or ""))
            if item_name and item_key not in seen_previous_items:
                seen_previous_items.add(item_key)
                previous_items.append(purchase)

    device_scores = {}
    for record in records:
        for device in record.get("devices", []):
            name = str(device.get("name", "LAINNYA / TAK TERKLASIFIKASI"))
            device_scores[name] = device_scores.get(name, 0) + float(device.get("percent", 0))
    devices = [{"name": name, "percent": round(percent / len(records), 1)} for name, percent in sorted(device_scores.items(), key=lambda item: item[1], reverse=True)]
    top_device = devices[0]["name"] if devices else "LAINNYA / TAK TERKLASIFIKASI"

    combined_customers.append({
        "branch": branch,
        "code": code,
        "name": str(latest_record.get("name", "") or ""),
        "frequency": frequency,
        "spend": round(spend),
        "avg": round(spend / frequency if frequency else 0),
        "recency": recency,
        "score": score,
        "status": status,
        "last": last,
        "first": first,
        "category": latest_record.get("category", categories[0] if categories else "-"),
        "sales": latest_record.get("sales", sales[-1] if sales else "-"),
        "suggested_days": 7 if recency <= 7 else 14 if recency <= 14 else 30,
        "phone": phones[-1] if phones else "",
        "devices": devices,
        "top_device": top_device,
        "previous_items": previous_items[:20],
    })

combined_customers.sort(key=lambda customer: (customer["branch"], -customer["score"]))
combined_branches = sorted({branch for branch, _ in customer_groups})
combined_branch_summary = []
for branch in combined_branches:
    subset = [customer for customer in combined_customers if customer["branch"] == branch]
    combined_branch_summary.append({
        "branch": branch,
        "customers": len(subset),
        "orders": sum(customer["frequency"] for customer in subset),
        "spend": sum(customer["spend"] for customer in subset),
        "high_priority": sum(customer["score"] >= 65 for customer in subset),
        "avg_score": round(sum(customer["score"] for customer in subset) / len(subset), 1),
    })

combined_trend_map = {}
for item in history_results:
    for branch_trend in item.get("trend", []):
        branch_name = branch_trend.get("branch")
        for point in branch_trend.get("series", []):
            date = point.get("date")
            combined_trend_map[(branch_name, date)] = combined_trend_map.get((branch_name, date), 0) + float(point.get("total", 0))
combined_item_map = {}
for item in history_results:
    for ranking in item.get("item_ranking", []):
        branch_name = str(ranking.get("branch", ""))
        item_code = str(ranking.get("item_code", "") or "")
        item_name = str(ranking.get("item_name", "") or "")
        key = (branch_name, item_code, item_name)
        current = combined_item_map.setdefault(key, {"quantity": 0, "spend": 0, "transactions": 0})
        current["quantity"] += float(ranking.get("quantity", 0) or 0)
        current["spend"] += float(ranking.get("spend", 0) or 0)
        current["transactions"] += int(ranking.get("transactions", 0) or 0)
combined_item_ranking = []
for branch in combined_branches:
    branch_items = []
    for (branch_name, item_code, item_name), totals in combined_item_map.items():
        if branch_name == branch:
            branch_items.append({
                "branch": branch_name,
                "item_code": item_code,
                "item_name": item_name,
                "quantity": round(totals["quantity"], 2),
                "spend": round(totals["spend"]),
                "transactions": totals["transactions"],
            })
    branch_items.sort(key=lambda row: (-row["quantity"], -row["spend"]))
    combined_item_ranking.extend(branch_items[:20])

combined_trend = []
for branch in combined_branches:
    series = [
        {"date": date, "total": round(total)}
        for (branch_name, date), total in sorted(combined_trend_map.items(), key=lambda item: (item[0][0], item[0][1]))
        if branch_name == branch
    ]
    combined_trend.append({"branch": branch, "series": series})

# Generate practical next-best-item suggestions from branch demand and device affinity.
for customer in combined_customers:
    branch_items = [row for row in combined_item_ranking if row["branch"] == customer["branch"]]
    device = customer.get("top_device", "LAINNYA / TAK TERKLASIFIKASI")
    device_terms = {
        "APPLE / IPHONE": ("APPLE", "IPHONE"), "SAMSUNG": ("SAMSUNG", "GALAXY"),
        "XIAOMI": ("XIAOMI", "REDMI", "POCO", "MI "), "OPPO": ("OPPO",),
        "VIVO": ("VIVO",), "REALME": ("REALME",), "INFINIX": ("INFINIX",),
        "TECNO": ("TECNO",), "HUAWEI": ("HUAWEI",), "HONOR": ("HONOR",),
        "ASUS": ("ASUS", "ROG"), "NOKIA": ("NOKIA",), "LENOVO": ("LENOVO",),
    }.get(device, ())
    scored = []
    for row in branch_items:
        text = f"{row.get('item_name', '')} {row.get('item_code', '')}".upper()
        device_match = bool(device_terms) and any(term in text for term in device_terms)
        confidence = min(95, 55 + (3 if device_match else 0) + (2 if customer.get("score", 0) >= 65 else 0))
        confidence = int(confidence + min(10, float(row.get("transactions", 0))))
        scored.append({**row, "device_match": device_match, "confidence": min(confidence, 95),
                       "reason": "Sesuaikan tipe perangkat" if device_match else "Produk laris di cabang"})
    scored.sort(key=lambda row: (-row["device_match"], -row["confidence"], -row["quantity"]))
    customer["recommended_items"] = scored[:3]
    customer["recommended_item"] = scored[0]["item_name"] if scored else "Konfirmasi kebutuhan pelanggan"

result = {
    "generated_at": pd.Timestamp.now().strftime("%Y-%m-%d %H:%M:%S"),
    "data_through": data_through,
    "source": " + ".join(item.get("source", "") for item in history_results if item.get("source")),
    "period": " + ".join(sorted({item.get("period", "") for item in history_results if item.get("period")})),
    "branches": combined_branches,
    "branch_summary": combined_branch_summary,
    "trend": combined_trend,
    "customers": combined_customers,
    "item_ranking": combined_item_ranking,
    "history_periods": sorted({item.get("period", "") for item in history_results if item.get("period")}),
}
output = root / "assets" / "data.json"
output.parent.mkdir(parents=True, exist_ok=True)
output.write_text(json.dumps(result, ensure_ascii=False), encoding="utf-8")
print(json.dumps({"success": True, "rows": len(df), "customers": len(combined_customers), "branches": len(combined_branches), "period": period, "history_periods": result["history_periods"]}))
