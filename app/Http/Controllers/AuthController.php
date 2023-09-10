<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'

        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {

        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string',
            'date_of_birth' => 'required|date|before:-18 years',
        ]);

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'role' => 'user',
            'voucher' => null,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
        ]);
        Auth::login($user);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);;
    }

    public function logout()
    {
        Auth::logout();

        if (!Auth::check()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            // vu que c'est unique, j'ajoute l'id pour exclure le user de la recherche d'unicité
            //dans la table Users.
            'email' => 'required|string|email|unique:users,email,' . Auth::user()->id,
            'password' => 'required|string',
        ]);

        $update = User::find(Auth::user()->id);
        $update->firstname = $request->input('firstname');
        $update->lastname = $request->input('lastname');
        $update->email = $request->input('email');
        $update->password = Hash::make($request->input('password'));
        $update->save();
        Auth::login($update);

        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function destroy()
    {
        $id = Auth::user()->id;
        $user = User::find($id);
        $user->active = !$user->active;
        $user->updated_at = Carbon::now();
        $user->save();
        Auth::logout();

        return response()->json([
            'status' => 'success'
        ]);
    }

   
}
