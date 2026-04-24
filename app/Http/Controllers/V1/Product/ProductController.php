<?php

namespace App\Http\Controllers\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\HttpResponse;
use App\Http\Requests\Product\PaginateProductRequest;
use App\Http\Requests\Product\UpsertProductRequest;
use App\Http\Resources\Product\ProductPaginateResourceCollection;
use App\Http\Resources\Product\ProductResource;
use App\Jobs\Product\PaginateProduct;
use App\Jobs\Product\UpsertProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @group Products
 *
 * APIs for managing products
 *
 * @authenticated
 */
class ProductController extends Controller
{
    /**
     * Allow to list all products
     */
    public function index(PaginateProductRequest $request): JsonResponse
    {
        $request->validated();

        $paginateCollection = $this->dispatchSync(PaginateProduct::fromRequest($request));

        return HttpResponse::make()
            ->setMessage(trans('messages.product.list'))
            ->setData(ProductPaginateResourceCollection::make($paginateCollection))
            ->ok();
    }

    /**
     * Show a single product (includes computed discounts)
     */
    public function show(Product $product): JsonResponse
    {
        $product->loadMissing([
            'category',
            'discounts',
        ]);

        return HttpResponse::make()
            ->setMessage(trans('messages.product.show'))
            ->setData(ProductResource::make($product))
            ->ok();
    }

    /**
     * Allow to create the product
     */
    public function store(UpsertProductRequest $request): JsonResponse
    {
        $request->validated();

        $created = $this->dispatchSync(UpsertProduct::forCreate($request));

        return HttpResponse::make()
            ->setMessage(trans('messages.product.create'))
            ->setData(ProductResource::make($created))
            ->ok(Response::HTTP_CREATED);
    }

    /**
     * Allow to update the product
     */
    public function update(UpsertProductRequest $request, Product $product): JsonResponse
    {
        $request->validated();

        $updated = $this->dispatchSync(UpsertProduct::forUpdate($request, $product));

        return HttpResponse::make()
            ->setMessage(trans('messages.product.update'))
            ->setData(ProductResource::make($updated))
            ->ok();
    }

    /**
     * Allow to delete the product
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return HttpResponse::make()
            ->setMessage(trans('messages.product.delete'))
            ->ok();
    }
}
