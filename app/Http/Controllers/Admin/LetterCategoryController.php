<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterCategory;
use Illuminate\Http\Request;

class LetterCategoryController extends Controller
{
    public function index()
    {
        $categories = LetterCategory::query()
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.letter-templates.categories.index', compact('categories'));
    }

    public function create()
    {
        $category = new LetterCategory();

        return view('admin.letter-templates.categories.create', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);

        LetterCategory::create($data);

        return redirect()
            ->route('letter-categories.index')
            ->with('success', 'Letter category created.');
    }

    public function edit(LetterCategory $letterCategory)
    {
        return view('admin.letter-templates.categories.edit', [
            'category' => $letterCategory,
        ]);
    }

    public function update(Request $request, LetterCategory $letterCategory)
    {
        $data = $this->validateCategory($request, $letterCategory->id);

        $letterCategory->update($data);

        return redirect()
            ->route('letter-categories.index')
            ->with('success', 'Letter category updated.');
    }

    public function destroy(LetterCategory $letterCategory)
    {
        $letterCategory->delete();

        return redirect()
            ->route('letter-categories.index')
            ->with('success', 'Letter category deleted.');
    }

    private function validateCategory(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:letter_categories,name'.($ignoreId ? ",{$ignoreId}" : '')],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
