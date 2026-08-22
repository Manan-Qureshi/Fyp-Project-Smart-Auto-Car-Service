<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use App\Models\CarType;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $lat = $request->input('lat') ?? session('customer_lat');
        $lng = $request->input('lng') ?? session('customer_lng');

        if ($request->filled('lat') && $request->filled('lng')) {
            session(['customer_lat' => $lat, 'customer_lng' => $lng]);
        }

        $providers = collect();
        if ($lat && $lng) {
            $providers = ServiceProvider::nearbyProviders((float)$lat, (float)$lng);
        } else {
            $providers = ServiceProvider::where('is_active', true)
                ->withCount('ratings')
                ->get()
                ->map(function ($p) {
                    $p->distance = null;
                    return $p;
                });
        }

        // Add average rating
        $providers = $providers->map(function ($p) {
            $p->avg_rating = round($p->ratings()->avg('rating') ?? 0, 1);
            $p->rating_count = $p->ratings()->count();
            return $p;
        });

        $allCarTypes = CarType::with('models')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.providers_list', compact('providers', 'lat', 'lng'))->render()
            ]);
        }

        return view('welcome', compact('providers', 'allCarTypes', 'lat', 'lng'));
    }
}
