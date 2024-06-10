<?php

namespace App\Models;
use App\Models\Votes\Vote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Participant extends Eloquent
{
    use HasFactory;

    protected $primaryKey = '_id';


    protected $fillable=[
        'voteId',
        'name',
        'image',
        'detail'
    ];

    public function vote(){
        return $this->belongsTo(Vote::class);
    }
}
