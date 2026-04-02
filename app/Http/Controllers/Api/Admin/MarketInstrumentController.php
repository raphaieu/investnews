<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarketInstrumentRequest;
use App\Http\Requests\UpdateMarketInstrumentRequest;
use App\Http\Resources\MarketInstrumentResource;
use App\Models\MarketInstrument;
use App\Services\MarketInstruments\MarketInstrumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketInstrumentController extends Controller
{
    public function __construct(private readonly MarketInstrumentService $instrumentService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return MarketInstrumentResource::collection(
            $this->instrumentService->adminIndex($request)
        );
    }

    public function store(StoreMarketInstrumentRequest $request): JsonResponse
    {
        $instrument = $this->instrumentService->create($request->validated());

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
        $instrument = $this->instrumentService->update($market_instrument, $request->validated());

        return new MarketInstrumentResource($instrument);
    }

    public function destroy(MarketInstrument $market_instrument): JsonResponse
    {
        $this->instrumentService->delete($market_instrument);

        return response()->json(null, 204);
    }
}
