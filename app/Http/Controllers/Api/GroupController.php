<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mail;
use App\Group;
use App\Hive;
use App\User;
use App\Mail\GroupInvitation;

class GroupController extends Controller
{

    public function index(Request $request, $code=200)
    {
        $groups = $request->user()->groups()->orderBy('name')->get();
        return response()->json($groups, $code);
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

        if ($group && $group->getAdminAttribute() === 1)
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
        
        if ($group && $group->getCreatorAttribute() === 1)
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
                    if (isset($user['delete']) && $user['delete'] == 1)
                    {
                        $validUser->groups()->detach($group->id);
                    }
                }
                else
                {
                    // invite existing Beep user for group
                    //die(print_r(['invite'=>$validUser->email, 'currentusers'=>$groupUsers->toArray()]));
                    $validUser->groups()->attach($group->id, ['creator'=>false,'admin'=>($user['admin']==1),'invited'=>now()]);
                    $invite_grp[$validUser->email] = ($user['admin']==1);
                }
            }
            else
            {
                // invite non-existing Beep user for group
                //die(print_r(['invite_new_user'=>$user['email']]));
                if (!isset($user['delete']) || $user['delete'] == 0)
                    $invite_grp[$user['email']] = ($user['admin']==1);
            }
        }
        if (count($invite_grp) > 0)
        {
            $emails = [];
            foreach ($invite_grp as $email => $admin) 
            {
                Mail::to($email)->send(new GroupInvitation($group, $admin));
                $emails[] = $email;
            }
            return ['message'=>'Invited: '.implode($emails, ', ')];
        }
        else if (count($invite_new) > 0)
        {
            return ['message'=>'These users are not yet members of Beep: '.implode($invite_new, ', ')];
        }
        return $group->users();
    }




}
