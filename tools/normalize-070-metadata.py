from pathlib import Path

changed = []
for path in list(Path('.').rglob('*.php')) + [Path('functions.inc')]:
    if not path.is_file():
        continue
    text = path.read_text(encoding='utf-8')
    updated = text.replace(
        '// | Store Plugin 0.6.5                                                       |',
        '// | Store Plugin 0.7.0                                                       |'
    )
    updated = updated.replace("'version' => 'Version 0.6.5'", "'version' => 'Version 0.7.0'")
    if updated != text:
        path.write_text(updated, encoding='utf-8')
        changed.append(str(path))

print('Updated metadata in:')
for item in changed:
    print(' - ' + item)
