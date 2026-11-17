<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\SalonDefaults;

/** "Salon" navigation item: the service catalogue and individual service pages. */
class SalonController extends Controller
{
    public function index()
    {
        $services = Service::active()->ordered()->get();
        $categories = collect(SalonDefaults::CATEGORIES)->filter(fn ($label, $key) => $services->contains('category', $key));

        return view('salon.index', compact('services', 'categories'));
    }

    public function show(Service $service)
    {
        abort_unless($service->is_active, 404);

        $related = Service::active()->ordered()->where('id', '!=', $service->id)
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$service->category])
            ->take(3)->get();

        return view('salon.show', compact('service', 'related'));
    }
}
