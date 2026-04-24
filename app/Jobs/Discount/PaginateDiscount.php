<?php

namespace App\Jobs\Discount;

use App\Http\Requests\Discount\PaginateDiscountRequest;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;

class PaginateDiscount
{
    use Dispatchable;

    public function __construct(
        private readonly int $page,
        private int $perPage,
        private ?string $search,
        private string $sortColumn,
        private string $sortDirection,
    ) {}

    public static function fromRequest(PaginateDiscountRequest $request): static
    {
        return new static(
            $request->getPage(),
            $request->getPerPage(),
            $request->getSearch(),
            $request->getSortColumn(),
            $request->getSortDirection(),
        );
    }

    public function handle()
    {
        $query = Discount::query()->with(['products']);

        $term = trim((string) $this->search);
        if ($term !== '') {
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', "%{$term}%");
            });
        }

        return $query
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($this->perPage);
    }
}
