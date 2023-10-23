<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'url',
    'target',
    'menu_id',
    'parent_id',
    'color',
    'icon_class',
    'order',
    'route',
    'parameters',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  // refs
  public function menu(): BelongsTo
  {
    return $this->belongsTo(Menu::class, 'menu_id', 'id');
  }

  public function children(): HasMany
  {
    return $this->hasMany(MenuItem::class, 'parent_id', 'id');
  }
}
