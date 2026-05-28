<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        foreach ($users as $user) {
            if (isset($user->policy_accepted) && $user->email_verified_at == null) {
                $user->email_verified_at = '2018-05-25 00:00:00';
                $user->save();
            }
        }
    }
}
