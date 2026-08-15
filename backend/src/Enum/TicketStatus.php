<?php

namespace App\Enum;

enum TicketStatus: string
{
    case NEW = 'NEW';
    case ASSIGNED = 'ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case WAITING = 'WAITING';
    case RESOLVED = 'RESOLVED';
    case CLOSED = 'CLOSED';
}