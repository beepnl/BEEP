<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeToRoleUserAndPermissionUserTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            if (Schema::hasTable('role_user') && !Schema::hasColumn('role_user','organization_id')) 
            {
                Schema::table('role_user', function (Blueprint $table) {
                    $table->unsignedInteger('organization_id')->nullable();
                    $table->string('user_type')->default('App\\\User');
                });
                Schema::table('role_user', function (Blueprint $table) {
                    $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');
                    $table->unique(['user_id', 'role_id', 'user_type', 'organization_id'], 'unique_role_user_type_organization_constraint');
                });
            }

            // Create table for associating permissions to users (Many To Many Polymorphic)
            if (!Schema::hasTable('permission_user')) 
            {
                Schema::create('permission_user', function (Blueprint $table) {
                    $table->unsignedInteger('permission_id');
                    $table->unsignedInteger('user_id');
                    $table->string('user_type')->default('App\\\User');
                    $table->unsignedInteger('organization_id')->nullable();
                    $table->unique(['user_id', 'permission_id', 'user_type', 'organization_id'], 'unique_permission_user_constraint');
                });
                Schema::table('permission_user', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
                    $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
                    $table->foreign('organization_id')->references('id')->on('organizations')->onUpdate('cascade');

                });
            }

            if (Schema::hasTable('roles') && \App\Role::where('name','api-gateway')->count() == 0) 
            {
                $api_role = \App\Role::create(['name'=>'api-gateway', 'display_name'=>'API gateway access role', 'description'=>'API gateway access role to get minimal list of (cached) data for API gateway auth. NB: Set user token in AWS Lambda config variables.']);
                $api_user = \App\User::create(['name'=>'hcs-api-gateway-user', 'email'=>'hcs@iconize.nl']);
                $api_user->attachRole($api_role->id);
            }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('permission_user')) 
        {
            Schema::table('permission_user', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
                $table->dropForeign(['permission_id']);
                $table->dropForeign(['user_id']);
            });
            Schema::dropIfExists('permission_user');

        }
        // Don't delete table, only organization link and new columns
        if (Schema::hasTable('role_user')) 
        {
            Schema::table('role_user', function(Blueprint $table)
            {
                $table->dropForeign(['organization_id']);
                $table->dropUnique('unique_role_user_type_organization_constraint');
                $table->dropColumn('organization_id');
                $table->dropColumn('user_type');
            });
        }
    }
}
