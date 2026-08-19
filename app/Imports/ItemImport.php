<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class ItemImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $category = Category::where('name', $row['category'] ?? '')->first();

        if (!$category) {
            $category = Category::firstOrCreate(['name' => $row['category'] ?? 'Umum']);
        }

        // Check if item already exists by code
        $existing = Item::where('code', $row['code'] ?? '')->first();
        if ($existing) {
            $existing->update([
                'name' => $row['name'] ?? $existing->name,
                'description' => $row['description'] ?? $existing->description,
                'unit' => $row['unit'] ?? $existing->unit,
                'min_stock' => $row['min_stock'] ?? $existing->min_stock,
                'current_stock' => $row['current_stock'] ?? $existing->current_stock,
            ]);
            return null;
        }

        return new Item([
            'code' => $row['code'] ?? Item::generateCode($category),
            'name' => $row['name'] ?? '',
            'description' => $row['description'] ?? '',
            'category_id' => $category->id,
            'unit' => $row['unit'] ?? 'pcs',
            'min_stock' => $row['min_stock'] ?? 0,
            'current_stock' => $row['current_stock'] ?? 0,
            'price' => $row['price'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'current_stock' => 'nullable|integer|min:0',
        ];
    }
}
