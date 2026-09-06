<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::all();

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Packages/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, \App\Services\ImageUploader $imageUploader)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'nullable|string|max:255',
            'pax_options' => 'nullable|array',
            'pax_options.*' => 'nullable|integer|min:1',
            'freebies' => 'nullable|array',
            'freebies.*' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array|max:3',
        ]);

        $validated['inclusions'] = self::cleanStringList($validated['inclusions'] ?? null);
        $validated['pax_options'] = self::cleanPaxOptions($validated['pax_options'] ?? null);
        $validated['freebies'] = self::cleanStringList($validated['freebies'] ?? null);

        if (isset($validated['images'])) {
            $validated['images'] = $imageUploader->uploadMultiple($request->images);
        }

        Package::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        // Not used, using edit instead.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        return Inertia::render('Packages/Edit', [
            'package' => $package,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package, \App\Services\ImageUploader $imageUploader)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'nullable|string|max:255',
            'pax_options' => 'nullable|array',
            'pax_options.*' => 'nullable|integer|min:1',
            'freebies' => 'nullable|array',
            'freebies.*' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array|max:3',
        ]);

        $validated['inclusions'] = self::cleanStringList($validated['inclusions'] ?? null);
        $validated['pax_options'] = self::cleanPaxOptions($validated['pax_options'] ?? null);
        $validated['freebies'] = self::cleanStringList($validated['freebies'] ?? null);

        if (isset($validated['images'])) {
            $validated['images'] = $imageUploader->uploadMultiple($request->images);
        } else {
            $validated['images'] = [];
        }

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully.');
    }

    /**
     * Strip null/empty/whitespace string elements (ConvertEmptyStringsToNull
     * turns blank dynamic rows into null). Always returns a clean list.
     */
    private static function cleanStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => is_string($item) ? trim($item) : null,
            $value,
        ), fn ($item) => is_string($item) && $item !== ''));
    }

    /**
     * Coerce numeric strings ("12") to ints, drop null/invalid/<1 entries.
     */
    private static function cleanPaxOptions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $cleaned = [];
        foreach ($value as $item) {
            if (is_int($item) && $item >= 1) {
                $cleaned[] = $item;
                continue;
            }
            if (is_string($item) && trim($item) !== '' && filter_var(trim($item), FILTER_VALIDATE_INT) !== false) {
                $int = (int) trim($item);
                if ($int >= 1) {
                    $cleaned[] = $int;
                }
            }
        }

        return array_values($cleaned);
    }
}
