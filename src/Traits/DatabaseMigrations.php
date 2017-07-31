<?php

namespace TestDummy\Traits;

use Cake\Datasource\ConnectionManager;
use Migrations\Migrations;

trait DatabaseMigrations
{

    public function runDatabaseMigrations()
    {
        $migrations = new Migrations(['connection' => 'test', 'source' => 'Migrations']);
        $migrations->migrate();

        $this->beforeApplicationDestroyed(function () use ($migrations) {
            $db = ConnectionManager::get('test');

            $db->execute('SET FOREIGN_KEY_CHECKS=0');

            // Create a schema collection.
            $collection = $db->schemaCollection();

            // Get the table names
            foreach ($collection->listTables() as $table) {
                $db->execute('DROP TABLE ' . $table);
            }

            $db->execute('SET FOREIGN_KEY_CHECKS=1');
        });
    }
}