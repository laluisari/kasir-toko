import base64
import json
import os
import sys

from flask import Flask, jsonify, request

sys.path.append(os.path.dirname(os.path.abspath(__file__)))

import win32print

BASE_DIR = os.path.dirname(os.path.abspath(__file__))


def load_config():
    path = os.path.join(BASE_DIR, "config.json")
    try:
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)
    except FileNotFoundError:
        return {}
    except json.JSONDecodeError:
        print("WARN: config.json corrupt, fallback ke default")
        return {}


CONFIG = load_config()

PRINTER_NAME = CONFIG.get("printer") or "Generic / Text Only"
HOST = CONFIG.get("host", "127.0.0.1")
PORT = int(CONFIG.get("port", 8765))

app = Flask(__name__)


def resolve_printer(name):
    if name and name.strip():
        return name.strip()
    candidates = []
    try:
        for _, _, printer, _ in win32print.EnumPrinters(win32print.PRINTER_ENUM_LOCAL):
            candidates.append(printer)
    except Exception:
        candidates = []
    return candidates[0] if candidates else None


@app.after_request
def add_cors(response):
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Access-Control-Allow-Methods"] = "POST, GET, OPTIONS"
    response.headers["Access-Control-Allow-Headers"] = "Content-Type"
    return response


@app.get("/health")
def health():
    printers = []
    try:
        for _, _, printer, _ in win32print.EnumPrinters(win32print.PRINTER_ENUM_LOCAL):
            printers.append(printer)
    except Exception as e:
        return jsonify({"success": False, "error": str(e), "printers": []}), 500

    return jsonify({
        "success": True,
        "printer": resolve_printer(PRINTER_NAME),
        "printers": printers,
    })


@app.post("/print")
def print_receipt():
    data = request.get_json(silent=True) or {}

    printer_name = resolve_printer(data.get("printer") or PRINTER_NAME)

    if not printer_name:
        return jsonify({"success": False, "error": "Tidak ada printer terpasang."}), 422

    if "base64" in data:
        try:
            payload = base64.b64decode(data["base64"])
        except Exception:
            return jsonify({"success": False, "error": "base64 tidak valid."}), 422
    else:
        content = data.get("content", "")
        payload = content.encode("utf-8")

    try:
        printer = win32print.OpenPrinter(printer_name)
        try:
            job_id = win32print.StartDocPrinter(
                printer, 1, ("Laravel Receipt", None, "RAW")
            )
            try:
                win32print.StartPagePrinter(printer)
                try:
                    win32print.WritePrinter(printer, payload)
                finally:
                    win32print.EndPagePrinter(printer)
            finally:
                win32print.EndDocPrinter(printer)
        finally:
            win32print.ClosePrinter(printer)
    except Exception as e:
        return jsonify({"success": False, "error": f"{printer_name}: {e}"}), 500

    return jsonify({"success": True, "message": "Printed successfully to " + printer_name})


if __name__ == "__main__":
    print(f"Bridge printer aktif: {resolve_printer(PRINTER_NAME)} @ http://{HOST}:{PORT}")
    app.run(host=HOST, port=PORT)