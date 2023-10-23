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

  public function order(Menu $menu, $orders, $parent_id = null)
  {
    for ($i = 0; $i < sizeof($orders); $i++) {
      $menuItem = $menu->menuItems()->find($orders[ $i ][ 'id' ]);
      if ($menuItem === null) {
        continue;
      }
      $menuItem->update([
        'order' => $i + 1,
        'parent_id' => $parent_id ?? null,
      ]);
      if (isset($array[ $i ][ 'children' ])) {
        $this->order($menu, $array[ $i ][ 'children' ], $array[ $i ][ 'id' ]);
      }
    }
  }

  // refs
  public function menuItems(): HasMany
  {
    return $this->hasMany(MenuItem::class, 'menu_id', 'id');
  }
}
