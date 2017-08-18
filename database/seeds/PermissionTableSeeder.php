<?php

use Illuminate\Database\Seeder;
use App\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
            ]
        ];

        foreach ($permission as $key => $value) {
        	Permission::create($value);
        }
    }
}