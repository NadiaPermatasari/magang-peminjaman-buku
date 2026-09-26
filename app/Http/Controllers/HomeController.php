<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;

/**
 * Public landing page (guests). Content (hero/about text, hero image) is
 * editable by Super Admin via Pengaturan > Halaman Depan; the stats and
 * featured books are always live from the database. Authenticated users
 * are sent straight to their dashboard instead.
 */
class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('home', [
            'stats' => [
                'totalBooks' => Book::where('is_active', true)->count(),
                'totalCategories' => Category::count(),
                'totalMembers' => Member::count(),
            ],
            'featuredBooks' => Book::where('is_active', true)
                ->with('category')
                ->withCount('copies')
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
