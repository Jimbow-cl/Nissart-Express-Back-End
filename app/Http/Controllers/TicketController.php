<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function Available()
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

    public function Validation($id)
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
}
