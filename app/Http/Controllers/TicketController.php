<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

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

    public function control($id)
    {
        $traincrew_id = Auth::id();
        $traincrew = User::where('id', $traincrew_id)->first();
        //Vérification que le control provient bien d'un controleur
        if ($traincrew->role === "traincrew") {
            $ticket = Ticket::where('id', $id)->first();
            $ticket->status = "CONTROLED";
            $ticket->save();
        

        return (response()->json([
            'success' => true,
        ])
        );}
        else{
            return (response()->json([
                'success' => false,
            ]));}
        
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
        Ticket::create([
            'order_id' => $order->id,
            'start' => $metadata['start'],
            'end' => $metadata['end'],
            'passenger' => $metadata['passenger'],
            'class' => $metadata['class'],
            'schedule' => $metadata['schedule'],
            'user_id' => $metadata['user_id'],
            'status' => "AVAILABLE"
        ]);
        return (response()->json([
            'success' => true,
        ])
        );
    }
    // Récupération pour le contrôleur, de la liste des tickets valides
    public function readValid()
    {
        //choix des deux status
        $validStatus = ['VALIDATED', 'CONTROLED'];

        $tickets = Ticket::whereIn('status', $validStatus)->get();

        if ($tickets->isEmpty()) {

            return (response()->json([
                'message' => 'No Ticket Validated'
            ]));
        } else {
            foreach ($tickets as $ticket) {
                $user = User::where('id', $ticket->user_id)->first();
                $ticket->lastname = $user->lastname;
                $ticket->firstname = $user->firstname;
                $ticket->voucher = $user->voucher;
                $ticket->role = $user->role;
                //Création d'un tableau
                $ticketsArray[] = $ticket;
            }
            return (response()->json([
                'tickets' => $ticketsArray,
            ])
            );
        }
    }
}
