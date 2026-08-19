<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\StockAlertConfig;
use Illuminate\Http\Request;

class StockAlertConfigController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $alertTypes = ['low_stock', 'expiry', 'min_stock'];

        $configs = StockAlertConfig::where('user_id', $user->id)->get();

        $existingTypes = $configs->pluck('alert_type')->toArray();

        foreach ($alertTypes as $type) {
            if (!in_array($type, $existingTypes)) {
                StockAlertConfig::create([
                    'user_id'         => $user->id,
                    'alert_type'      => $type,
                    'email_enabled'   => true,
                    'browser_enabled' => true,
                    'threshold_days'  => 30,
                ]);
            }
        }

        $configs = StockAlertConfig::where('user_id', $user->id)->get();

        return inertia('Apps/StockAlerts/Index', [
            'configs' => $configs,
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'configs'                        => 'required|array',
            'configs.*.id'                   => 'required|exists:stock_alert_configs,id',
            'configs.*.email_enabled'        => 'required|boolean',
            'configs.*.browser_enabled'      => 'required|boolean',
            'configs.*.threshold_days'       => 'required|integer|min:1|max:365',
        ]);

        foreach ($validated['configs'] as $configData) {
            $config = StockAlertConfig::where('id', $configData['id'])
                ->where('user_id', $user->id)
                ->first();

            if ($config) {
                $config->update([
                    'email_enabled'   => $configData['email_enabled'],
                    'browser_enabled' => $configData['browser_enabled'],
                    'threshold_days'  => $configData['threshold_days'],
                ]);
            }
        }

        return back()->with('success', 'Pengaturan alert berhasil diperbarui.');
    }
}
