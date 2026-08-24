<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemRoute extends Model
{
    protected $fillable = [
        'route_name',
        'display_name',
    ];

    /**
     * Menus associated with this system route
     *
     * @return HasMany
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
