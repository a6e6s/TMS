<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommentAuthorType: string implements HasLabel
{
    case Staff = 'staff';
    case Client = 'client';
    case System = 'system';

    public function getLabel(): string
    {
        return __('filament/enums.comment_author_type.'.$this->value);
    }
}
