<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasLabel
{
    case Staff = 'staff';
    case Service = 'service';

    public function getLabel(): string
    {
        return __('filament/enums.user_type.'.$this->value);
    }
}
