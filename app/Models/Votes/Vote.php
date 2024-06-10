<?php

namespace App\Models;
use App\Models\Events\Event;
use App\Models\Participants\Participant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Vote extends Eloquent
{
    use HasFactory;

    protected $primaryKey = '_id';

    protected $fillable = [
            'eventId',
            'name',
            'description'
    ];
    public function event(){
        return $this->belongsTo(Event::class);
    }

    public function participants(){
     
        return $this->hasMany(Participant::class);
    }
}
