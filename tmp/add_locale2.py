#!/usr/bin/env python3
import json, os
BASE = r'C:\laragon\www\MaterialTrack\resources/js/locales'

data_json = json.load(open(r'C:\laragon\www\MaterialTrack/tmp/locale_additions.json', encoding='utf-8'))

for lang in ['en', 'id']:
    path = os.path.join(BASE, f"{lang}.json")
    with open(path, 'r', encoding='utf-8') as f:
        loc = json.load(f)
    for section, langs in data_json.items():
        keys = langs.get(lang, langs.get('en', {}))
        if section not in loc:
            loc[section] = keys
        else:
            loc[section].update(keys)
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(loc, f, ensure_ascii=False, indent=2)
    print(f"Updated {lang}.json")
print("Done!")
