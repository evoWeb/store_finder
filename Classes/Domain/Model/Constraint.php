<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Domain\Model;

class Constraint extends Location
{
    protected string $search = '';

    protected array $category = [];

    protected int $radius = 0;

    protected int $limit = 0;

    protected int $page = 0;

    public function setSearch(string $search): void
    {
        $this->search = $search;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setCategory(array $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): array
    {
        return array_filter($this->category);
    }

    public function setRadius(int $radius): void
    {
        $this->radius = $radius;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
