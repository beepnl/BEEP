<?php
 
namespace App\Transformer;
 
class UserTransformer {
 
    public function transform($user) {
        return [
            'id' 		=> $user->id,
            'name' 		=> $user->name,
            'email' 	=> $user->email,
            'created' 	=> $user->created_at
        ];
    }
 
}