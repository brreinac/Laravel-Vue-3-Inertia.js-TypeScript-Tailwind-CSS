<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'activo';
    case Archived = 'archivado';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
