<?php

namespace App\Services;

use App\Models\PerformanceRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PerformanceRecordQuery
{
    public function __construct(private int $perPage = 25)
    {
    }

    public function apply(array $filters): LengthAwarePaginator
    {
        $query = PerformanceRecord::query()
            ->select(['id', 'reference', 'name', 'email', 'status', 'amount', 'created_at']);

        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyStatus($query, $filters['status'] ?? null);
        $this->applyDateFrom($query, $filters['date_from'] ?? null);
        $this->applyMinAmount($query, $filters['min_amount'] ?? null);
        $this->applyMaxAmount($query, $filters['max_amount'] ?? null);

        return $query
            ->orderBy('id')
            ->paginate($this->perPage, ['*'], 'page', $filters['page'] ?? 1);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function (Builder $query) use ($search) {
            $query->where('reference', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    protected function applyStatus(Builder $query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        $query->where('status', $status);
    }

    protected function applyDateFrom(Builder $query, ?string $date): void
    {
        if (empty($date)) {
            return;
        }

        $query->where('created_at', '>=', $date.' 00:00:00');
    }

    protected function applyMinAmount(Builder $query, mixed $minAmount): void
    {
        if ($minAmount === null || $minAmount === '') {
            return;
        }

        $query->where('amount', '>=', $minAmount);
    }

    protected function applyMaxAmount(Builder $query, mixed $maxAmount): void
    {
        if ($maxAmount === null || $maxAmount === '') {
            return;
        }

        $query->where('amount', '<=', $maxAmount);
    }
}
