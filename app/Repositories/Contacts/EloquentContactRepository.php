<?php

namespace App\Repositories\Contacts;

use App\Models\Contact;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentContactRepository implements ContactRepositoryInterface
{
    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return Contact::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
