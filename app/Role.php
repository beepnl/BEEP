<?php

namespace App;

use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
	public $fillable = ['name','display_name','description'];

  public $guarded = [];

	public static function updateRoles()
    {
        // FIRST CREATE PERMISSIONS
        Permission::updatePermissions();

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
                              ->orWhere('name','taxonomy-list')
                              ->orWhere('name','taxonomy-create')
                              ->orWhere('name','taxonomy-edit')
                              ->orWhere('name','taxonomy-delete')
                              ->get();
                              
        $perm_manag = Permission::where('name','role-list')
                              ->orWhere('name','user-list')
                              ->orWhere('name','group-list')
                              ->orWhere('name','group-create')
                              ->orWhere('name','group-edit')
                              ->orWhere('name','group-delete')
                              ->orWhere('name','sensor-list')
                              ->orWhere('name','sensor-create')
                              ->orWhere('name','sensor-edit')
                              ->get();

        $perm_taxonomy_editor = Permission::where('name','taxonomy-list')
                              ->orWhere('name','taxonomy-create')
                              ->orWhere('name','taxonomy-edit')
                              ->get();

        $perm_language_editor = Permission::where('name','language-list')
                              ->orWhere('name','language-create')
                              ->orWhere('name','translation-list')
                              ->orWhere('name','translation-create')
                              ->orWhere('name','translation-edit')
                              ->get();
                              

        // Roles
        if (Role::where('name', 'superadmin')->count() == 0)
        {
          $super = new Role();
          $super->name         = 'superadmin';
          $super->display_name = 'Super administrator'; // optional
          $super->description  = 'User is the master of the system, and can edit everything'; // optional
          $super->save();
          $super->attachPermissions($perm_super); // all roles
        }

        if (Role::where('name', 'admin')->count() == 0)
        {
          $admin = new Role();
          $admin->name         = 'admin';
          $admin->display_name = 'Administrator'; // optional
          $admin->description  = 'User is allowed to manage users, groups and sensors'; // optional
          $admin->save();
          $admin->attachPermissions($perm_admin); 
        }

        if (Role::where('name', 'manager')->count() == 0)
        {
          $manag = new Role();
          $manag->name         = 'manager';
          $manag->display_name = 'Sensor manager'; // optional
          $manag->description  = 'User is allowed to manage groups and sensors'; // optional
          $manag->save();
          $manag->attachPermissions($perm_manag); 
        }

        if (Role::where('name', 'taxonomy')->count() == 0)
        {
          $taxon = new Role();
          $taxon->name         = 'taxonomy';
          $taxon->display_name = 'Taxonomy editor'; // optional
          $taxon->description  = 'User is allowed to create and edit taxonomy items'; // optional
          $taxon->save();
          $taxon->attachPermissions($perm_taxonomy_editor); 
        }

        if (Role::where('name', 'translator')->count() == 0)
        {
          $trans = new Role();
          $trans->name         = 'translator';
          $trans->display_name = 'Translation editor'; // optional
          $trans->description  = 'User is allowed to create languages and edit translations'; // optional
          $trans->save();
          $trans->attachPermissions($perm_language_editor); 
        }
    }
}
