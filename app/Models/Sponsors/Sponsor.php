<?php

namespace App\Models\Sponsors;

use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;


class Sponsor extends Model
{
    use HasFactory;

    protected $primaryKey = '_id';

    protected $fillable = [
        "event",
        "name",
        "type",
        "logo",
        "activity_sector",
        "description",
    ];

    public function event(){
        return $this->belongsTo(Event::class);
    }
}
