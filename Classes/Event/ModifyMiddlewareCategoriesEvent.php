<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Event;

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

use Evoweb\StoreFinder\Middleware\StoreFinderMiddleware;
use Psr\Http\Message\ServerRequestInterface;

final class ModifyMiddlewareCategoriesEvent
{
    public function __construct(
        protected StoreFinderMiddleware $storeFinderMiddleware,
        protected ServerRequestInterface $request,
        protected array $categories,
    ) {
    }

    public function getStoreFinderMiddleware(): StoreFinderMiddleware
    {
        return $this->storeFinderMiddleware;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }
}
