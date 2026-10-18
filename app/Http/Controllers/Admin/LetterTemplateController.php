<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterCategory;
use App\Models\LetterTemplate;
use App\Support\EthiopianDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LetterTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = LetterTemplate::query()
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (LetterTemplate $template): array => [
                'id' => (int) $template->id,
                'title' => (string) $template->title,
                'category' => $template->category ?: null,
                'placeholders' => array_values($template->placeholders ?? []),
                'updated' => EthiopianDate::smartRelative($template->updated_at),
            ]);

        return Inertia::render('Admin/LetterTemplates/Index', ['templates' => $templates]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);
        $data = $this->handleUploads($request, new LetterTemplate(), $data);

        LetterTemplate::create($data);

        return redirect()->route('letter-templates.index')->with('success', [
            'key' => 'messages.success.created',
            'replace' => ['resource' => __('messages.resources.letter_template')],
        ]);
    }

    public function edit(LetterTemplate $letterTemplate): Response
    {
        return $this->form($letterTemplate);
    }

    public function update(Request $request, LetterTemplate $letterTemplate)
    {
        $data = $this->validateTemplate($request);
        $data = $this->handleUploads($request, $letterTemplate, $data);

        $letterTemplate->update($data);

        return redirect()->route('letter-templates.index')->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.letter_template')],
        ]);
    }

    public function destroy(LetterTemplate $letterTemplate)
    {
        $letterTemplate->delete();

        return redirect()->route('letter-templates.index')->with('success', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.letter_template')],
        ]);
    }

    /** Create and edit share one page; `template` is null when creating. */
    private function form(?LetterTemplate $template): Response
    {
        return Inertia::render('Admin/LetterTemplates/Form', [
            'template' => $template === null ? null : [
                'id' => (int) $template->id,
                'title' => (string) $template->title,
                'category' => (string) ($template->category ?? ''),
                'placeholders' => implode(', ', $template->placeholders ?? []),
                'body' => (string) $template->body,
                'headerImage' => $this->image($template->header_image_path),
                'footerImage' => $this->image($template->footer_image_path),
            ],
            'categories' => LetterCategory::query()->orderBy('name')->pluck('name')->values(),
        ]);
    }

    /** @return array{url: string, name: string}|null */
    private function image(?string $path): ?array
    {
        return $path ? ['url' => asset('storage/'.$path), 'name' => basename($path)] : null;
    }

    private function validateTemplate(Request $request): array
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'category'            => ['nullable', 'string', 'max:120'],
            'placeholders'        => ['nullable', 'string', 'max:2000'],
            'body'                => ['required', 'string'],
            'header_image'        => ['nullable', 'image', 'max:3072'],
            'footer_image'        => ['nullable', 'image', 'max:3072'],
            'remove_header_image' => ['nullable', 'boolean'],
            'remove_footer_image' => ['nullable', 'boolean'],
        ]);

        // Clean placeholders into array format
        $data['placeholders'] = $this->normalizePlaceholders($data['placeholders'] ?? null);

        return $data;
    }

    private function normalizePlaceholders(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $parts = preg_split('/[\r\n,]+/', $raw);
        $parts = array_filter(array_map('trim', $parts));

        return empty($parts) ? null : array_values(array_unique($parts));
    }

    private function handleUploads(Request $request, LetterTemplate $template, array $data): array
    {
        // Remove HEADER
        if ($request->boolean('remove_header_image') && $template->header_image_path) {
            Storage::disk('public')->delete($template->header_image_path);
            $data['header_image_path'] = null;
        }

        // Remove FOOTER
        if ($request->boolean('remove_footer_image') && $template->footer_image_path) {
            Storage::disk('public')->delete($template->footer_image_path);
            $data['footer_image_path'] = null;
        }

        // Upload HEADER
        if ($request->hasFile('header_image')) {
            if ($template->header_image_path) {
                Storage::disk('public')->delete($template->header_image_path);
            }
            $data['header_image_path'] = $request->file('header_image')
                ->store('letter-templates/headers', 'public');
        }

        // Upload FOOTER
        if ($request->hasFile('footer_image')) {
            if ($template->footer_image_path) {
                Storage::disk('public')->delete($template->footer_image_path);
            }
            $data['footer_image_path'] = $request->file('footer_image')
                ->store('letter-templates/footers', 'public');
        }

        // Cleanup unused fields
        unset(
            $data['header_image'],
            $data['footer_image'],
            $data['remove_header_image'],
            $data['remove_footer_image']
        );

        return $data;
    }
}
