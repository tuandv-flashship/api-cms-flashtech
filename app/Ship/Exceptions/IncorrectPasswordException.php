<?php

namespace App\Ship\Exceptions;

use App\Ship\Parents\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncorrectPasswordException extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    protected $message = 'Current password is incorrect.';

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: Response::HTTP_BAD_REQUEST);
    }
}
