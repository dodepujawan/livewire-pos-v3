<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'system_route_id',
        'title',
        'icon',
        'sort_order',
        'is_sidebar',
        'launcher_group',
    ];

    protected $casts = [
        'is_sidebar' => 'boolean',
    ];

    /**
     * Parent Menu
     */
    /**
     * Parent Menu
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Child menus
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('sort_order');
    }

    /**
     * Related system route (if any)
     *
     * @return BelongsTo
     */
    public function systemRoute(): BelongsTo
    {
        return $this->belongsTo(SystemRoute::class, 'system_route_id');
    }

    /**
     * Related launcher group (if any)
     *
     * @return BelongsTo
     */
    public function launcherGroup(): BelongsTo
    {
        return $this->belongsTo(LauncherGroup::class, 'launcher_group', 'key');
    }
}
