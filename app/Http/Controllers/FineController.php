<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FineController extends Controller
{
    public function create(Request $request)
    {
        $user_id = Auth::id();
        $order = Order::where('user_id', $user_id)->where('paiement_id', $request->paiement_id)->first();
        Log::info($order);
        $order->status = "PAYED";
        $order->paiement_confirmation_id = $request->paiement_confirmation_id;
        $order->save();
        return (response()->json([
            'success' => true,
        ])
        );
    }
}
