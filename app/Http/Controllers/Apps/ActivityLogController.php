<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', '%'.$request->input('q').'%')
                    ->orWhere('ip_address', 'like', '%'.$request->input('q').'%')
                    ->orWhere('subject_type', 'like', '%'.$request->input('q').'%');
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->latest()->paginate(30);

        $actions = ActivityLog::distinct()->pluck('action')->filter()->values();
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Apps/ActivityLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'users' => $users,
            'ledgerStatus' => StockMovement::verifyIntegrity(),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', '%'.$request->input('q').'%')
                    ->orWhere('ip_address', 'like', '%'.$request->input('q').'%');
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $filename = 'MaterialTrack-ActivityLog-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Waktu', 'User', 'Aksi', 'Subjek', 'Detail', 'IP', 'User Agent']);

            $query->orderByDesc('created_at')->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at,
                        $log->user->name ?? 'System',
                        $log->action,
                        $log->subject_type,
                        json_encode($log->properties, JSON_UNESCAPED_UNICODE),
                        $log->ip_address,
                        $log->user_agent,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
