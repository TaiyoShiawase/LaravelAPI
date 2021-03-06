<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct()
    {
        $this->middleware('can:view,user')->only('show');
        $this->middleware('can:update,user')->only('update');
        $this->middleware('can:delete,user')->only('destroy');
        
        $this->middleware('scope:manage-account')->only(['show', 'update']);
        $this->middleware('client.credentials')->only(['store']);
        $this->middleware('auth:api')->except(['store', 'verify']);
        $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);
    }

    public function index()
    {
        $this->allowedAdminAction();

        $users = User::all();

        return $this->showAll($users);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

       Mail::to($user->email)->send(new UserCreated($user));
       
        return $this->showOne($user, 201);
    }

    public function show(User $user)
    {
        return $this->showOne($user);
    }

    public function update(Request $request, User $user)
    {   
        $rules = [
            'email' => 'email|unique:users,email' . $user,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER
        ];

        $this->validate($request, $rules);

       if($request->has('name')){
           $user->name = $request->name;
       }

       if ($request->has('email') && $user->email != $request->email) {
        $user->verified = User::UNVERIFIED_USER;
        $user->verification_token = User::generateVerificationCode();
        $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')) {
            $this->allowedAdminAction();

            if (!$user->isVerified()) {
                return $this->errorResponse('Only verified users can modify the admin field.', 409);
            }

            $user->admin = $request->admin;
        }

        if (!$user->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update.', 422);
        }

        $user->save();

        return $this->showOne($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;

        $user->save();

        return $this->showMessage('The account has been verified successfully');
    }
}
