<?php

namespace App\Http\Controllers\API;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class MenuController extends BaseController
{
    /**
     * Fungsi index GET /api/menu akan menampilkan semua data menu
     * @return JsonResponse
     */
    public function index()
    {
        $menu = Menu::all();

        if (count($menu) > 0)
            return $this->sendResponse($menu, 'Menu retrieved successfully');

        return $this->sendError('Menu empty');
    }

    /**
     * Fungsi store POST /api/menu akan menyimpan data menu
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $store_data = $request->all();
        $validator = Validator::make($store_data, [
            'name' => 'required|max:30',
            'description' => 'required',
            'size' => 'required|numeric',
            'size_unit' => 'required|alpha',
            'price' => 'required|numeric',
            'image_path' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $menu = Menu::create($store_data);

        return $this->sendResponse($menu, 'Menu created successfully');
    }

    /**
     * Fungsi GET /api/menu/{id} akan menampilkan data menu berdasarkan id
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $menu = Menu::find($id);

        if (is_null($menu))
            return $this->sendError('Menu not found');

        return $this->sendResponse($menu, 'Menu retrieved successfully');
    }

    /**
     * Fungsi update PUT /api/menu/{id} akan mengedit data menu berdasarkan id
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (is_null($menu))
            return $this->sendError('Menu not found');

        $store_data = $request->all();
        $validator = Validator::make($store_data, [
            'name' => 'required|max:30',
            'description' => 'required',
            'size' => 'required|numeric',
            'size_unit' => 'required|alpha',
            'price' => 'required|numeric',
            'image_path' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation error', $validator->errors());

        $menu->name = $store_data['name'];
        $menu->description = $store_data['description'];
        $menu->size = $store_data['size'];
        $menu->size_unit = $store_data['size_unit'];
        $menu->price = $store_data['price'];
        $menu->image_path = $store_data['image_path'];

        $menu->save();

        return $this->sendResponse($menu, 'Menu updated successfully');
    }

    /**
     * Fungsi destroy DELETE /api/menu/{id} akan menghapus menu berdasarkan id
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (is_null($menu))
            return $this->sendError('Menu not found');

        $menu->delete();

        return $this->sendResponse(null, 'Menu deleted successfully.');
    }
}
