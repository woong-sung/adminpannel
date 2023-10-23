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
    })->orderBy('name', $order)
      ->paginate($per_page);

    return $menus->toJson();
  }

  public function store(Request $request)
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

  public function update(Menu $menu, Request $request)
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

  public function destroy(Menu $menu)
  {
    $menu->menuItems()->delete();
    $menu->delete();
    return 'delete success';
  }

  public function order(Menu $menu, Request $request)
  {
    $orders = $request[ 'order' ];
    $menu->order($menu, $orders);

    return 'order success';
  }
}