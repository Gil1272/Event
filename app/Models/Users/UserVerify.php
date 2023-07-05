<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserVerify extends Model
{

    protected $fillable = [
        "id",
        "token",
        "user_id",
    ];

    public $timestamps = false;

    public function user(){
        $this->belongsTo(User::class);
    }

}
