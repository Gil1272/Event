<?php

namespace App\Models\Events;

use ReflectionClass;

enum EventType
{
    const CONFERENCE = 'Conférence';
    const SEMINAR = 'Séminaire';
    const WORKSHOP = 'Atelier';
    const MEETING = 'Réunion';
    const GALA = 'Gala';
    const FAIR = 'Foire';
    const CONCERT = 'Concert';
    const SPORT = 'Sport';
    const FESTIVAL = 'Festival';
    const WEBINAR = 'Webinaire';

    // Check if a given type exists in the enum
    public static function value_exists(string $type): bool{
        $types = self::toArray();
        return in_array($type, $types);
    }

    // Check if a given type exists in the enum
    public static function key_exists(string $key): bool{
        $types = self::toArray();
        return array_key_exists($key, $types);
    }

    // Get all the types as an array
    public static function toArray(): array{
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
