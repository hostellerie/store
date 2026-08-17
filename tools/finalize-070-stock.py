from pathlib import Path

for filename in ('includes/orders070.php', 'includes/pos070.php'):
    path = Path(filename)
    text = path.read_text(encoding='utf-8')
    old = "if (DB_error() || DB_affectedRows() < 1) {"
    if old not in text:
        raise SystemExit('Expected stock update guard not found in ' + filename)
    text = text.replace(old, "if (DB_error()) {", 1)
    path.write_text(text, encoding='utf-8')
