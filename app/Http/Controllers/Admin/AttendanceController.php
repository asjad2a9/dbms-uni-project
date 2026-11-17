<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ?: now()->toDateString();
        try {
            $date = Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            $date = now()->toDateString();
        }

        $query = Attendance::with('member')->whereDate('date', $date);

        if ($q = trim((string) $request->input('q'))) {
            $query->whereHas('member', fn ($m) => $m->where('name', 'like', "%{$q}%")
                ->orWhere('member_code', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }

        $attendances = $query->orderByDesc('check_in')->paginate(20);

        $today = now()->toDateString();
        $todayCount = Attendance::whereDate('date', $today)->count();
        $insideCount = Attendance::whereDate('date', $today)->whereNull('check_out')->count();
        $monthCount = Attendance::whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count();

        $members = Member::where('status', 'active')->orderBy('name')->get(['id', 'name', 'member_code']);

        return view('admin.attendance.index', compact('attendances', 'date', 'todayCount', 'insideCount', 'monthCount', 'members'));
    }

    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
        ]);

        $member = Member::with('activeSubscription')->findOrFail($data['member_id']);
        $today = now()->toDateString();

        $open = Attendance::where('member_id', $member->id)
            ->whereDate('date', $today)
            ->whereNull('check_out')
            ->exists();

        if ($open) {
            return back()->with('error', "{$member->name} is already checked in.");
        }

        Attendance::create([
            'member_id' => $member->id,
            'date' => $today,
            'check_in' => now()->format('H:i:s'),
        ]);

        $msg = "{$member->name} checked in at " . now()->format('h:i A') . '.';
        if (! $member->activeSubscription) {
            $msg .= ' (no active plan!)';
        }

        return back()->with('success', $msg);
    }

    public function checkOut(Attendance $attendance)
    {
        if ($attendance->check_out) {
            return back()->with('error', 'Already checked out.');
        }

        $attendance->update(['check_out' => now()->format('H:i:s')]);

        return back()->with('success', ($attendance->member->name ?? 'Member') . ' checked out.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', 'Attendance record deleted.');
    }
}
