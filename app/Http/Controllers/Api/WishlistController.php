<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/wishlist",
     *     summary="Get user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of wishlist packages",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(ref="#/components/schemas/Package")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return response()->json($request->user()->wishlistPackages);
    }

    /**
     * @OA\Post(
     *     path="/api/wishlist/toggle",
     *     summary="Toggle package in wishlist",
     *     tags={"Wishlist"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"package_id"},
     *
     *             @OA\Property(property="package_id", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Toggle status",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="attached", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = $request->user();
        $packageId = $request->package_id;

        $result = $user->wishlistPackages()->toggle($packageId);

        $attached = count($result['attached']) > 0;

        return response()->json([
            'attached' => $attached,
            'message' => $attached ? 'Package added to wishlist.' : 'Package removed from wishlist.',
        ]);
    }
}
