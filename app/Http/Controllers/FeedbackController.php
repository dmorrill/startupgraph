<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        Feedback::create([
            'user_id' => $request->user()?->id,
            'page_url' => $request->input('page_url', $request->header('referer')),
            'message' => $request->input('message'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok'], 201);
        }

        return back()->with('status', 'Thanks for your feedback!');
    }
}
