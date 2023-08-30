<?php

namespace App\Models\Sponsors;

use ReflectionClass;

enum SponsorActivitySector
{
    const ENERGY = 'Énergie';
    const SANTE = 'Santé';
    const AGRICULTURE = 'Agriculture';
    const ELEVAGE = 'Élevage';
    const FINANCE = 'Finance';
    const TELECOMS = 'Télécommunication';

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


    public static function get_value(string $key): string{
        $types = self::toArray();
        return $types[$key];
    }

    // Get all the types as an array
    public static function toArray(): array{
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
