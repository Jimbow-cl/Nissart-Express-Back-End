<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $table = 'train_tickets';
    protected $fillable = [
        'order_id',
        'start',
        'end',
        'passenger',
        'class',
        'schedule',
        'status',
        'user_id'
    ];
}
