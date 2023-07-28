<?php

namespace App\Models\Tickets;

use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';

    protected $fillable = [
        "event",
        "type",
        "free",
        "price",
        "number",
        "description",
        "qrcode",
        "qrid",
        "photos",
        "template",
        "tags",
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
