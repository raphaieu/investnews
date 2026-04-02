<?php

namespace App\Services\MarketInstruments;

use App\Models\MarketInstrument;
use App\Repositories\MarketInstruments\MarketInstrumentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketInstrumentService
{
    public function __construct(
        private readonly MarketInstrumentRepositoryInterface $instrumentRepository
    ) {}

    public function adminIndex(Request $request): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        return $this->instrumentRepository->paginateForAdmin($search, $perPage);
    }

    public function create(array $validated): MarketInstrument
    {
        return $this->instrumentRepository->create($validated);
    }

    public function update(MarketInstrument $instrument, array $validated): MarketInstrument
    {
        return $this->instrumentRepository->update($instrument, $validated);
    }

    public function delete(MarketInstrument $instrument): void
    {
        $this->instrumentRepository->delete($instrument);
    }
}
