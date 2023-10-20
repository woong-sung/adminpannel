<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = [
      'name',
    ];



    //ref
    public function menuItems(): HasMany
    {
      return $this->hasMany(MenuItem::class, 'menu_id', 'id');
    }
}
