<?php

return [
    'storefinder:import' => [
        'class' => \Evoweb\StoreFinder\Command\ImportLocationsCommand::class,
    ],
    'storefinder:geocode' => [
        'class' => \Evoweb\StoreFinder\Command\GeocodeLocationsCommand::class,
    ],
];
