#!/usr/bin/env python3
import json, os

BASE = r'C:\laragon\www\MaterialTrack\resources/js/locales'
files = ['tmp/locale_additions.json', 'tmp/locale_additions2.json']

additions = {}
for fname in files:
    with open(os.path.join(r'C:\laragon\www\MaterialTrack', fname), 'r', encoding='utf-8') as f:
        additions.update(json.load(f))

for lang in ['en', 'id']:
    path = os.path.join(BASE, f"{lang}.json")
    with open(path, "r", encoding="utf-8") as f:
        data = json.load(f)
    for section, langs in additions.items():
        keys = langs.get(lang, langs.get('en', {}))
        if section not in data:
            data[section] = keys
        else:
            data[section].update(keys)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    print(f"Updated {lang}.json with {len(additions)} sections")
print("Done!")
