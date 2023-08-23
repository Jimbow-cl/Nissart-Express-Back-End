<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function available()
    {
        $user_id = Auth::id();
        $tickets = Ticket::where('user_id', $user_id)->get();
        //mise en forme de la date sans les hh:mm:ss
        $today = now()->format('Y-m-d');
        foreach ($tickets as $ticket) {
            if ($ticket->schedule < $today) {
                $ticket->status = "EXPIRED";
                $ticket->save();
            };
        }
        return (response()->json([
            'success' => true,
            'ticket' => $tickets
        ])
        );
    }
    // Validation manuel du client

    public function validation($id)
    {
        $user_id = Auth::id();
        $today = now();
        $ticket = Ticket::where('user_id', $user_id)->where('id', $id)->first();
        $ticket->status = "VALIDATED";
        $ticket->schedule = $today;
        $ticket->save();

        return (response()->json([
            'success' => true,
        ])
        );
    }

    public function create(Request $request)
    {
        $user_id = Auth::id();
        $order = Order::where('user_id', $user_id)->where('paiement_id', $request->paiement_id)->first();
        $order->status = "PAYED";
        $order->paiement_confirmation_id = $request->paiement_confirmation_id;
        $order->save();
        //Décoder en JSON la colonne métadata

        $metadata = json_decode($order->metadata, true);
        Log::error('Metadata ' . $order->metadata);
        Ticket::create([
            'start' => $metadata['start'],
            'end' => $metadata['end'],
            'passenger' => $metadata['passenger'],
            'class' => $order->metadata['class'],
            'schedule' => $metadata['schedule'],
            'user_id' => $metadata['user_id'],
            'status' => "AVAILABLE"
        ]);
        return (response()->json([
            'success' => true,
        ])
        );
    }
}
