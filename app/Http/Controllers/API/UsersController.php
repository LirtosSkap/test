<?php

namespace App\Http\Controllers\API;

use App\Like;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Laravel\Passport\Client;
use Validator;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{


    /**
     * Registration new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function create(Request $request)
    {
        $v = validator($request->only('email', 'name', 'last_name', 'password'), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }
        $data = request()->only('email', 'name', 'last_name', 'password');

        $user = User::create([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $client = Client::where('password_client', 1)->first();

        $request->request->add([
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $data['email'],
            'password'      => $data['password'],
            'scope'         => null,
        ]);

        // Fire off the internal request.
        $proxy = Request::create(
            'oauth/token',
            'POST'
        );

        return \Route::dispatch($proxy);
    }


    /**
     * Login
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], 200);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    /**
     * Logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        Auth::guard('api')->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        $json = [
            'success' => true,
            'message' => 'You are logged out.',
        ];

        return response()->json($json, 200);
    }


    /**
     * Return all users with some of their fields: name, last_name and registration date
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::select('name','last_name', 'created_at')->get();

        return response()->json($users);
    }


    /**
     * Updating info in current authorized user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if($user = Auth::guard('api')->user()){
            $json['success'] = $user->update($request->only('name', 'last_name', 'email', 'profile_image'));
            $json['message'] = 'You are updated';

            return response()->json($json, 200);
        }
        else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    /**
     * Like entity by current authorized user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function like(Request $request)
    {
        if($user = Auth::guard('api')->user()){

            $json = Like::create([
                'type_id' => $request['model_id'],
                'type' => $request['model_name'],
                'user_id' => $user->id,
                'status' => true
            ]);
            return response()->json($json, 200);
        }
        else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    public function getCurrentUserInfo()
    {
        if($user = Auth::guard('api')->user()){

            $json = $user;

            return response()->json($json, 200);
        }
        else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }



    public function getSpecificUserInfo()
    {

        $json = User::find(2);
        $json = \App\Like::find(1)->likable();
//        $json = $json->likes();
        return response()->json($json, 200);

//        if($user = Auth::guard('api')->user()){
//
//            $json = $user;
//
//            return response()->json($json, 200);
//        }
//        else {
//            return response()->json(['error'=>'Unauthorised'], 401);
//        }
    }
}