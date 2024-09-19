<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtcQuote extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['buy_price', 'sell_price', 'recorded_at'];
}
