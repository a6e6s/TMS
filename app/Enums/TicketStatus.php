<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Triaged = 'triaged';
    case PushedToPms = 'pushed_to_pms';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return __('filament/enums.ticket_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::Triaged => 'warning',
            self::PushedToPms => 'gray',
            self::InProgress => 'primary',
            self::Resolved => 'success',
            self::Closed => 'gray',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::Triaged, self::PushedToPms, self::Closed],
            self::Triaged => [self::InProgress, self::PushedToPms, self::Open, self::Closed],
            self::PushedToPms => [self::Triaged, self::InProgress, self::Resolved, self::Closed],
            self::InProgress => [self::Resolved, self::Triaged, self::Closed],
            self::Resolved => [self::Open, self::Closed],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return $this === $next || in_array($next, $this->allowedTransitions(), true);
    }
}
