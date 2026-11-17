<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $inquiries = Inquiry::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")->orWhere('subject', 'like', "%{$q}%")->orWhere('message', 'like', "%{$q}%")))
            ->when(in_array($request->query('status'), Inquiry::STATUSES, true), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.inquiries.index', compact('inquiries'));
    }

    public function show(Inquiry $inquiry)
    {
        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Inquiry::STATUSES)],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);
        if (($data['admin_response'] ?? '') !== (string) $inquiry->admin_response && !empty($data['admin_response'])) {
            $data['responded_at'] = now();
        }
        $inquiry->update($data);

        return back()->with('success', 'Inquiry updated.');
    }
}
