<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case WAITING_VENDOR = 'waiting_vendor';
    case IN_PROGRESS = 'in_progress';
    case WAITING = 'waiting';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::WAITING_VENDOR => 'Waiting Vendor',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING => 'Waiting',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }
}
