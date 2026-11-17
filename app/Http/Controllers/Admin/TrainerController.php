<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    public function index(Request $request)
    {
        $trainers = Trainer::withCount('members')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->q;
                $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('specialization', 'like', "%{$q}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('name')
            ->paginate(12);

        return view('admin.trainers.index', compact('trainers'));
    }

    public function create()
    {
        return view('admin.trainers.create', ['trainer' => new Trainer(['status' => 'active', 'gender' => 'male', 'joined_at' => now()])]);
    }

    public function store(Request $request)
    {
        $trainer = Trainer::create($this->validated($request));

        return redirect()->route('admin.trainers.show', $trainer)->with('success', 'Trainer added successfully.');
    }

    public function show(Trainer $trainer)
    {
        $trainer->loadCount('members');
        $members = $trainer->members()->with('activeSubscription.plan')->orderBy('name')->get();

        return view('admin.trainers.show', compact('trainer', 'members'));
    }

    public function edit(Trainer $trainer)
    {
        return view('admin.trainers.edit', compact('trainer'));
    }

    public function update(Request $request, Trainer $trainer)
    {
        $trainer->update($this->validated($request, $trainer));

        return redirect()->route('admin.trainers.show', $trainer)->with('success', 'Trainer updated successfully.');
    }

    public function destroy(Trainer $trainer)
    {
        $trainer->delete();

        return redirect()->route('admin.trainers.index')->with('success', 'Trainer deleted.');
    }

    private function validated(Request $request, ?Trainer $trainer = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('trainers', 'email')->ignore($trainer?->id)],
            'phone' => 'nullable|string|max:30',
            'gender' => 'required|in:male,female,other',
            'specialization' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:80',
            'salary' => 'nullable|numeric|min:0',
            'joined_at' => 'nullable|date',
            'bio' => 'nullable|string|max:5000',
            'status' => 'required|in:active,inactive',
        ]);
        $data['experience_years'] = $data['experience_years'] ?? 0;
        $data['salary'] = $data['salary'] ?? 0;

        return $data;
    }
}
