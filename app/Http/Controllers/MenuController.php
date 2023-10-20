<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  public function index(Request $request)
  {
    $query = $request->input('query', null);
    $per_page = $request->input('per_page', 10);
    $order = $request->input('order', 'asc');

    $menus = Menu::when($query != null, function ($q) use ($query) {
      $q->where('name', 'like', '%' . $query . '%');
    })->orderBy('id', $order)
      ->paginate($per_page);

    return $menus->toJson();
  }

  public function showMenuItems()
  {
    $menu_id = 1;
    // @todo : 로그인한 사람의 roles에서 menu_id 불러오는 방식으로 추후 변경
    $menu_items = MenuItem::where('menu_id', $menu_id)
      ->where('parent_id', null)
      ->where('is_active', true)
      ->orderBy('order')
      ->get();
    foreach ($menu_items as $menu_item) {
      $menu_item->children = MenuItem::where('menu_id', $menu_id)
        ->where('parent_id', $menu_item->id)
        ->where('is_active', true)
        ->orderBy('order')
        ->get();
    }
    return $menu_items->toJson();
  }

  public function storeMenu(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    Menu::create([
      'name' => $request->name,
    ]);
    return 'add success';
  }

  public function editMenu(Menu $menu, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    $menu->update([
      'title' => $request->title,
    ]);
    return 'edit success';
  }

  public function deleteMenu(Menu $menu)
  {
    $menu->delete();
    // menuItem 같이 삭제
    return 'delete success';
  }

  public function addMenuItem(Request $request)
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

  public function editMenuItem(MenuItem $menuItem, Request $request)
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

  public function deleteMenuItem(MenuItem $menuItem)
  {
    $menuItem->delete();
    return 'delete success';
  }

  public function orderMenuItem(Menu $menu, Request $request)
  {
    $array = $request[ 'order' ];
    $this->orderMenuItemArray($menu, $array);

    return 'order success';
  }

  public function orderMenuItemArray($menu, $array, $parent_id = null)
  {
    for ($i = 0; $i < sizeof($array); $i++) {
      $menuItem = MenuItem::where('menu_id', $menu)->find($array[ $i ][ 'id' ]);
      if ($menuItem === null) {
        continue;
      }
      $menuItem->update([
        'order' => $i + 1,
        'parent_id' => $parent_id ?? null,
      ]);
      if (isset($array[ $i ][ 'children' ])) {
        $this->orderMenuItemArray($menu, $array[ $i ][ 'children' ], $array[ $i ][ 'id' ]);
      }
    }
  }
}