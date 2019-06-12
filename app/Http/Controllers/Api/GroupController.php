<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Mail;
use App\Group;
use App\Hive;
use App\User;
use App\Mail\GroupInvitation;
use App\Mail\GroupAcceptation;

use DB;
use Validator;

class GroupController extends Controller
{

    public function index(Request $request, $code=200, $message=null, $error=null)
    {
        $groups = $request->user()->groups()->orderBy('name')->get();
        $invite = $request->user()->groupInvitations();
        return response()->json(['invitations'=>$invite, 'groups'=>$groups, 'message'=>$message, 'error'=>$error], $code);
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
            $valid_data     = $validator->validated();
            $group_user_id  = DB::table('group_user')->where('token',$valid_data['token'])->where('group_id',$valid_data['group_id'])->value('user_id');
            $user_name      = User::where('id',$group_user_id)->value('name');
            
            $res = DB::table('group_user')->where('token',$valid_data['token'])->where('group_id',$valid_data['group_id'])->update(['invited'=>null,'accepted'=>now(),'declined'=>null,'token'=>null]);
            if ($res)
            {
                $this->sendAcceptMailToGroupAdmins($valid_data['group_id'], $user_name, $group_user_id);
                return response()->json(['message'=>'group_activated']);
            }
        }
        return response()->json('token_error',500);
    }

    private function sendAcceptMailToGroupAdmins($group_id, $user_name, $group_user_id)
    {
        $group_name  = Group::where('id', $group_id)->value('name');
        $group_admin = DB::table('group_user')->where('user_id', '!=', $group_user_id)->where('group_id',$group_id)->where('admin',1)->pluck('user_id')->toArray();
        $admin_mails = User::whereIn('id',$group_admin)->pluck('name','email')->toArray();
        
        foreach ($admin_mails as $email => $name) 
        {
            Mail::to($email)->send(new GroupAcceptation($name, $group_name, $user_name));
        }
    }


    public function store(Request $request)
    {
        $userExist = $this->checkIfUsersExist($request);
        if (gettype($userExist) == 'array')
        {
            if (isset($userExist['error']))
                return response()->json($userExist, 422);
        }

        $requestData = $request->only(['name','description','hex_color']);
        $group       = Group::create($requestData);
        $request->user()->groups()->attach($group, ['creator'=>true,'admin'=>true,'accepted'=>now()]);
        
        $this->syncHives($request, $group);
        
        $msg = $this->syncUsers($request, $group);
        if (gettype($msg) == 'array')
        {
            if (isset($msg['message']))
                return $this->index($request, 201, $msg['message']);
            else if (isset($msg['error']))
                return $this->index($request, 422, null, $msg['error']);
        }

        return $this->index($request, 201, __('group.Created').$requestData['name']);
    }


    public function show(Request $request, $id)
    {
        $group = $request->user()->groups()->find($id);
        if ($group)
        {
            return response()->json($group); 
        }
        return response()->json(null, 404);
    }


    public function update(Request $request, $id)
    {
        $userExist = $this->checkIfUsersExist($request);
        if (gettype($userExist) == 'array')
        {
            if (isset($userExist['error']))
                return response()->json($userExist, 422);
        }

        $requestData = $request->only(['id','name','description','hex_color']);
        $group = $request->user()->groups()->find($id);

        if ($group)
        {
            $this->syncHives($request, $group);
            
            if ($group->getAdminAttribute())
            {
                $group->update($requestData);
                $msg = $this->syncUsers($request, $group);
                if (gettype($msg) == 'array')
                {
                    if (isset($msg['message']))
                        return $this->index($request, 201, $msg['message']);
                    else if (isset($msg['error']))
                        return $this->index($request, 422, null, $msg['error']);
                }
            }

            return $this->index($request, 200, __('group.Updated').$requestData['name']);
        }
        return response()->json('no_group_found', 404);
    }

    public function detach(Request $request, $id)
    {
        $res = $this->detachFromGroup($request->user(), $request->user()->groups()->findOrFail($id));
        if ($res)
            return response()->json(['message'=>'group_detached'], 200);

        return response()->json(['error'=>'no_group_detached'], 404);
    }

    private function detachFromGroup($user, $group)
    {
        $user_hive_ids       = $user->hives()->pluck('hives.id')->toArray();
        $group_hive_ids      = $group->hives()->pluck('hives.id')->toArray();
        $user_group_hive_ids = array_intersect($group_hive_ids, $user_hive_ids);
        
        //die(print_r(['user_hives'=>$user_hive_ids,'hives'=>$group_hive_ids, 'match'=>$user_group_hive_ids]));
        
        $group->hives()->detach($user_group_hive_ids);
        $user->groups()->detach($group->id);
        
        // ToDo make next admin in group owner
        return true;
    }

    public function destroy(Request $request, $id)
    {
        $group = $request->user()->groups()->findOrFail($id);
        $name  = $group->name;
        $del   = false;

        if ($group && $group->getCreatorAttribute())
        {
            $del = $group->delete();
            return $this->index($request, 200, __('group.Deleted').$name);
        }
        
        return $this->index($request, 404, null, 'no_group_creator');
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


    private function checkIfUsersExist(Request $request)
    {
        $users      = $request->input('users');
        $error_msg  = [];
        foreach ($users as $i => $user) 
        {
            $validUser = null;
            $user_id   = '';
            
            if (isset($user['email']))
            {
                $validUser = User::where('email',$user['email'])->first();
                if (!isset($validUser))
                    $error_msg[] = $user['email'];
            }
        }

        if (count($error_msg) > 0)
        {
            return ['error'=>__('group.email_na').implode($error_msg, ', ')];
        }
        return true;
    }

    private function syncUsers(Request $request, $group)
    {   
        // add edit_hive states to group_hive association
        $groupUsers = $group->users;
        $users      = $request->input('users');
        $invite_grp = [];
        $invite_new = [];
        $updated_msg= [];
        $error_msg  = [];

        foreach ($users as $i => $user) 
        {
            $validUser = null;
            $user_id   = '';
            
            if (isset($user['email']))
            {
                $validUser = User::where('email',$user['email'])->first();
                $user_id   = isset($validUser) ? ','.$validUser->id : '';
            }

            $validator = Validator::make($user, [
                'id'     => 'nullable|integer|exists:users,id',
                'name'   => 'nullable|string',
                'email'  => 'required|email|unique:users,email'.$user_id,
                'admin'  => 'required|boolean',
                'delete' => 'nullable|boolean',
            ]);
            if ($validator->fails())
            {
                $error_msg[] = $validator->errors();
                continue;
            }

            $validData = $validator->validated();
            $email     = $validData['email'];
            $name      = isset($validData['name']) ? $validData['name'] : $validUser['name'];
            $admin     = (isset($validData['admin']) && $validData['admin']);
            $delete    = (isset($validData['delete']) && $validData['delete']);

            if (isset($validData['id']))
                $validUser = User::where('id',$validData['id'])->orWhere('email',$email)->first();
            else
                $validUser = User::where('email',$email)->first();

            if ($validUser)
            {
                $alreadyIn = ($groupUsers->where('email',$email)->count() > 0);
                // check if we need to invite
                if ($alreadyIn)
                {
                    if ($delete) // detach user and it's hives from the group
                    {
                        $this->detachFromGroup($validUser, $group);
                    }
                    else // update user
                    {
                        $res = DB::table('group_user')->where('user_id',$validUser->id)->where('group_id',$group->id)->update(['admin'=>$admin]);
                        if ($res && $validUser->id != $request->user()->id)
                            $updated_msg[] = $name;

                        // die(print_r(['admin'=>$admin,'del'=>$delete,'invite_new'=>$invite_new, 'invite_grp'=>$invite_grp, 'updated_msg'=>$updated_msg, 'u'=>$validUser->id, 'g'=>$group->group_id]));
                    }
                }
                else
                {
                    // invite existing Beep user for group
                    $token = str_random(30);
                    $validUser->groups()->attach($group->id, ['creator'=>false,'admin'=>$admin,'invited'=>now(),'token'=>$token]);
                    $invite_grp[$validUser->email] = ['name'=>$name, 'admin'=>$admin, 'token'=>$token];
                }
            }
            else
            {
                // invite non-existing Beep user for group
                //die(print_r(['invite_new_user'=>$email]));
                if ($delete)
                    $invite_grp[$email] = $admin;
                else
                    $invite_new[$user['email']] = $user['name'];
            }
        }
        if (count($invite_grp) > 0)
        {
            $emails = [];
            foreach ($invite_grp as $email => $user) 
            {
                $invited_by = Auth::user()->name.(Auth::user()->name != Auth::user()->email ? ' ('.Auth::user()->email.')' : '');
                Mail::to($email)->send(new GroupInvitation($group, $name, $admin, $user['token'], $invited_by));
                $emails[] = $email;
            }
            return ['message'=>__('group.Invited').implode($emails, ', ')];
        }
        else if (count($invite_new) > 0)
        {
            return ['error'=>__('group.email_na').implode($invite_new, ', ')];
        }
        else if (count($updated_msg) > 0)
        {
            return ['message'=>__('group.Updated').implode($updated_msg, ', ')];
        }
        else if (count($error_msg) > 0)
        {
            return ['error'=>implode($error_msg, ', ')];
        }
        return $group->users();
    }




}
