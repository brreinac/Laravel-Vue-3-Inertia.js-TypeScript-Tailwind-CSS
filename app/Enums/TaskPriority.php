<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'baja';
    case Medium = 'media';
    case High = 'alta';
    case Critical = 'crítica';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
