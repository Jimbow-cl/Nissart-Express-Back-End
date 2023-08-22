<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function Available()
    {
        $user_id = Auth::id();
        $tickets = Ticket::where('user_id', $user_id)->get();
        // Carbon permet de gèrer les date plus précisément
        $yesterday = Carbon::yesterday(); 

        foreach ($tickets as $ticket) {
            $ticketDate = Carbon::parse($ticket->schedule);
    
            if ($ticketDate->lessThan($yesterday)) {
                $ticket->status = "EXPIRED";
                $ticket->save();
            }
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
