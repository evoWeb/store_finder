#!/usr/bin/env php
<?php

// needs fakerphp/faker to be installed

require_once '../../../../../vendor/autoload.php';

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

$additionalConfigurationFile = '/home/www/AdditionalConfiguration.php';
$GLOBALS['TYPO3_CONF_VARS'] = require '../../../LocalConfiguration.php';
if (@file_exists($additionalConfigurationFile)) {
    require $additionalConfigurationFile;
}

$seeder = new class {
    /**
     * @var int
     */
    protected $pid = 3;

    /**
     * @var string
     */
    protected $table = 'tx_storefinder_domain_model_location';

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var Connection
     */
    protected $databaseConnection;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create('de_DE');
        $this->faker->seed(1974);

        $this->databaseConnection = (new ConnectionPool())->getConnectionForTable($this->table);
        $this->databaseConnection->connect();
    }

    public function generateRows(int $amount = 10): void
    {
        $this->databaseConnection->delete($this->table, ['pid' => $this->pid]);
        for ($loop = 0; $loop < $amount; $loop++) {
            $this->databaseConnection->insert($this->table, $this->getData());
        }
    }

    public function getData(): array
    {
        $f = $this->faker;
        return [
            'pid' => $this->pid,
            'name' => $f->company(),
            'address' => $f->streetAddress(),
            'person' => $f->name(),
            'city' => $f->city(),
            'zipcode' => $f->postcode(),
            'state' => $f->numberBetween(79, 94),
            'country' => 54,
            'phone' => $f->phoneNumber(),
            'mobile' => $f->phoneNumber(),
            'fax' => $f->phoneNumber(),
            'hours' => $f->text(),
            'email' => $f->safeEmail(),
            'url' => $f->url(),
            'latitude' => str_replace(',', '.', $f->latitude(47.2, 55.1)),
            'longitude' => str_replace(',', '.', $f->longitude(5.5, 15.5)),
        ];
    }
};

$seeder->generateRows(100);
