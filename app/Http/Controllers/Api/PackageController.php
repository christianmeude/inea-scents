<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Packages",
 *     description="API Endpoints for Packages"
 * )
 */
class PackageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/packages",
     *     tags={"Packages"},
     *     summary="Get list of packages",
     *     description="Returns list of available packages.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Package")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $packages = Package::select([
            'id', 'name', 'price', 'rating', 'reviews_count', 'images', 'gallery_images', 'description'
        ])->get();
        
        return response()->json($packages);
    }

    /**
     * @OA\Get(
     *     path="/api/packages/{package}",
     *     tags={"Packages"},
     *     summary="Get package details",
     *     description="Returns full package details for a specific ID.",
     *     @OA\Parameter(
     *         name="package",
     *         in="path",
     *         description="ID of package to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Package")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Package not found"
     *     )
     * )
     */
    public function show(Package $package)
    {
        $package->load('scents');
        return response()->json($package);
    }
}
