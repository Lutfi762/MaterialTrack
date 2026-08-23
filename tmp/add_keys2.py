#!/usr/bin/env python3
import json, os
BASE = r'C:\laragon\www\MaterialTrack\resources/js/locales'

updates = {
    'en': {
        'stock_adjustments': {
            'col_ref': 'Reference',
            'col_date': 'Date',
            'col_by': 'By',
            'col_status': 'Status',
            'status_pending': 'Pending',
            'status_approved': 'Approved',
            'status_rejected': 'Rejected',
            'approve_confirm': 'Approve this adjustment?',
            'reject_confirm': 'Reject this adjustment?',
            'delete_confirm': 'This adjustment will be permanently deleted.',
        },
    },
    'id': {
        'stock_adjustments': {
            'col_ref': 'Ref',
            'col_date': 'Tanggal',
            'col_by': 'Diajukan',
            'col_status': 'Status',
            'status_pending': 'Menunggu',
            'status_approved': 'Disetujui',
            'status_rejected': 'Ditolak',
            'approve_confirm': 'Setujui penyesuaian ini?',
            'reject_confirm': 'Tolak penyesuaian ini?',
            'delete_confirm': 'Penyesuaian ini akan dihapus permanen.',
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
