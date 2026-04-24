<?php

namespace App\Jobs\Product;

use App\Http\Requests\Product\PaginateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PaginateProduct
{
    use Dispatchable;

    public function __construct(
        private readonly int $page,
        private int $perPage,
        private ?string $search,
        private ?int $categoryId,
        private bool $includeSubcategories,
        private string $sortColumn,
        private string $sortDirection,
    ) {}

    public static function fromRequest(PaginateProductRequest $request): static
    {
        return new static(
            $request->getPage(),
            $request->getPerPage(),
            $request->getSearch(),
            $request->getCategoryId(),
            $request->getIncludeSubcategories(),
            $request->getSortColumn(),
            $request->getSortDirection(),
        );
    }

    public function handle()
    {
        $query = Product::query()
            ->with([
                'category',
                'discounts',
            ]);

        $this->applySearch($query);
        $this->applyCategoryFilter($query);

        return $query
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($this->perPage);
    }

    private function applySearch(Builder $query): void
    {
        $term = trim((string) $this->search);
        if ($term === '') {
            return;
        }

        $driver = DB::connection()->getDriverName();

        $query->where(function (Builder $q) use ($term, $driver) {
            if ($driver === 'mysql') {
                $q->whereRaw('MATCH(name, description) AGAINST (? IN BOOLEAN MODE)', [$term.'*'])
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");

                return;
            }

            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    private function applyCategoryFilter(Builder $query): void
    {
        if (! $this->categoryId) {
            return;
        }

        $ids = collect([$this->categoryId]);
        if ($this->includeSubcategories) {
            $ids = $ids->merge($this->descendantCategoryIds($this->categoryId));
        }

        $query->whereIn('category_id', $ids->unique()->values()->all());
    }

    /**
     * @return Collection<int, int>
     */
    private function descendantCategoryIds(int $rootId): Collection
    {
        $seen = collect([$rootId]);
        $frontier = collect([$rootId]);

        while ($frontier->isNotEmpty()) {
            $children = Category::query()
                ->whereIn('parent_id', $frontier->all())
                ->pluck('id');

            $new = $children->diff($seen);
            if ($new->isEmpty()) {
                break;
            }

            $seen = $seen->merge($new);
            $frontier = $new;
        }

        return $seen->diff([$rootId])->values();
    }
}

