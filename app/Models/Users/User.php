<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $primaryKey = '_id';

    // protected $connection = 'mongodb';

    protected $hidden = [
        "password"
    ];

    protected $casts = ['birthday' => 'datetime'];

    protected $fillable = [
        "user_type",
        "name",
        "firstname",
        "password",
        "email",
        "phone_number",
        'active',
        'locked',
        "civility",
        "entreprise",
        "country"
    ];

    public $timestamps = true;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // /**
    //  * The attributes that should be cast.
    //  *
    //  * @var array<string, string>
    //  */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    // public function event(){
    //     return $this->hasMany(Event::class);
    // }

    // public function notification(){
    //     return $this->hasMany(Notification::class);
    // }

    public function userVerify(){
        return $this->hasOne(UserVerify::class);
    }
}
