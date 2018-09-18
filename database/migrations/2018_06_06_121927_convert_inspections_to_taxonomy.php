<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ChecklistFactory;
use App\User;
use App\Inspection;
use App\Role;

class ConvertInspectionsToTaxonomy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function __construct()
    {
        $this->checklistFactory = new ChecklistFactory;
        $this->debug = false;
    }


    public function up()
    {
        if (Schema::hasTable('categories')) 
        {
            echo("ConvertInspectionsToTaxonomy disableForeignKeyConstraints...\r\n");
            Schema::disableForeignKeyConstraints();

            if (Schema::hasTable('inspections') == false || Inspection::all()->count() == 0)
            {
                $sql = Storage::get('new_taxonomy_tables.sql');
                if ($sql)
                {
                    // Replace tables
                    echo("ConvertInspectionsToTaxonomy replacing new db-tables with test.beep db tables...\r\n");
                    echo(DB::unprepared($sql)."\r\n");
                }
                else
                {
                    echo("ConvertInspectionsToTaxonomy - ERROR - No SQL found, NOT replaced new db-tables\r\n");
                }
            }
            else
            {
                echo("ConvertInspectionsToTaxonomy already replaced db tables...\r\n");
            }

            // add missing roles and permissions
            Role::updateRoles();

            // do the magic
            if (Schema::hasTable('actions') && Schema::hasTable('conditions'))
            {
                // Convert old category_ids
                echo("ConvertInspectionsToTaxonomy converting user data...\r\n");
                
                if ($this->debug)
                {
                    $users = User::where('id','<=', 2)->get(); //User::all(); 
                }
                else
                {
                    $users = User::all(); 
                }

                $this->checklistFactory->convertUsersChecklists($users, $this->debug);
                
                if($this->debug == false)
                {
                    Schema::dropIfExists('actions');
                    Schema::dropIfExists('conditions');
                }
            }
            else
            {
                echo("ConvertInspectionsToTaxonomy - ERROR - No table \'categories\' found!");
            }

            // Create new foreign id's
            if (Schema::hasTable('hive_types'))
            {
                Schema::table('hives', function (Blueprint $table) 
                {
                    $table->dropForeign(['hive_type_id']);
                    $table->foreign('hive_type_id')->references('id')->on('categories')->onUpdate('cascade');
                    Schema::dropIfExists('hive_types');
                });
            }
            if (Schema::hasTable('bee_races'))
            {
                Schema::table('queens', function (Blueprint $table) 
                {
                    $table->dropForeign(['race_id']);
                    $table->foreign('race_id')->references('id')->on('categories')->onUpdate('cascade');
                    Schema::dropIfExists('bee_races');
                });
            }

            echo("ConvertInspectionsToTaxonomy enableForeignKeyConstraints...\r\n");
            Schema::enableForeignKeyConstraints();
        }
        else
        {
            echo("ConvertInspectionsToTaxonomy - ERROR - table actions, conditions, or categories not available (already migrated)\r\n");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
