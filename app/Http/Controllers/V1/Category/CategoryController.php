<?php

namespace App\Http\Controllers\V1\Category;

use App\Http\Controllers\Controller;
use App\Http\HttpResponse;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

/**
 * @group Categories
 *
 * APIs for browsing categories (nested)
 *
 * @authenticated
 */
class CategoryController extends Controller
{
    /**
     *  Allow to list all categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->with('children.children.children')
            ->orderBy('name')
            ->get();

        return HttpResponse::make()
            ->setMessage(trans('messages.category.list'))
            ->setData(CategoryResource::collection($categories))
            ->ok();
    }
}
