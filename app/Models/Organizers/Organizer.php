<?php

namespace App\Models\Organizers;

use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;


class Organizer extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';

    protected $fillable = [
        "event",
        "name",
        "logo",
        "activity_area",
        "description",
    ];

    public function event(){
        return $this->belongsTo(Event::class);
    }
}
