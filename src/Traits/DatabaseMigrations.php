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

            // Get the table names and drop them
            foreach ($db->execute('SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'') as $table) {
                $db->execute('DROP TABLE ' . $table[0]);
            }

            $db->execute('SET FOREIGN_KEY_CHECKS=1');
        });
    }
}