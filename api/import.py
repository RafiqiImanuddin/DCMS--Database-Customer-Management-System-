from fastapi import FastAPI, UploadFile, File, Form
import subprocess
import tempfile
import json
import os
from pathlib import Path

app = FastAPI()

@app.post("/api/import")
async def import_file(file: UploadFile = File(...), period: str = Form(...)):
    allowed = {"xlsx", "xls", "csv"}
    suffix = Path(file.filename or "").suffix.lower().lstrip(".")
    if suffix not in allowed:
        return {"success": False, "message": "Format harus XLSX, XLS, atau CSV"}

    root = Path(__file__).resolve().parents[1]
    content = await file.read()
    if len(content) > 100 * 1024 * 1024:
        return {"success": False, "message": "Ukuran file maksimal 100 MB"}

    if not __import__("re").match(r"^\d{4}-\d{2}$", period or ""):
        return {"success": False, "message": "Periode harus YYYY-MM"}

    fd, source_path = tempfile.mkstemp(suffix="." + suffix)
    try:
        with os.fdopen(fd, "wb") as target:
            target.write(content)
        script = root / "process_import.py"
        completed = subprocess.run(
            ["python", str(script), source_path, period],
            capture_output=True, text=True, timeout=240
        )
        output = completed.stdout.strip()
        result = json.loads(output) if output else {"success": False, "message": "Output pemrosesan tidak valid"}
        if completed.returncode != 0 or not result.get("success"):
            return {"success": False, "message": result.get("message", "Gagal memproses file"), "detail": completed.stderr or output}
        data_path = root / "assets" / "data.json"
        if data_path.exists():
            result["data"] = json.loads(data_path.read_text(encoding="utf-8"))
        return result
    except subprocess.TimeoutExpired:
        return {"success": False, "message": "Pemrosesan file timeout"}
    except Exception as error:
        return {"success": False, "message": "Gagal memproses file", "detail": str(error)}
    finally:
        try:
            os.unlink(source_path)
        except OSError:
            pass
