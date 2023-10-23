<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuItemController extends Controller
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  public function show()
  {
    // @todo : testCode
    $menu = Menu::find(1);
    //
    $menu_items = $menu->menuItems()
      ->where('parent_id', null)
      ->where('is_active', true)
      ->with('children')
      ->orderBy('order')
      ->get();
    return $menu_items->toJson();
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'menu_id' => 'numeric|required',
      'title' => 'required',
      'route' => 'required',
      'parent_id' => 'numeric',
    ]);
    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }
    $parent_id = $request->parent_id;

    $last_item = MenuItem::select('order')
      ->where('parent_id', $parent_id)
      ->where('menu_id', $request->menu_id)
      ->orderBy('order', 'desc')
      ->first();
    if ($last_item === null) {
      $request[ 'order' ] = 1;
    } else {
      $request[ 'order' ] = $last_item->order + 1;
    }
    MenuItem::create(
      $request->all()
    );
    return 'create success';
  }

  public function update(MenuItem $menuItem, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'menu_id' => 'numeric|required',
      'title' => 'required',
      'parent_id' => 'numeric',
    ]);
    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    $menuItem->update([
      $request->all(),
    ]);
  }

  public function destroy(MenuItem $menuItem)
  {
    $menuItem->delete();
    return 'delete success';
  }
}