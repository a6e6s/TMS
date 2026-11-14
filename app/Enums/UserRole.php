<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case TeamLead = 'team_lead';
    case SupportAgent = 'support_agent';

    public function getLabel(): string
    {
        return __('filament/enums.user_role.'.$this->value);
    }
}
