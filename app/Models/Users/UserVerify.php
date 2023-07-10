<?php

namespace App\Models\Users;

use Jenssegers\Mongodb\Eloquent\Model;

class UserVerify extends Model
{
    // protected $connection = 'mongodb';
    protected $primaryKey = '_id';

    protected $fillable = [
        "token",
        "user",
    ];

    public $timestamps = true;

    public function user(){
        $this->belongsTo(User::class);
    }

}
