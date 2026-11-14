<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TicketSource: string implements HasLabel
{
    case Manual = 'manual';
    case Api = 'api';

    public function getLabel(): string
    {
        return __('filament/enums.ticket_source.'.$this->value);
    }
}
