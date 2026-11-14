<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProjectSyncStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';
    case OutOfSync = 'out_of_sync';

    public function getLabel(): string
    {
        return __('filament/enums.project_sync_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Synced => 'success',
            self::Failed => 'danger',
            self::OutOfSync => 'warning',
        };
    }
}
