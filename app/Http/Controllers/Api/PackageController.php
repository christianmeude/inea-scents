<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use OpenApi\Attributes as OAT;

#[OAT\Tag(
    name: 'Packages',
    description: 'API Endpoints for Packages'
)]
class PackageController extends Controller
{
    #[OAT\Get(
        path: '/api/packages',
        summary: 'Get list of packages',
        description: 'Returns list of available packages.',
        tags: ['Packages']
    )]
    #[OAT\Response(
        response: 200,
        description: 'Successful operation',
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(
                    property: 'data',
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/Package')
                )
            ],
            type: 'object'
        )
    )]
    public function index()
    {
        $packages = Package::with('scents')->get();

        return \App\Http\Resources\PackageResource::collection($packages);
    }

    #[OAT\Get(
        path: '/api/packages/{package}',
        summary: 'Get package details',
        description: 'Returns full package details for a specific ID.',
        tags: ['Packages']
    )]
    #[OAT\Parameter(
        name: 'package',
        description: 'ID of package to return',
        in: 'path',
        required: true,
        schema: new OAT\Schema(type: 'integer')
    )]
    #[OAT\Response(
        response: 200,
        description: 'Successful operation',
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(property: 'data', ref: '#/components/schemas/Package')
            ],
            type: 'object'
        )
    )]
    #[OAT\Response(response: 404, description: 'Package not found')]
    public function show(Package $package)
    {
        $package->load('scents');

        return new \App\Http\Resources\PackageResource($package);
    }
}
