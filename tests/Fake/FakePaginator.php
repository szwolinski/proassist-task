<?php

declare(strict_types=1);

namespace App\Tests\Fake;

use ApiPlatform\State\Pagination\PaginatorInterface;
use ArrayIterator;
use Traversable;

final class FakePaginator implements PaginatorInterface, \IteratorAggregate
{
    public function __construct(
        private array $items,
        private float $totalItems,
        private float $currentPage,
        private float $itemsPerPage
    ) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
    public function getTotalItems(): float
    {
        return $this->totalItems;
    }
    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }
    public function getItemsPerPage(): float
    {
        return $this->itemsPerPage;
    }
    public function count(): int
    {
        return count($this->items);
    }

    public function getLastPage(): float
    {
        return ceil($this->totalItems / $this->itemsPerPage);
    }
}
