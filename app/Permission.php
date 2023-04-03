<?php

namespace App;

use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
	public $fillable = ['name','display_name','description'];

    protected $hidden = ['id', 'pivot', 'updated_at', 'created_at', 'description'];
    
    public $guarded = [];

	public static function updatePermissions()
    {
        $permission = [
        	[
        		'name' => 'role-list',
        		'display_name' => 'Display role list',
        		'description' => 'View a list of roles'
        	],
        	[
        		'name' => 'role-create',
        		'display_name' => 'Create role',
        		'description' => 'Create new role'
        	],
        	[
        		'name' => 'role-edit',
        		'display_name' => 'Edit role',
        		'description' => 'Edit role'
        	],
        	[
        		'name' => 'role-delete',
        		'display_name' => 'Delete role',
        		'description' => 'Delete role'
        	],
            [
                'name' => 'user-list',
                'display_name' => 'Display user list',
                'description' => 'View a list of users'
            ],
            [
                'name' => 'user-create',
                'display_name' => 'Create user',
                'description' => 'Create new user'
            ],
            [
                'name' => 'user-edit',
                'display_name' => 'Edit user',
                'description' => 'Edit user'
            ],
            [
                'name' => 'user-delete',
                'display_name' => 'Delete user',
                'description' => 'Delete user'
            ],
        	[
        		'name' => 'group-list',
        		'display_name' => 'Display group list',
        		'description' => 'View a list of groups'
        	],
        	[
        		'name' => 'group-create',
        		'display_name' => 'Create group',
        		'description' => 'Create new group'
        	],
        	[
        		'name' => 'group-edit',
        		'display_name' => 'Edit group',
        		'description' => 'Edit group'
        	],
        	[
        		'name' => 'group-delete',
        		'display_name' => 'Delete group',
        		'description' => 'Delete group'
        	],
            [
                'name' => 'sensor-list',
                'display_name' => 'Display sensor list',
                'description' => 'View a list of sensors'
            ],
            [
                'name' => 'sensor-create',
                'display_name' => 'Create sensor',
                'description' => 'Create new sensor'
            ],
            [
                'name' => 'sensor-edit',
                'display_name' => 'Edit sensor',
                'description' => 'Edit sensor'
            ],
            [
                'name' => 'sensor-delete',
                'display_name' => 'Delete sensor',
                'description' => 'Delete sensor'
            ],
            [
                'name' => 'taxonomy-list',
                'display_name' => 'Display taxonomy list',
                'description' => 'View a list of the taxonomy'
            ],
            [
                'name' => 'taxonomy-create',
                'display_name' => 'Create taxonomy',
                'description' => 'Create new taxonomy items'
            ],
            [
                'name' => 'taxonomy-edit',
                'display_name' => 'Edit taxonomy',
                'description' => 'Edit taxonomy items'
            ],
            [
                'name' => 'taxonomy-delete',
                'display_name' => 'Delete taxonomy',
                'description' => 'Delete taxonomy items'
            ],
            [
                'name' => 'language-list',
                'display_name' => 'Display language list',
                'description' => 'View a list of the language'
            ],
            [
                'name' => 'language-create',
                'display_name' => 'Create language',
                'description' => 'Create new language items'
            ],
            [
                'name' => 'language-edit',
                'display_name' => 'Edit language',
                'description' => 'Edit language items'
            ],
            [
                'name' => 'language-delete',
                'display_name' => 'Delete language',
                'description' => 'Delete language items'
            ],
            [
                'name' => 'translation-list',
                'display_name' => 'Display translation list',
                'description' => 'View a list of the translation'
            ],
            [
                'name' => 'translation-create',
                'display_name' => 'Create translation',
                'description' => 'Create new translation items'
            ],
            [
                'name' => 'translation-edit',
                'display_name' => 'Edit translation',
                'description' => 'Edit translation items'
            ],
            [
                'name' => 'translation-delete',
                'display_name' => 'Delete translation',
                'description' => 'Delete translation items'
            ]
        ];

        foreach ($permission as $key => $value) 
        {
        	//print("Checking permission $key: ".$value['display_name']."\r\n");

            if (Permission::where('name', $value['name'])->count() > 0)
                continue;
                
            $p = Permission::create($value);

            //die(print_r($p->toArray()));

	        // if (Role::where('name', 'superadmin')->count() == 1)
	        // {
	        // 	Role::where('name', 'superadmin')->first()->attachPermissions([$p]);
	        // }
	        print("Added permission ".$value['display_name']."\r\n");
        }

    }
}
