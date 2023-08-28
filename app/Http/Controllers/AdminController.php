<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function displayUser()
    {
        $find = User::find(Auth::user()->id);
        //Verification que l'admin est bien admin

        if ($find->role === "admin") {
            $user = User::select('id', 'firstname', 'lastname', 'voucher', 'role')->get();
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } else {
            return response()->json([
                'status' => 'unauthorized'
            ]);
        }
    }
    public function roleUser(Request $request)
    {
        $admin = User::find(Auth::user()->id);
        $user = User::where('id', $request->id)->first();
        //Verification que l'admin est bien admin et que le user existe
        if ($admin->role === "admin" && $user != null) {
            // vérification du rôle donné 
            switch ($request->role) {
                case ('user'):
                    // Vérification si une carte de réduction existait déja
                    $voucher = Voucher::where('user_id', $user->id)->first();
                    if ($voucher != null) {
                        $user->voucher = $voucher->value;
                        
                    } else {
                        $user->voucher = null;
                    }
                    $user->role = "user";
                    $user->save();
                    break;

                case ('traincrew'):
                    $voucher = Voucher::where('user_id', $user->id)->first();
                    if ($voucher != null) {
                        $voucher->value = 100;
                        $user->voucher = 100;
                    } 
                    // Vérification si une carte de réduction existait déja
                    else {
                        $voucher = new Voucher();
                        $voucher->user_id = $user->id;
                        $voucher->value = 100;
                        $voucher->save();
                    }

                    $user->role = "traincrew";
                    $user->save();

                    break;
                case ('admin'):
                    $user->role = "admin";
                    $user->save();
                    break;
                default:
                    break;
            }

            return response()->json([]);
        } else {
            return response()->json([
                'status' => 'unauthorized'
            ]);
        }
    }
}
