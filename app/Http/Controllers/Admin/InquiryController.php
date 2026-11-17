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
        $query = Inquiry::with('plan');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $inquiries = $query->latest()->paginate(15);
        $newCount = Inquiry::where('status', 'new')->count();

        return view('admin.inquiries.index', compact('inquiries', 'newCount'));
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Inquiry::STATUSES)],
        ]);

        $inquiry->update($data);

        return back()->with('success', "Inquiry from {$inquiry->name} marked as {$data['status']}.");
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return back()->with('success', 'Inquiry deleted.');
    }
}
