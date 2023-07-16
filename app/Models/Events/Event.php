<?php

namespace App\Models\Events;

use App\Models\Users\User;
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
        "start_date",
        "end_date",
        "time_end",
        "user",
        "type",
        "status",
        "published",
        "private",
        "verify",
        "link",
        "banners",
        "photos",
    ];

    //Événement Transport Conférences et formations ,Sport
    public function user(){
        return $this->belongsTo(User::class);
    }
}
