<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use App\Device;
use DB;
use Hash;
use Image;
use Auth;
use Storage;
use InterventionImage;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page       = $request->input('page');
        $show_stats = $request->filled('stats');

        if (Auth::user()->hasRole('superadmin'))
        {
            $keyword     = $request->get('search');
            $perPage     = 50;
            $users       = User::where('id', '!=', null);

            if (!empty($keyword)) 
            {
                $users = $users->where('name', 'LIKE', "%$keyword%")
                                ->orWhere('email', 'LIKE', "%$keyword%")
                                ->orWhere('locale', 'LIKE', "%$keyword%")
                                ->orWhere('id', 'LIKE', "%$keyword%");

            }
            
            $data = $users->orderBy('name')->with('roles')->paginate($perPage);
        }
        else
        {
            $data = [Auth::user()];
        }
        return view('users.index',compact('data', 'show_stats', 'page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = $this->getMyPermittedRoles(Auth::user());
        $sensors = Device::all()->pluck('name','id');
        return view('users.create',compact('roles','sensors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->checkRoleAuthorization($request, "user-create") == false)
        {
            return redirect()->route('users.index')->with('error', 'You are not allowed to create this type of user');
        }

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            //'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['api_token'] = Str::random(60);

        $storage  = env('IMAGE_STORAGE', 's3');
        
        // Handle the user upload of avatar
        if($request->hasFile('avatar')){
            $avatar   = $request->file('avatar');
            $filename = time() . '.' . $avatar->getClientOriginalExtension();
            $path     = 'avatars/'.$filename;
            $thumb    = InterventionImage::make($avatar)->resize(300, 300);
            Storage::disk($storage)->put($path, $thumb->stream());
            $input['avatar'] = Storage::disk($storage)->url($path);
        }
        else
        {
            $input['avatar'] = Storage::disk($storage)->url('avatars/default.jpg');
        }

        $user = User::create($input);
        
        // Handle role assignment, only store permitted role
        if ($request->filled('roles'))
        {
            $roleIds = $this->getMyPermittedRoles(Auth::user(), true);
            foreach ($request->input('roles') as $key => $value)
            {
                if (in_array($value, $roleIds))
                {
                    $user->attachRole($value);
                }
            }
        }

        // Edit sensors
        if($request->filled('sensors')){
            foreach ($request->input('sensors') as $key => $value) {
                DB::table('sensor_user')->insert(
                    ['user_id' => $user->id, 'sensor_id' => $value]
                );
            }
        }

        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        $sensors = []; //DB::table('sensors')->join('sensor_user', 'sensors.id', '=', 'sensor_user.sensor_id')->where('user_id',$id)->orderBy('name','asc')->pluck('name','id');
        return view('users.show',compact('user','sensors'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user       = User::find($id);
        $roles      = $this->getMyPermittedRoles($user);
        $userRole   = $user->roles->pluck('id','id')->toArray();
        $sensors    = DB::table('sensors')->orderBy('name','asc')->pluck('name','id');
        $userSensor = DB::table('sensor_user')->where('user_id',$id)->pluck('sensor_id','sensor_id')->toArray();

        return view('users.edit',compact('user','roles','userRole','sensors','userSensor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($this->checkRoleAuthorization($request, "user-edit", $id) == false)
            return redirect()->route('users.index')->with('error', 'You are not allowed to edit this user');

        // Do normal validation
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            // 'roles' => 'required',
            'avatar' => 'mimes:jpeg,gif,png'
        ]);


        $input = $request->all();
        if(!empty($input['password'])){ 
            $input['password'] = Hash::make($input['password']);
        }else{
            unset($input['password']);    
        }

        $user = User::find($id);
        
        // Handle the user upload of avatar
        if($request->hasFile('avatar')){
            $avatar   = $request->file('avatar');
            $filename = time().'.'.$avatar->getClientOriginalExtension();
            $path     = 'avatars/'.$filename;
            $storage  = env('IMAGE_STORAGE', 's3');
            $thumb    = InterventionImage::make($avatar)->resize(300, 300);
            Storage::disk($storage)->put($path, $thumb->stream());
            $input['avatar'] = Storage::disk($storage)->url($path);
        }

        $user->update($input);

        // Edit role
        if ($request->filled('roles'))
        {
            if ($request->filled('roles'))
            {
                DB::table('role_user')->where('user_id',$id)->delete();
                foreach ($request->input('roles') as $key => $value) {
                    $user->attachRole($value);
                }
            }
        }
        else
        {
            $user->roles()->detach();
        }

        // Edit sensors
        if($request->filled('sensors'))
        {
            DB::table('sensor_user')->where('user_id',$id)->delete();
            foreach ($request->input('sensors') as $key => $value) {
                DB::table('sensor_user')->insert(
                    ['user_id' => $id, 'sensor_id' => $value]
                );
            }
        }
        else
        {
            DB::table('sensor_user')->where('user_id', $user->id)->delete();
        }

        return redirect()->route('users.index',['search='.$id])
                        ->with("success", "User updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($this->checkRoleAuthorization(null, "user-delete", $id) == false)
            return redirect()->route('users.index')->with('error','User not deleted, you have no permission');


        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');

    }

    private function checkRoleAuthorization($request=null, $permission=null, $id=null)
    {
        if ($id && Auth::user()->id == $id) // edit self is allowed
            return true;
     
        if ($permission && Auth::user()->can($permission) == false) // check permissions
            return false;

        // Check for unauthorized role editing
        if ($request != null && $request->filled('roles') && count($request->input('roles')) > 0)
        {
            if ($request->input('roles')[0] == '')
                return true; // no role

            $superId = Role::where('name','=','superadmin')->pluck('id','id')->toArray();
            $reqIsSup= count(array_diff($request->input('roles'), $superId)) == 0 ? true : false; // check if super admin id role is requested
            $roleIds = $this->getMyPermittedRoles(Auth::user(), true);
            $reqMatch= count(array_diff($request->input('roles'), $roleIds)) == 0 ? true : false; // check if all roles match

            if ($reqMatch == false || ($reqIsSup && Auth::user()->hasRole('superadmin') == false)){
                return false;
            }
        }
        return true;
    }

    // Helpers
    private function getMyPermittedRoles($user, $returnIdArray=false)
    {
        //die($user->roles->pluck('id'));
        if (Auth::user()->hasRole('superadmin'))
        {
            $roles = Role::all();
        }
        else if (Auth::user()->hasRole('admin'))
        {
            $roles = Role::where('name','!=','superadmin');
        }
        else 
        {
            $roles = $user->roles;
        }
        //die($roles);
        if ($returnIdArray)
        {
            return $roles->pluck('id','id')->toArray();
        } 
        else
        {
            return $roles->pluck('display_name','id');
        }
    }


}