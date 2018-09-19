<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
//use Laravel\Passport\Http\Controllers\AccessTokenController;

class UserController extends ApiController
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['store', 'resend']);
        $this->middleware('auth:api')->except(['store', 'resend', 'verify', 'index', 'show']);
        $this->middleware('transform.input:'.UserTransformer::class)->only(['store', 'update']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::all();
        //$login = AccessTokenController->issueToken();

        return $this->showAll($users);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        $this->validate($request, $rules);
        $fields = $request->all();
        $fields['password'] = bcrypt($request->password);
        $fields['verified'] = User::USER_NOT_VERIFIED;
        $fields['verification_token'] = User::createVerificationToken();
        $fields['admin'] = User::USER_GENERAL;

        $user = User::create($fields);

        return $this->showOne($user, 201);
    }


    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:'.User::USER_ADMINISTRATOR.','.User::USER_GENERAL,
        ];

        $this->validate($request, $rules);

        if ($request->has('name')){
            $user->name = $request->name;
        }

        if (!empty($request->email)) {
            if ($request->has('email') && $user->email != $request->email){
                $user->verified = User::USER_NOT_VERIFIED;
                $user->verification_token = User::createVerificationToken();
                $user->email = $request->email;
            }
        }

        if ($request->has('password')){
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')){
            if (!$user->isVerified()){
                return $this->errorResponse('User not verified', 409);
            }
            $user->admin = $request->admin;
        }

        if (!$user->isDirty()){
            return $this->errorResponse('Please enter at least one field to update', 422);
        }

        $user->save();
        return $this->showOne($user);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify($token){
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::USER_VERIFIED;
        $user->verification_token = null;

        $user->save();
        return $this->showMessage('La cuenta ha sido verificada');
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(User $user){
        if ($user->isVerified()){
            return $this->errorResponse('Este usuario ya ha sido ferificado', 409);
        }

        retry(5, function() use ($user){
            Mail::to($user)->send(new UserCreated($user));
        }, 100);
        return $this->showMessage('El correo de verificación se ha reenviado');
    }
}
