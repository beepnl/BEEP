<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mail;
use App\Group;
use App\Hive;
use App\User;
use App\Mail\GroupInvitation;

use DB;
use Validator;

class GroupController extends Controller
{

    public function index(Request $request, $code=200)
    {
        $groups = $request->user()->groups()->orderBy('name')->get();
        return response()->json($groups, $code);
    }

    public function checktoken(Request $request)
    {
        $validator = Validator::make($request->only('token','group_id'), [
            'token'     => 'required|exists:group_user,token',
            'group_id'  => 'required|exists:group_user,group_id',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
        }
        else
        {
            $valid_data = $validator->validated();
            $res = DB::table('group_user')->where('token',$valid_data['token'])->where('group_id',$valid_data['group_id'])->update(['invited'=>null,'accepted'=>now(),'declined'=>null,'token'=>null]);
            if ($res)
                return response()->json(['message'=>'group_activated']);
        }
        return response()->json('token_error',500);
    }


    public function store(Request $request)
    {
        $requestData = $request->only(['name','description','hex_color']);
        $group       = Group::create($requestData);
        $request->user()->groups()->attach($group, ['creator'=>true,'admin'=>true,'accepted'=>now()]);
        
        $this->syncHives($request, $group);
        
        $msg = $this->syncUsers($request, $group);
        if (gettype($msg) == 'array' && isset($msg['message']))
            return response()->json($msg, 201);

        return $this->index($request, 201);
    }


    public function show(Request $request, $id)
    {
        $group = $request->user()->groups()->find($id);
        if ($group)
        {
            return response()->json($group); // formatting for jsTree
        }
        return response()->json(null, 404);
    }


    public function update(Request $request, $id)
    {
        $requestData = $request->only(['id','name','description','hex_color']);
        $group = $request->user()->groups()->find($id);

        if ($group && $group->getAdminAttribute())
        {
            $group->update($requestData);
            
            $this->syncHives($request, $group);
            
            $msg = $this->syncUsers($request, $group);
            if (gettype($msg) == 'array' && isset($msg['message']))
                return response()->json($msg, 201);

            return $this->index($request, 200);
        }
        return response()->json('no_group_found', 404);
    }


    public function destroy(Request $request, $id)
    {
        $group = $request->user()->groups()->findOrFail($id);
        
        if ($group && $group->getCreatorAttribute())
            $group->delete();
        
        return $this->index($request);
    }
    
    

    private function syncHives(Request $request, $group)
    {   
        // add edit_hive states to group_hive association
        $hive_ids = $request->input('hives_selected');
        $edit_ids = $request->input('hives_editable');
        $sync_ids = [];
        foreach ($hive_ids as $i => $hive_id) 
        {
            $sync_ids[$hive_id] = ['edit_hive'=>false];
            if (in_array($hive_id, $edit_ids))
                $sync_ids[$hive_id] = ['edit_hive'=>true];
        }
        return $group->hives()->sync($sync_ids);
    }

    private function syncUsers(Request $request, $group)
    {   
        // add edit_hive states to group_hive association
        $groupUsers = $group->users;
        $users      = $request->input('users');
        $invite_grp = [];
        $invite_new = [];
        $updated_msg= [];

        foreach ($users as $i => $user) 
        {
            $validUser = null;

            if (isset($user['id']))
                $validUser = User::where('id',$user['id'])->orWhere('email',$user['email'])->first();
            else
                $validUser = User::where('email',$user['email'])->first();

            if ($validUser)
            {
                $alreadyIn = ($groupUsers->where('email',$user['email'])->count() > 0);
                // check if we need to invite
                if ($alreadyIn)
                {
                    if (isset($user['delete']) && $user['delete'])
                    {
                        $validUser->groups()->detach($group->id);
                    }
                    else // update user
                    {
                        $res = DB::table('group_user')->where('user_id',$validUser->id)->where('group_id',$group->group_id)->update(['admin'=>$user['admin']]);
                        die(print_r($user));
                        if ($res)
                            $updated_msg[] = $user['name'];
                    }
                }
                else
                {
                    // invite existing Beep user for group
                    $token = str_random(30);
                    $validUser->groups()->attach($group->id, ['creator'=>false,'admin'=>$user['admin'],'invited'=>now(),'token'=>$token]);
                    $invite_grp[$validUser->email] = ['admin'=>$user['admin'], 'token'=>$token];
                }
            }
            else
            {
                // invite non-existing Beep user for group
                //die(print_r(['invite_new_user'=>$user['email']]));
                if (!isset($user['delete']) || $user['delete'])
                    $invite_grp[$user['email']] = $user['admin'];
            }
        }
        if (count($invite_grp) > 0)
        {
            $emails = [];
            foreach ($invite_grp as $email => $user) 
            {
                Mail::to($email)->send(new GroupInvitation($group, $user['admin'], $user['token']));
                $emails[] = $email;
            }
            return ['message'=>'Invited: '.implode($emails, ', ')];
        }
        else if (count($invite_new) > 0)
        {
            return ['message'=>'These users are not yet members of Beep: '.implode($invite_new, ', ')];
        }
        else if (count($updated_msg) > 0)
        {
            return ['message'=>'Updated: '.implode($updated_msg, ', ')];
        }
        return $group->users();
    }




}
