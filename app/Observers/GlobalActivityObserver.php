<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Mencatat semua perubahan (create/update/delete) model yang didaftarkan
 * ke Activity Log secara otomatis, termasuk nilai sebelum/sesudah.
 */
class GlobalActivityObserver
{
    private const SENSITIVE_KEYS = [
        'password', 'two_factor_secret', 'two_factor_recovery_codes',
        'remember_token', 'email_verified_at',
    ];

    public function created($model): void
    {
        $this->log('create', $model);
    }

    public function updated($model): void
    {
        $this->log('update', $model);
    }

    public function deleted($model): void
    {
        $this->log('delete', $model);
    }

    private function log(string $verb, $model): void
    {
        // Skip saat seeder / command berjalan tanpa user (bukan aksi user).
        // Tetap catat jika ada user terautentikasi (mis. test / queue job).
        if (app()->runningInConsole() && ! auth()->check()) {
            return;
        }

        $resource = Str::snake(Str::plural(class_basename($model)));
        $properties = [];

        if ($verb === 'create') {
            $properties['new'] = $this->safeAttributes($model);
        } elseif ($verb === 'update') {
            $properties['old'] = $this->safeAttributes($model->getOriginal());
            $properties['new'] = $this->safeAttributes($model);
        } else {
            $properties['old'] = $this->safeAttributes($model);
        }

        ActivityLogService::log("{$resource}.{$verb}", $model, $properties);
    }

    private function safeAttributes($model): array
    {
        $attributes = $model instanceof Model
            ? $model->getAttributes()
            : (array) $model;

        return collect($attributes)
            ->reject(fn ($value, $key) => in_array($key, self::SENSITIVE_KEYS, true))
            ->reject(fn ($value) => is_array($value) || is_object($value))
            ->map(fn ($value) => (string) $value)
            ->all();
    }
}
