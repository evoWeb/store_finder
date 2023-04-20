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

use Evoweb\StoreFinder\Middleware\CategoryMiddleware;
use Psr\Http\Message\ServerRequestInterface;

final class ModifyCategoriesMiddlewareOutputEvent
{
    private CategoryMiddleware $categoryMiddleware;

    private array $categories;

    private ServerRequestInterface $request;

    public function __construct(
        CategoryMiddleware $categoryMiddleware,
        array $categories,
        ServerRequestInterface $request
    ) {
        $this->categoryMiddleware = $categoryMiddleware;
        $this->categories = $categories;
        $this->request = $request;
    }

    public function getCategoryMiddleware(): CategoryMiddleware
    {
        return $this->categoryMiddleware;
    }

    public function setCategoryMiddleware(CategoryMiddleware $categoryMiddleware): self
    {
        $this->categoryMiddleware = $categoryMiddleware;

        return $this;
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

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }
}
