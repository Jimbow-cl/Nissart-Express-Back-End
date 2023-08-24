<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VoucherController extends Controller
{
    // Création d'un voucher

    public function create(Request $request)
    {
        // Création des ressources offertes
        $user_id = Auth::id();
        $order = Order::where('user_id', $user_id)->where('paiement_id', $request->paiement_id)->first();
        $order->status = "PAYED";
        $order->paiement_confirmation_id = $request->paiement_confirmation_id;
        $order->save();
        //Décoder en JSON la colonne métadata

        $metadata = json_decode($order->metadata, true);

        $voucher = Voucher::where('user_id', $user_id)->first();
        if ($voucher === null) {
            $voucher = new Voucher();
            $voucher->user_id = $metadata['user_id'];
            $voucher->value = $metadata['value'];
            $voucher->save();

            //Mise en place de l'information sur le User

            $verify = Voucher::select('value')->where('user_id', $user_id)->first();
            $user = User::where('id', $user_id)->first();
            $user->voucher = $verify->value;
            $user->save();
            return response()->json([
                'success' => true,
                'voucher' =>  $user->voucher
            ], 200);
        } else {
            return response()->json(['Already an actif voucher'], 200);
        }
    }

    //lecture des vouchers disponibles
    //lecture des vouchers disponibles
    public function read()
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
                $user = User::where('id', $user_id)->first();
                $user->voucher = null;
                $user->save();

                $voucher->delete();
                return response()->json(['success' => false]);
            }
        }
    }
}
