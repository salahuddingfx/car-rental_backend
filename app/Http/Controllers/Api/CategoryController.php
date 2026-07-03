<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Public: list active categories
    public function index()
    {
        return response()->json(
            Category::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    // Admin: list all categories
    public function adminIndex()
    {
        return response()->json(
            Category::orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    // Admin: create category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    // Admin: update category
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json($category);
    }

    // Admin: delete category
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
