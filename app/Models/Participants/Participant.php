<?php

namespace App\Models\Participants;
use App\Models\Votes\Vote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';


    protected $fillable=[
        'name',
        'image',
        'detail',
        'vote_id'
    ];

    public function vote(){
        return $this->belongsTo(Vote::class);
    }
}
