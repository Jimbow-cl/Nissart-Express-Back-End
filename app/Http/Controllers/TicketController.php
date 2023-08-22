<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function Available()
    {
        $user_id = Auth::id();
        $tickets = Ticket::where('user_id', $user_id)->where('status', 'AVAILABLE')->get();
        $today = now();
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
}
