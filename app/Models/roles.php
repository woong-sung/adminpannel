<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'menu_id',
    ];

    // refs
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id', 'id');
    }
}
