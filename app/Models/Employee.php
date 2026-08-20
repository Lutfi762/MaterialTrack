<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'employee_id', 'user_id', 'department_id', 'first_name', 'last_name',
        'email', 'phone', 'position', 'hire_date', 'employment_status',
        'basic_salary', 'bank_account_no', 'bank_name', 'tax_id', 'address', 'is_active',
        'employee_code', 'designation', 'join_date', 'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    protected static function booted(): void
    {
        // UI baru memakai employee_code; pastikan kolom lama employee_id ikut terisi
        static::creating(function (Employee $employee) {
            if (blank($employee->employee_id) && filled($employee->employee_code)) {
                $employee->employee_id = $employee->employee_code;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
