<?php

namespace TestDummy\Traits;

use Migrations\Migrations;

trait DatabaseMigrations
{

    public function runDatabaseMigrations()
    {
        $migrations = new Migrations(['connection' => 'test', 'source' => 'Migrations']);
        $migrations->migrate();

        $this->beforeApplicationDestroyed(function () use ($migrations) {
            $migrations->rollback(['target' => 0]);
        });
    }
}