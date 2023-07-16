<?php

namespace App\Models\Events;

use ReflectionClass;

enum EventStatus
{
    const PENDING = 'En attente';
    const CONFIRMED = 'Confirmé';
    const CANCELLED = 'Annulé';
    const POSTPONED = 'Reporté';
    const COMPLETED = 'Terminé';
    const ONGOING = 'En cours';
    const PENDING_CONFIRMATION = 'En attente de confirmation';
    const UPCOMING = 'À venir';


    // Check if a given status exists in the enum
    public static function value_exists(string $status): bool{
        $statuses = self::toArray();
        return in_array($status, $statuses);
    }

    // Check if a given status key  exists in the enum
    public static function key_exists(string $key): bool{
        $statuses = self::toArray();
        return array_key_exists($key, $statuses);
    }

    // Get all the statuses as an array
    public static function toArray(): array{
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
