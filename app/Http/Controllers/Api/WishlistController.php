<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
class WishlistController extends Controller
{
    #[OAT\Get(
        path: '/api/wishlist',
        summary: "Get user's wishlist",
        security: [['sanctum' => []]],
        tags: ['Wishlist']
    )]
    #[OAT\Response(
        response: 200,
        description: 'List of wishlist packages',
        content: new OAT\JsonContent(
            type: 'array',
            items: new OAT\Items(ref: '#/components/schemas/Package')
        )
    )]
    public function index(Request $request)
    {
        return response()->json($request->user()->wishlistPackages);
    }

    #[OAT\Post(
        path: '/api/wishlist/toggle',
        summary: 'Toggle package in wishlist',
        security: [['sanctum' => []]],
        tags: ['Wishlist']
    )]
    #[OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ['package_id'],
            properties: [
                new OAT\Property(property: 'package_id', type: 'integer', example: 1),
            ]
        )
    )]
    #[OAT\Response(
        response: 200,
        description: 'Toggle status',
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(property: 'attached', type: 'boolean'),
                new OAT\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OAT\Response(response: 401, description: 'Unauthenticated')]
    #[OAT\Response(response: 422, description: 'Validation Error')]
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
