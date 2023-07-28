<?php

namespace App\Models\Tickets;

use ReflectionClass;

enum TicketType
{
    const STANDARD = 'Standard';
    const VIP = 'Vip';
    const PREMIUM = 'Premium';
    const BACKSTAGE = 'Backstage';
    const PRESS_ACCREDITATION = 'Accréditation_Presse';
    const SPECIAL_GUEST = 'Invité_Spécial';
    const STUDENT_DISCOUNT = 'Étudiant_Réduction';
    const FULL_ACCESS = 'Accès_Complet';
    const PARTIAL_ACCESS = 'Accès_Partiel';
    const FAMILY = 'Familial';

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
