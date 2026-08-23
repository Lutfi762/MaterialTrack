#!/usr/bin/env python3
import json, os
BASE = r'C:\laragon\www\MaterialTrack\resources/js/locales'

updates = {
    'en': {
        'cash_bank': {
            'add': 'New Transaction',
            'search_placeholder': 'Search transactions...',
            'col_number': 'Number',
            'col_counter_account': 'Counter Account',
            'col_description': 'Description',
            'total': 'Total',
        },
        'payroll': {
            'col_period': 'Period',
            'delete_confirm': 'This payroll period will be permanently deleted.',
        },
    },
    'id': {
        'cash_bank': {
            'add': 'Catat Transaksi',
            'search_placeholder': 'Cari transaksi...',
            'col_number': 'Nomor',
            'col_counter_account': 'Lawan Akun',
            'col_description': 'Keterangan',
            'total': 'Total',
        },
        'payroll': {
            'col_period': 'Periode',
            'delete_confirm': 'Periode gaji ini akan dihapus permanen.',
        },
    }
}

for lang in ['en', 'id']:
    path = os.path.join(BASE, f"{lang}.json")
    with open(path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    for section, keys in updates[lang].items():
        if section not in data:
            data[section] = {}
        data[section].update(keys)
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    print(f'Updated {lang}.json')
print('Done!')
