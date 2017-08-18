<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use App\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // FIRST CREATE PERMISSIONS
        $perm_super = Permission::all();
        
        $perm_admin = Permission::where('name','role-list')
                              ->orWhere('name','user-list')
                              ->orWhere('name','user-create')
                              ->orWhere('name','user-edit')
                              ->orWhere('name','user-delete')
                              ->orWhere('name','group-list')
                              ->orWhere('name','group-create')
                              ->orWhere('name','group-edit')
                              ->orWhere('name','group-delete')
                              ->orWhere('name','sensor-list')
                              ->orWhere('name','sensor-create')
                              ->orWhere('name','sensor-edit')
                              ->orWhere('name','sensor-delete')
                              ->get();
                              

        // Roles
        $super = new Role();
        $super->name         = 'superadmin';
        $super->display_name = 'Super administrator'; // optional
        $super->description  = 'User is the master of the system, and can edit everything'; // optional
        $super->save();
        $super->attachPermissions($perm_super); // all roles
        
        $admin = new Role();
        $admin->name         = 'admin';
        $admin->display_name = 'Administrator'; // optional
        $admin->description  = 'User is allowed to manage users, groups and sensors'; // optional
        $admin->save();
        $admin->attachPermissions($perm_admin); // all roles

        // Users
        $user = new User();
        $user->name     = 'Admin';
        $user->email    = 'admin@beep.nl';
        $user->password = bcrypt('admin');
        $user->api_token= str_random(60);
        $user->remember_token = str_random(10);
        $user->save();
        $user->attachRole($super);

    }
}
