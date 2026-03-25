<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Jobs\ProcessContactSubmission;
use Illuminate\Http\JsonResponse;

class PublicContactController extends Controller
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        ProcessContactSubmission::dispatch($request->validated());

        return response()->json([
            'message' => 'Mensagem recebida e enfileirada para processamento.',
            'status' => 'queued',
        ], 202);
    }
}
