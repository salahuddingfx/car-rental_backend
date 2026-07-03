<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::where('is_published', true);
        if ($request->category && $request->category !== 'All') {
            $query->where('category', $request->category);
        }
        return response()->json($query->orderByDesc('date')->paginate(12));
    }

    public function show($slug)
    {
        return response()->json(BlogPost::where('slug', $slug)->firstOrFail());
    }

    public function adminIndex()
    {
        return response()->json(BlogPost::orderByDesc('date')->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:blog_posts',
            'title' => 'required|string',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'category' => 'required|string',
            'date' => 'required|date',
            'read_time' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $post = BlogPost::create($validated);
        return response()->json($post, 201);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'slug' => 'sometimes|string|unique:blog_posts,slug,' . $blogPost->id,
            'title' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'category' => 'sometimes|string',
            'date' => 'sometimes|date',
            'read_time' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $blogPost->update($validated);
        return response()->json($blogPost);
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}
