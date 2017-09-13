<?php

namespace App\Http\Controllers\API;

use App\Like;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

use Illuminate\Support\Facades\Input;
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
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $data['email'],
            'password' => $data['password'],
            'scope' => null,
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
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
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
        $users = User::select('name', 'last_name', 'created_at')->get();

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
        if ($user = Auth::guard('api')->user()) {
            $data = $request->only('name', 'last_name', 'email');

            $json['success'] = $user->update([
                'name' => $data['name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
            ]);

            $json['message'] = 'You are updated';

            return response()->json($json, 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }


    /**
     * Update profile image for current user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPhoto(Request $request)
    {
        $this->validate($request, [
            'profile_image' => 'required|mimes:jpeg,png |max:4096',
        ]);

        if ($user = Auth::guard('api')->user()) {

            $file = Input::file('profile_image');
            $destinationPath = public_path(). '/images/';
            $filename = $file->getClientOriginalName();
            $file->move($destinationPath, $filename);

            $user->profile_image = $filename;
            $user->save();
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
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
        if ($user = Auth::guard('api')->user()) {

            $json = Like::create([
                'type_id' => $request['model_id'],
                'type' => $request['model'],
                'user_id' => $user->id,
                'status' => true
            ]);
            return response()->json($json, 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }


    /**
     * Return info about current current authorized user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentUserInfo()
    {
        if ($user = Auth::guard('api')->user()) {

            $json = $user;

            return response()->json($json, 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }


    /**
     * Return user information by id with likes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecificUserInfo(Request $request)
    {
        if ($user = User::find($request['user_id'])) {
            $json = $user->likes();
            return response()->json($json, 200);
        } else {
            return response()->json(['error' => 'User not found'], 401);
        }
    }
}