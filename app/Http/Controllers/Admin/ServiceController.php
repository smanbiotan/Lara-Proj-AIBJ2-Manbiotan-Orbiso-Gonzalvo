<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\SalonDefaults;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        return view('admin.services.index', ['services' => Service::withCount('appointments')->ordered()->get()]);
    }

    public function create()
    {
        return view('admin.services.form', ['service' => new Service(['is_active' => true, 'duration_minutes' => 60, 'category' => 'hair'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $request->hasFile('image') ? $this->storeImage($request->file('image')) : 'images/services/haircut.jpg';
        $data['sort_order'] = (Service::max('sort_order') ?? 0) + 1;
        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.form', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request, $service);
        if ($request->hasFile('image')) $data['image'] = $this->storeImage($request->file('image'));

        $gallery = $service->gallery ?? [];
        foreach ((array) $request->input('remove_gallery', []) as $index) unset($gallery[(int) $index]);
        foreach ((array) $request->file('gallery', []) as $file) {
            $gallery[] = ['image' => $this->storeImage($file), 'caption' => $service->name];
        }
        $data['gallery'] = array_values($gallery);

        $service->update($data);
        return redirect()->route('admin.services.index')->with('success', "{$service->name} updated.");
    }

    public function toggle(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        return back()->with('success', $service->name.' is now '.($service->is_active ? 'available for booking.' : 'hidden and not bookable.'));
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('services', 'slug')->ignore($service?->id)],
            'category' => ['required', Rule::in(array_keys(SalonDefaults::CATEGORIES))],
            'short_description' => ['required', 'string', 'max:500'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'inclusions' => ['nullable', 'string', 'max:3000'],
            'benefits' => ['nullable', 'string', 'max:3000'],
            'ideal_for' => ['nullable', 'string', 'max:1000'],
            'preparation' => ['nullable', 'string', 'max:2000'],
            'aftercare' => ['nullable', 'string', 'max:2000'],
            'faqs' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=300,min_height=200'],
            'gallery.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_gallery.*' => ['nullable', 'integer'],
        ]);

        $lines = fn (?string $text) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\n/', (string) $text))));
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        if (Service::where('slug', $data['slug'])->when($service, fn ($q) => $q->where('id', '!=', $service->id))->exists()) {
            $data['slug'] .= '-'.Str::lower(Str::random(4));
        }
        $data['inclusions'] = $lines($data['inclusions'] ?? '');
        $data['benefits'] = $lines($data['benefits'] ?? '');
        // FAQs: one per line, formatted "Question? | Answer"
        $data['faqs'] = collect($lines($data['faqs'] ?? ''))->map(function ($line) {
            [$q, $a] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            return $q && $a ? ['q' => $q, 'a' => $a] : null;
        })->filter()->values()->all();
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image'], $data['gallery'], $data['remove_gallery']);

        return $data;
    }

    /** Stores uploads under public/uploads/services with a random name (never the client file name). */
    private function storeImage(UploadedFile $file): string
    {
        $name = Str::uuid().'.'.$file->extension();
        $file->move(public_path('uploads/services'), $name);
        return 'uploads/services/'.$name;
    }
}
