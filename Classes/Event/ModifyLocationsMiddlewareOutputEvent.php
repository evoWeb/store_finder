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

use Evoweb\StoreFinder\Middleware\LocationMiddleware;
use Psr\Http\Message\ServerRequestInterface;

final class ModifyLocationsMiddlewareOutputEvent
{
    private $locationMiddleware;

    private $locations;

    private $request;

    public function __construct(LocationMiddleware $locationMiddleware, array $locations, ServerRequestInterface $request)
    {
        $this->locationMiddleware = $locationMiddleware;
        $this->locations = $locations;
        $this->request = $request;
    }

    /**
     * @return LocationMiddleware
     */
    public function getLocationMiddleware(): LocationMiddleware
    {
        return $this->locationMiddleware;
    }

    /**
     * @param LocationMiddleware $locationMiddleware
     */
    public function setLocationMiddleware(LocationMiddleware $locationMiddleware): self
    {
        $this->locationMiddleware = $locationMiddleware;

        return $this;
    }

    /**
     * @return array
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param array $locations
     */
    public function setLocations(array $locations): self
    {
        $this->locations = $locations;

        return $this;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
