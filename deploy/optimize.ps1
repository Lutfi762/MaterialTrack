# deploy/optimize.ps1 — Optimasi produksi MaterialTrack.
# Jalankan SEBAGAI USER PRODUKSI, bukan di local (APP_ENV=local ditolak).
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)

# --- Guard: jangan pernah optimize di environment local ---
$appEnv = (Select-String -Path .env -Pattern '^APP_ENV=(.*)').Matches.Groups[1].Value.Trim()
if ($appEnv -eq 'local') {
    throw "APP_ENV=$appEnv — optimize hanya untuk produksi. Batal."
}

Write-Host "==> 1/5 ziggy:generate (routes JS external)"
php artisan ziggy:generate

Write-Host "==> 2/5 npm run build (aset ber-hash)"
npm run build

Write-Host "==> 3/5 config:cache"
php artisan config:cache

Write-Host "==> 4/5 route:cache + view:cache + event:cache"
php artisan route:cache
php artisan view:cache
php artisan event:cache

Write-Host ""
Write-Host "Optimasi selesai."
Write-Host "Catatan:"
Write-Host "  1. Restart PHP/Apache supaya OPcache kosong (bila opcache.validate_timestamps=0)."
Write-Host "  2. Di development, jangan lupa: php artisan config:clear route:clear view:clear event:clear"