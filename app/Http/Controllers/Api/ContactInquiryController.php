<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:5000'],
        ]);

        $inquiry = ContactInquiry::create($validated);

        return response()->json([
            'message' => 'Thank you. Your message has been received.',
            'contact_inquiry_id' => $inquiry->id,
        ], 201);
    }
}
