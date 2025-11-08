<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    /**
     * Update dark mode preference
     */
    public function updateDarkMode(Request $request)
    {
        $validated = $request->validate([
            'dark_mode' => ['required', 'boolean'],
        ]);

        $user = auth()->user();
        $user->update(['dark_mode' => $validated['dark_mode']]);

        return back();
    }
}
