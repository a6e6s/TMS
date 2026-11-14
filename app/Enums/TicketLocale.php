<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TicketLocale: string implements HasLabel
{
    case English = 'en';
    case Arabic = 'ar';

    public function getLabel(): string
    {
        return __('filament/enums.ticket_locale.'.$this->value);
    }
}
