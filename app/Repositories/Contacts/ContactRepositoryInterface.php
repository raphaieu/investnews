<?php

namespace App\Repositories\Contacts;

use App\Models\Contact;
use Illuminate\Pagination\LengthAwarePaginator;

interface ContactRepositoryInterface
{
    public function create(array $data): Contact;

    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator;
}
