<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pendiente';
    case InProgress = 'en_progreso';
    case InReview = 'en_revision';
    case Completed = 'completada';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
