<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LetterCategoryController extends Controller
{
    public function index(): Response
    {
        $categories = LetterCategory::query()
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (LetterCategory $category): array => $this->present($category));

        return Inertia::render('Admin/LetterCategories/Index', ['categories' => $categories]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/LetterCategories/Form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);

        LetterCategory::create($data);

        return redirect()
            ->route('letter-categories.index')
            ->with('success', [
                'key' => 'messages.success.created',
                'replace' => ['resource' => __('messages.resources.letter_category')],
            ]);
    }

    public function edit(LetterCategory $letterCategory): Response
    {
        return Inertia::render('Admin/LetterCategories/Form', ['category' => $this->present($letterCategory)]);
    }

    public function update(Request $request, LetterCategory $letterCategory)
    {
        $data = $this->validateCategory($request, $letterCategory->id);

        $letterCategory->update($data);

        return redirect()
            ->route('letter-categories.index')
            ->with('success', [
                'key' => 'messages.success.updated',
                'replace' => ['resource' => __('messages.resources.letter_category')],
            ]);
    }

    public function destroy(LetterCategory $letterCategory)
    {
        $letterCategory->delete();

        return redirect()
            ->route('letter-categories.index')
            ->with('success', [
                'key' => 'messages.success.deleted',
                'replace' => ['resource' => __('messages.resources.letter_category')],
            ]);
    }

    /** @return array{id: int, name: string, description: string|null} */
    private function present(LetterCategory $category): array
    {
        return [
            'id' => (int) $category->id,
            'name' => (string) $category->name,
            'description' => $category->description ?: null,
        ];
    }

    private function validateCategory(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:letter_categories,name'.($ignoreId ? ",{$ignoreId}" : '')],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
