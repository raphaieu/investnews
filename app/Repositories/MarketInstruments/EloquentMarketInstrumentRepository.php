<?php

namespace App\Repositories\MarketInstruments;

use App\Models\MarketInstrument;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentMarketInstrumentRepository implements MarketInstrumentRepositoryInterface
{
    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator
    {
        return MarketInstrument::query()
            ->search($search)
            ->orderBy('symbol')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): MarketInstrument
    {
        return MarketInstrument::create($data);
    }

    public function update(MarketInstrument $instrument, array $data): MarketInstrument
    {
        $instrument->update($data);

        return $instrument->refresh();
    }

    public function delete(MarketInstrument $instrument): void
    {
        $instrument->delete();
    }
}
