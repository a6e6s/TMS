<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WebhookProcessingStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';
    case Duplicate = 'duplicate';

    public function getLabel(): string
    {
        return __('filament/enums.webhook_processing_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Processed => 'success',
            self::Failed => 'danger',
            self::Duplicate => 'info',
        };
    }
}
