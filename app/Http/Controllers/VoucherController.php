<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VoucherController extends Controller
{
    // Création d'un voucher

    public function Create(Request $request, $value)
    {
        // Création des ressources offertes
        $user_id = Auth::id();
        $voucher = Voucher::where('user_id', $user_id)->first();
        if ($voucher === null) {
            $voucher = new Voucher();
            $voucher->user_id = $user_id;
            $voucher->value = $value;
            $voucher->save();
            $verify = Voucher::select('id')->where('user_id', $user_id)->first();
            $user = User::where('id', $user_id)->first();
            $user->voucher = $verify->id;
            $user->save();
            return response()->json(['success' => true], 200);

        } else {
            return response()->json(['Already an actif voucher'], 200);
        }
    }

    //lecture des vouchers disponibles
    //lecture des vouchers disponibles
    public function Read()
    {
        $user_id = Auth::id();
        $voucher = Voucher::where('user_id', $user_id)->first();

        if ($voucher === null) {
            return response()->json(['success' => false]);
        } else {
            $today = now();
            // Ajoute un an à la date de création du voucher
            $expirationDate = $voucher->created_at->addYear();

            if ($expirationDate >= $today) {
                return response()->json([$voucher], 200);
            } else {
                $voucher->delete();
                return response()->json(['success' => false]);
            }
        }
    }
}
