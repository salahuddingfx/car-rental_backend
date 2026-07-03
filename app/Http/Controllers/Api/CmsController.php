<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Timeline;
use App\Models\ProcessStep;
use App\Models\CmsContent;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function getCmsContent(Request $request)
    {
        $contents = CmsContent::all()->keyBy('key');
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();
        $offers = Offer::where('active', true)->get();
        $reviews = Review::orderByDesc('date')->get();
        $timelines = Timeline::orderBy('sort_order')->get();
        $steps = ProcessStep::orderBy('sort_order')->get();

        return response()->json([
            'cms' => $contents,
            'faqs' => $faqs,
            'offers' => $offers,
            'reviews' => $reviews,
            'timelines' => $timelines,
            'process_steps' => $steps,
        ]);
    }

    // CMS Content
    public function getCmsByKey($key)
    {
        $content = CmsContent::where('key', $key)->firstOrFail();
        return response()->json($content);
    }

    public function upsertCms(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'group' => 'nullable|string',
            'value' => 'required|array',
        ]);

        $content = CmsContent::updateOrCreate(['key' => $validated['key']], [
            'group' => $validated['group'] ?? 'general',
            'value' => $validated['value'],
        ]);

        return response()->json($content);
    }

    // FAQ
    public function faqIndex() { return response()->json(Faq::orderBy('sort_order')->get()); }

    public function faqStore(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:5000',
        ]);
        return response()->json(Faq::create($validated), 201);
    }

    public function faqUpdate(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'sometimes|string|max:1000',
            'answer' => 'sometimes|string|max:5000',
        ]);
        $faq->update($validated);
        return response()->json($faq);
    }

    public function faqDestroy(Faq $faq) { $faq->delete(); return response()->json(['message' => 'Deleted']); }

    // Reviews
    public function reviewIndex() { return response()->json(Review::orderByDesc('date')->get()); }

    public function reviewStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string', 'avatar' => 'nullable|string',
            'rating' => 'required|integer|min:1,max:5', 'text' => 'required|string',
            'source' => 'required|in:google,facebook,tripadvisor,apexride', 'date' => 'required|date',
        ]);
        return response()->json(Review::create($validated), 201);
    }

    public function reviewUpdate(Request $request, Review $review)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'rating' => 'sometimes|integer|min:1|max:5',
            'text' => 'sometimes|string|max:5000',
            'source' => 'sometimes|string|in:google,facebook,tripadvisor,apexride',
            'date' => 'sometimes|date',
        ]);
        $review->update($validated);
        return response()->json($review);
    }

    public function reviewDestroy(Review $review) { $review->delete(); return response()->json(['message' => 'Deleted']); }

    // Offers
    public function offerIndex() { return response()->json(Offer::all()); }

    public function offerStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string', 'description' => 'nullable|string',
            'cta_text' => 'nullable|string', 'cta_link' => 'nullable|string',
            'image' => 'nullable|string', 'active' => 'nullable|boolean',
        ]);
        return response()->json(Offer::create($validated), 201);
    }

    public function offerUpdate(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'cta_text' => 'nullable|string|max:100',
            'cta_link' => 'nullable|string|max:500',
            'image' => 'nullable|string|max:500',
            'active' => 'sometimes|boolean',
        ]);
        $offer->update($validated);
        return response()->json($offer);
    }

    public function offerDestroy(Offer $offer) { $offer->delete(); return response()->json(['message' => 'Deleted']); }

    // Timelines
    public function timelineIndex() { return response()->json(Timeline::orderBy('sort_order')->get()); }

    public function timelineStore(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string', 'title' => 'required|string',
            'description' => 'nullable|string', 'icon' => 'nullable|string',
            'type' => 'required|in:journey,process', 'sort_order' => 'nullable|integer',
        ]);
        return response()->json(Timeline::create($validated), 201);
    }

    public function timelineUpdate(Request $request, Timeline $timeline)
    {
        $validated = $request->validate([
            'year' => 'sometimes|string|max:10',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:100',
            'type' => 'sometimes|string|in:journey,process',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $timeline->update($validated);
        return response()->json($timeline);
    }

    public function timelineDestroy(Timeline $timeline) { $timeline->delete(); return response()->json(['message' => 'Deleted']); }

    // Process Steps
    public function stepIndex() { return response()->json(ProcessStep::orderBy('sort_order')->get()); }

    public function stepStore(Request $request)
    {
        $validated = $request->validate([
            'step' => 'required|integer', 'title' => 'required|string',
            'description' => 'nullable|string', 'icon' => 'nullable|string',
        ]);
        return response()->json(ProcessStep::create($validated), 201);
    }

    public function stepUpdate(Request $request, ProcessStep $step)
    {
        $validated = $request->validate([
            'step' => 'sometimes|integer|min:1',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $step->update($validated);
        return response()->json($step);
    }

    public function stepDestroy(ProcessStep $step) { $step->delete(); return response()->json(['message' => 'Deleted']); }
}
