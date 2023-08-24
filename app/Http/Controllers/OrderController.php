<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    // Affichage des Reçu de l'utilisateur
    public function read()
    {
        $user_id = Auth::id();
        $order = Order::where('user_id', $user_id)->get();

        return (response()->json([
            'ticket' => $order
        ]));
    }
}
