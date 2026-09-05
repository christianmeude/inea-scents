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
            'pax_options' => 'nullable|array',
            'freebies' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array|max:3',
        ]);

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
            'pax_options' => 'nullable|array',
            'freebies' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array|max:3',
        ]);

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
}
