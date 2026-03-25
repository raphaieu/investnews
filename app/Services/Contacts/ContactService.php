<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Repositories\Contacts\ContactRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactService
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}

    public function store(array $validated): Contact
    {
        return $this->contactRepository->create($validated);
    }

    public function adminIndex(Request $request): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        return $this->contactRepository->paginateForAdmin($search, $perPage);
    }
}
