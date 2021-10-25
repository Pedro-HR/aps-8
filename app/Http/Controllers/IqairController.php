<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Services\IqairService;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class IqairController extends Controller
{
    private IqairService $service;

    public function __construct(IqairService $iqairService)
    {
        $this->service = $iqairService;
    }

    public function citys()
    {
        try {
            $data = $this->service->city();

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);

            return response()->json($exception);
        }
    }
}
