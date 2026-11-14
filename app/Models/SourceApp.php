<?php

namespace App\Models;

use Database\Factories\SourceAppFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $user_id
 * @property string|null $pms_project_id
 * @property bool $is_active
 */
#[Fillable(['name', 'slug', 'user_id', 'pms_project_id', 'is_active'])]
class SourceApp extends Model
{
    /** @use HasFactory<SourceAppFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The service account that owns this app's Sanctum tokens.
     *
     * @return BelongsTo<User, $this>
     */
    public function serviceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
