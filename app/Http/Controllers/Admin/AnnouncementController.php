<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(15);

        // The list (React, Court/Announcements/Index).
        return Inertia::render('Court/Announcements/Index', [
            'announcements' => $announcements->through(fn (Announcement $announcement): array => [
                'id' => (int) $announcement->id,
                'title' => (string) $announcement->title,
                'status' => (string) ($announcement->status ?? 'active'),
                'created_at' => $announcement->created_at?->toIso8601String(),
                'updated_at' => $announcement->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Court/Announcements/Form', ['announcement' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Announcement::create($data);

        return redirect()->route('announcements.index')->with('success', __('announcements.status_saved'));
    }

    public function show(Announcement $announcement)
    {
        return Inertia::render('Court/Announcements/Show', ['announcement' => [
            ...$this->formProps($announcement),
            'created_at' => $announcement->created_at?->toIso8601String(),
        ]]);
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Court/Announcements/Form', ['announcement' => $this->formProps($announcement)]);
    }

    /** @return array{id: int, title: string, content: string, status: string} */
    private function formProps(Announcement $announcement): array
    {
        return [
            'id' => (int) $announcement->id,
            'title' => (string) $announcement->title,
            'content' => (string) $announcement->content,
            'status' => (string) ($announcement->status ?? 'active'),
        ];
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $announcement->update($data);

        return redirect()->route('announcements.index')->with('success', __('announcements.status_updated'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', __('announcements.status_deleted'));
    }
}
