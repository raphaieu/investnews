<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Services\Contacts\ContactService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contactService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->contactService->adminIndex($request);

        return ContactResource::collection($paginator);
    }
}
