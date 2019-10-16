<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        if (\Gate::allows('isAdmin') || \Gate::allows('isAuthor')) {
            return User::latest()->paginate(5);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6',
        ]);
        return User::create([
            'name' => $request['name'],
            'password' => Hash::make($request['password']),
            'bio' => $request['bio'],
            'email' => $request['email'],
            'type' => $request['type'],
            'photo' => $request['photo']
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
    public function profile()
    {
        return auth('api')->user();
    }   
    public function updateProfile(Request $request)
    {
        
        $user = auth('api')->user();

        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6',
        ]);

        $currentPhoto = $user->photo;
        

        if ($request->photo != $currentPhoto){
            $name = time().'.'. explode('/',explode(':',substr($request->photo,0,strpos($request->photo,';')))[1])[1];
            \Image::make($request->photo)->save(public_path('img/profile/'.$name));
            $request->merge(['photo'=>$name]);

            $userPhoto = public_path('img/profile/').$currentPhoto;
            if (file_exists($userPhoto)){
                @unlink($userPhoto);
            }
        }

        if (!empty($request->password)){
            $request->merge(['password'=>Hash::make($request['password'])]);
        }
        
        $user->update($request->all());
        return ['code' => '000','message' => 'Successfully Update Profile'];

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
        //
        $user = User::findOrFail($id);

        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6',
        ]);

        $user -> update($request->all());
        return ['message' => 'Edit user success'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this>authorize('isAdmin');
        $user = User::findOrFail($id);

        //delete
        $user -> delete();
        return ['message'=>'User succesfully deleted'];
    }

    public function search(){
        if ($search = \Request::get('q')){
            $users = User::where(function($query) use ($search){
                $query->where('name','LIKE',"%$search%")
                ->orWhere('type','LIKE',"%$search%")
                ->orWhere('email','LIKE',"%$search%");
            })->paginate(20);
        }
        return $users;
    }
}
