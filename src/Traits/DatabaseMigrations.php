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

        $migrations->getManager()
                   ->getEnvironment('default')
                   ->getAdapter()
                   ->disconnect();

        $this->beforeApplicationDestroyed(function () use ($migrations) {
            /** @var \Cake\Database\Connection $db */
            $db = ConnectionManager::get('test');

            $db->disableForeignKeys();

            // Get the table names and drop them
            foreach ($db->execute('SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'') as $table) {
                $db->execute('DROP TABLE ' . $table[0]);
            }

            $db->enableForeignKeys();

            $db->disconnect();
        });
    }
}
