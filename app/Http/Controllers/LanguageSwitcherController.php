<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Prompt 21: Handles language switching (English ↔ Bangla).
 *
 * POST /language  { locale: 'en' | 'bn' }
 * Stores the chosen locale in the session and redirects back.
 */
class LanguageSwitcherController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->input('locale', 'en');

        if (! in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        Session::put('locale', $locale);

        return redirect()->back()->withHeaders([
            'Vary' => 'Accept-Language',
        ]);
    }
}
