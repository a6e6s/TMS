<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketCategory: string implements HasColor, HasLabel
{
    case UserError = 'user_error';
    case HowTo = 'how_to';
    case Bug = 'bug';
    case FeatureRequest = 'feature_request';
    case CodeChange = 'code_change';
    case ConfigOrInfra = 'config_or_infra';
    case Unclear = 'unclear';

    public function getLabel(): string
    {
        return __('filament/enums.ticket_category.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UserError, self::HowTo => 'gray',
            self::Bug => 'danger',
            self::FeatureRequest, self::CodeChange => 'info',
            self::ConfigOrInfra => 'warning',
            self::Unclear => 'gray',
        };
    }
}
