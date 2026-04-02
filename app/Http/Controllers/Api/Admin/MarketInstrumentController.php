<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarketInstrumentRequest;
use App\Http\Requests\UpdateMarketInstrumentRequest;
use App\Http\Resources\MarketInstrumentResource;
use App\Models\MarketInstrument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketInstrumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        return MarketInstrumentResource::collection(
            MarketInstrument::query()
                ->search($search)
                ->orderBy('symbol')
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function store(StoreMarketInstrumentRequest $request): JsonResponse
    {
        $instrument = MarketInstrument::create($request->validated());

        return (new MarketInstrumentResource($instrument))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MarketInstrument $market_instrument): MarketInstrumentResource
    {
        return new MarketInstrumentResource($market_instrument);
    }

    public function update(UpdateMarketInstrumentRequest $request, MarketInstrument $market_instrument): MarketInstrumentResource
    {
        $market_instrument->update($request->validated());

        return new MarketInstrumentResource($market_instrument);
    }

    public function destroy(MarketInstrument $market_instrument): JsonResponse
    {
        $market_instrument->delete();

        return response()->json(null, 204);
    }
}
