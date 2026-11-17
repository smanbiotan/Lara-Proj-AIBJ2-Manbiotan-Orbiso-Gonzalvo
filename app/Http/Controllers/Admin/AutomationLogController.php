<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use Illuminate\Http\Request;

class AutomationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AutomationLog::query()
            ->when($request->query('workflow'), fn ($q, $w) => $q->where('workflow', $w))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->where('appointment_reference', 'like', "%{$term}%"))
            ->latest()->paginate(25)->withQueryString();

        $summary = AutomationLog::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.automation-logs.index', [
            'logs' => $logs,
            'summary' => $summary,
            'configured' => [
                'Chat webhook (Workflow 1)' => (bool) config('salon.n8n.chat_webhook_url'),
                'Appointment webhook (Workflow 2)' => (bool) config('salon.n8n.appointment_webhook_url'),
                'Webhook secret' => (bool) config('salon.n8n.webhook_secret'),
                'n8n API token (Workflows 1 & 3)' => strlen((string) config('salon.n8n.api_token')) >= 16,
            ],
        ]);
    }
}
