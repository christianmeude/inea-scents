<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OAT;

#[OAT\Info(
    version: "1.0.0",
    title: "Inea Scents API",
    description: "API Documentation for Inea Scents"
)]
#[OAT\Server(
    url: "/api",
    description: "API Server"
)]
abstract class Controller
{
    //
}
