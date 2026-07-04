<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderVerification;
use Illuminate\Http\Request;

class ProviderVerificationController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404);
        }

        $verifications = $provider->verifications()->orderByDesc('created_at')->get();

        return response()->json($verifications);
    }

    public function store(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404);
        }

        $request->validate([
            'document_type' => 'required|in:trade_license,tax_id,insurance,vehicle_registration,company_registration,other',
            'document_number' => 'nullable|string|max:100',
            'document_image' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $verification = ProviderVerification::create([
            ...$request->only(['document_type', 'document_number', 'document_image', 'expires_at']),
            'provider_id' => $provider->id,
            'status' => 'pending',
        ]);

        $provider->update(['verification_status' => 'under_review']);

        return response()->json(['verification' => $verification], 201);
    }
}
