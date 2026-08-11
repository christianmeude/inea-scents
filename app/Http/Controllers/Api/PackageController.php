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
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Romantic Getaway"),
     *                 @OA\Property(property="description", type="string", example="A nice package for couples."),
     *                 @OA\Property(property="price", type="number", format="float", example=199.99),
     *                 @OA\Property(property="rating", type="number", format="float", example=4.5),
     *                 @OA\Property(property="reviews_count", type="integer", example=120),
     *                 @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="gallery_images", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Package::all());
    }

    /**
     * @OA\Get(
     *     path="/api/packages/{id}",
     *     tags={"Packages"},
     *     summary="Get package details",
     *     description="Returns full package details for a specific ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of package to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Romantic Getaway"),
     *             @OA\Property(property="description", type="string", example="A nice package for couples."),
     *             @OA\Property(property="inclusions", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="pax_options", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="freebies", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="price", type="number", format="float", example=199.99),
     *             @OA\Property(property="rating", type="number", format="float", example=4.5),
     *             @OA\Property(property="reviews_count", type="integer", example=120),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="gallery_images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Package not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $package = Package::findOrFail($id);
        return response()->json($package);
    }
}
