<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PanoramaResource;
use App\Services\Panorama\PanoramaService;
use Illuminate\Http\Request;

class PanoramaController extends Controller
{
    public function __construct(private readonly PanoramaService $panorama) {}

    public function index(Request $request)
    {
        return new PanoramaResource($this->panorama->resumenPara($request->user()));
    }
}
