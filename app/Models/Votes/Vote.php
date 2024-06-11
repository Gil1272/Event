<?php

namespace App\Models\Votes;
use App\Models\Events\Event;
use App\Models\Participants\Participant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';

    protected $fillable = [
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
