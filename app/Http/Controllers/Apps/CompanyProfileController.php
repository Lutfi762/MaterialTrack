<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompanyProfileController extends Controller
{
    public function index()
    {
        $company = CompanyProfile::first() ?? new CompanyProfile();

        return Inertia::render('Apps/CompanyProfile/Index', [
            'company' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
        ]);

        DB::transaction(function () use ($validated) {
            // Kunci baris agar tetap satu-satunya profil (ces duplikat konkuren)
            $company = CompanyProfile::lockForUpdate()->first();
            if ($company) {
                $company->update($validated);
            } else {
                CompanyProfile::create($validated);
            }
        });

        return to_route('apps.company-profile.index')->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}
