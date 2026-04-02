<?php

namespace App\Repositories\MarketInstruments;

use App\Models\MarketInstrument;
use Illuminate\Pagination\LengthAwarePaginator;

interface MarketInstrumentRepositoryInterface
{
    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator;

    public function create(array $data): MarketInstrument;

    public function update(MarketInstrument $instrument, array $data): MarketInstrument;

    public function delete(MarketInstrument $instrument): void;
}
