<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserLocaleController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|in:en,id',
        ]);

        $request->user()->update(['locale' => $validated['locale']]);

        return response()->json(['success' => true]);
    }
}
