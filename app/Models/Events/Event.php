<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';

    protected $fillable = [
        "name",
        "description",
        "country",
        "place",
        "date_since",
        "date_end",
        "time_end",
        "user",
        "event_type",
        "status",
        "published",
        "private",
        "verify",
        "link",
        "banners",
        "photos",
    ];

    //Événement Transport Conférences et formations ,Sport

}
