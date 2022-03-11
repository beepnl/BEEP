<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeDbToUtf8mb4 extends Migration
{
    /**
 * Run the migrations.
 *
 * @return void
 */
public function up()
{
    $dryRun = false;
    $this->convertDb('mysql', 'utf8mb4', 'utf8mb4_unicode_ci', $dryRun);
}

/**
 * Reverse the migrations.
 *
 * @return void
 */
public function down()
{
    $dryRun = false;
    $this->convertDb('mysql', 'utf8', 'utf8_unicode_ci', $dryRun);
}

private function convertDb($connection, $charset, $collate, $dryRun)
{
    $dbName = config("database.connections.{$connection}.database");

    $varchars = \DB::connection($connection)
        ->select(\DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE DATA_TYPE = 'varchar' AND (CHARACTER_SET_NAME != '{$charset}' OR COLLATION_NAME != '{$collate}') AND TABLE_SCHEMA = '{$dbName}'"));

    // Check if shrinking field size will truncate!
    $skip = [];  // List of table.column that will be handled manually
    $indexed = [];
    if ($charset == 'utf8mb4') {
        $error = false;
        foreach($varchars as $t) {
            if ($t->CHARACTER_MAXIMUM_LENGTH > 191) {
                $key = "{$t->TABLE_NAME}.{$t->COLUMN_NAME}";

                // Check if column is indexed
                $index = \DB::connection($connection)
                    ->select(\DB::raw("SHOW INDEX FROM `{$t->TABLE_NAME}` where column_name = '{$t->COLUMN_NAME}'"));
                $indexed[$key] = count($index) ? true : false;

                if (count($index)) {
                    $result = \DB::connection($connection)
                        ->select(\DB::raw("SELECT count(*) as `count` from `{$t->TABLE_NAME}` where length(`{$t->COLUMN_NAME}`) > 191"));
                    if ($result[0]->count > 0) {
                        echo "-- DATA TRUNCATION: {$t->TABLE_NAME}.{$t->COLUMN_NAME}({$t->CHARACTER_MAXIMUM_LENGTH}) => {$result[0]->count}" . PHP_EOL;
                        if (!in_array($key, $skip)) {
                            $error = true;
                        }
                    }
                }
            }
        }
        if ($error) {
            throw new \Exception('Aborting due to data truncation');
        }
    }

    $query = "SET FOREIGN_KEY_CHECKS = 0";
    $this->dbExec($query, $dryRun, $connection);

    $query = "ALTER SCHEMA `{$dbName}` DEFAULT CHARACTER SET {$charset} DEFAULT COLLATE {$collate}";
    $this->dbExec($query, $dryRun, $connection);

    $tableChanges = [];

    // Convert varchar columns
    foreach($varchars as $t) {
        $key = "{$t->TABLE_NAME}.{$t->COLUMN_NAME}";
        if (!in_array($key, $skip)) {
            if ($charset == 'utf8mb4' && $t->CHARACTER_MAXIMUM_LENGTH > 191 && $indexed["{$t->TABLE_NAME}.{$t->COLUMN_NAME}"]) {
                $tableChanges["{$t->TABLE_NAME}"][] = "CHANGE `{$t->COLUMN_NAME}` `{$t->COLUMN_NAME}` VARCHAR(191) CHARACTER SET {$charset} COLLATE {$collate}";
                echo "-- Shrinking: {$t->TABLE_NAME}.{$t->COLUMN_NAME}({$t->CHARACTER_MAXIMUM_LENGTH})" . PHP_EOL;
            } else if ($charset == 'utf8' && $t->CHARACTER_MAXIMUM_LENGTH == 191) {
                $tableChanges["{$t->TABLE_NAME}"][] = "CHANGE `{$t->COLUMN_NAME}` `{$t->COLUMN_NAME}` VARCHAR(255) CHARACTER SET {$charset} COLLATE {$collate}";
                echo "-- Expanding: {$t->TABLE_NAME}.{$t->COLUMN_NAME}({$t->CHARACTER_MAXIMUM_LENGTH})";
            } else {
                $tableChanges["{$t->TABLE_NAME}"][] = "CHANGE `{$t->COLUMN_NAME}` `{$t->COLUMN_NAME}` VARCHAR({$t->CHARACTER_MAXIMUM_LENGTH}) CHARACTER SET {$charset} COLLATE {$collate}";
            }
        }
    }

    // Convert text like columns
    $texts = \DB::connection($connection)
        ->select(\DB::raw("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE DATA_TYPE LIKE '%text%' AND (CHARACTER_SET_NAME != '{$charset}' OR COLLATION_NAME != '{$collate}') AND TABLE_SCHEMA = '{$dbName}'"));
    foreach($texts as $t) {
        $tableChanges["{$t->TABLE_NAME}"][] = "CHANGE `{$t->COLUMN_NAME}` `{$t->COLUMN_NAME}` {$t->DATA_TYPE} CHARACTER SET {$charset} COLLATE {$collate}";
    }

    // Convert tables
    $tables = \DB::connection($connection)
        ->select(\DB::raw("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_COLLATION != '{$collate}' AND TABLE_SCHEMA = '{$dbName}';"));
    foreach($tables as $t) {
        $tableChanges["{$t->TABLE_NAME}"][] = "CONVERT TO CHARACTER SET {$charset} COLLATE {$collate}";
        $tableChanges["{$t->TABLE_NAME}"][] = "DEFAULT CHARACTER SET={$charset} COLLATE={$collate}";
    }

    foreach ($tableChanges as $table => $changes) {
        $query = "ALTER TABLE `{$table}` ".implode(",\n", $changes);
        $this->dbExec($query, $dryRun, $connection);
    }

    $query = "SET FOREIGN_KEY_CHECKS = 1";
    $this->dbExec($query, $dryRun, $connection);

    echo "-- {$dbName} CONVERTED TO {$charset}-{$collate}" . PHP_EOL;
}

private function dbExec($query, $dryRun, $connection)
{
    if ($dryRun) {
        echo $query . ';' . PHP_EOL;
    } else {
        \DB::connection($connection)->getPdo()->exec($query);
    }
}
}
