<?php

namespace App\Http\Controllers\V1\Discount;

use App\Http\Controllers\Controller;
use App\Http\HttpResponse;
use App\Http\Requests\Discount\PaginateDiscountRequest;
use App\Http\Requests\Discount\UpsertDiscountRequest;
use App\Http\Resources\Discount\DiscountPaginateResourceCollection;
use App\Http\Resources\Discount\DiscountResource;
use App\Jobs\Discount\PaginateDiscount;
use App\Jobs\Discount\UpsertDiscount;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @group Discounts
 *
 * APIs for managing discounts
 *
 * @authenticated
 */
class DiscountController extends Controller
{
    /**
     * List discounts (pagination, search, sorting)
     */
    public function index(PaginateDiscountRequest $request): JsonResponse
    {
        $request->validated();

        $paginateCollection = $this->dispatchSync(PaginateDiscount::fromRequest($request));

        return HttpResponse::make()
            ->setMessage('Discounts list')
            ->setData(DiscountPaginateResourceCollection::make($paginateCollection))
            ->ok();
    }

    /**
     * Show a discount
     */
    public function show(Discount $discount): JsonResponse
    {
        $discount->loadMissing(['products']);

        return HttpResponse::make()
            ->setMessage('Discount details')
            ->setData(DiscountResource::make($discount))
            ->ok();
    }

    /**
     * Create discount (bonus CRUD)
     */
    public function store(UpsertDiscountRequest $request): JsonResponse
    {
        $request->validated();

        $created = $this->dispatchSync(UpsertDiscount::forCreate($request));

        return HttpResponse::make()
            ->setMessage('Discount created')
            ->setData(DiscountResource::make($created))
            ->ok(Response::HTTP_CREATED);
    }

    /**
     * Update discount (bonus CRUD)
     */
    public function update(UpsertDiscountRequest $request, Discount $discount): JsonResponse
    {
        $request->validated();

        $updated = $this->dispatchSync(UpsertDiscount::forUpdate($request, $discount));

        return HttpResponse::make()
            ->setMessage('Discount updated')
            ->setData(DiscountResource::make($updated))
            ->ok();
    }

    /**
     * Delete discount (bonus CRUD)
     */
    public function destroy(Discount $discount): JsonResponse
    {
        $discount->delete();

        return HttpResponse::make()
            ->setMessage('Discount deleted')
            ->ok();
    }
}
