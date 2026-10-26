<?php

namespace App\Http\Controllers;

use App\Models\TradeImportLog;
use Illuminate\Http\Request;

class TradeImportLogController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        if (!in_array($status, ['started', 'success', 'error'], true)) {
            $status = null;
        }

        $logs = TradeImportLog::where('user_id', auth()->id())
            ->when($status, fn($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('trade-import-logs.index', compact('logs', 'status'));
    }
}
