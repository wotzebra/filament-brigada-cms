<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;

    protected $guarded = [];

    protected $casts = [
        'online' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('online', fn ($query) => $query->where('online', true));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
