<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcements = Announcement::latest()->paginate(15)->through(fn (Announcement $announcement): array => [
            'id' => $announcement->id,
            'title' => (string) $announcement->title,
            'status' => $announcement->status === 'inactive' ? 'inactive' : 'active',
            'created' => EthiopianDate::smartFormat($announcement->created_at),
            'createdFull' => EthiopianDate::smartFormat($announcement->created_at, true, '', 'h:i A', 'Y-m-d'),
            'updated' => EthiopianDate::smartFormat($announcement->updated_at),
        ]);

        return Inertia::render('Admin/Announcements/Index', ['announcements' => $announcements]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Announcements/Form', ['announcement' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::create($this->validated($request));

        return redirect()->route('announcements.index')->with('success', __('announcements.status_saved'));
    }

    public function show(Announcement $announcement): Response
    {
        return Inertia::render('Admin/Announcements/Show', [
            'announcement' => [
                'id' => $announcement->id,
                'title' => (string) $announcement->title,
                'status' => $announcement->status === 'inactive' ? 'inactive' : 'active',
                'created' => EthiopianDate::smartFormat($announcement->created_at, true, '', 'h:i A', 'Y-m-d'),
                'contentHtml' => (string) clean((string) $announcement->content, 'cases'),
            ],
        ]);
    }

    public function edit(Announcement $announcement): Response
    {
        return Inertia::render('Admin/Announcements/Form', [
            'announcement' => [
                'id' => $announcement->id,
                'title' => (string) $announcement->title,
                'content' => (string) $announcement->content,
                'status' => $announcement->status === 'inactive' ? 'inactive' : 'active',
            ],
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request));

        return redirect()->route('announcements.index')->with('success', __('announcements.status_updated'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', __('announcements.status_deleted'));
    }

    /** @return array{title: string, content: string, status: string} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Rich text from the editor is sanitised before it is stored; signage sanitises again on output.
        $data['content'] = (string) clean($data['content'], 'cases');

        return $data;
    }
}
