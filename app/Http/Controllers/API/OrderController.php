<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ReceiptController;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\User;
use Carbon\Carbon;
use Faker\Provider\Base;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Facades\Invoice;
use Validator;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userid = Auth::guard('api')->user()->id;
        $user_role = Auth::guard('api')->user()->user_role;

        if ($userid == null || $user_role == null) {
            return $this->sendError('Unauthorized');
        }

        if ($request->input('status')){
            if ($request->input('status') == 'ongoing') {
                $orders = Order::whereHas('order_status', function ($query) use ($request) {
                    return $query->where('order_status', '!=', 'completed');
                })->with('order_item', 'order_item.menu')->with('order_status')->get();
            } else {
                $orders = Order::whereHas('order_status', function ($query) use ($request) {
                    return $query->where('order_status', '=', $request->input('status'));
                })->with('order_status')->get();
            }
        } else {
            if ($user_role == 'admin') {
                $orders = Order::all();
            } else if ($user_role == 'customer') {
                $orders = Order::where('user_id', $userid)->get();
            } else {
                return $this->sendError('No permission to access this route');
            }
        }

        if (count($orders) > 0) {
            return $this->sendResponse($orders, 'Retrieve all orders success', 201);
        }

        return $this->sendError('No orders available');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $userid = Auth::guard('api')->user()->id;

        $request_data = $request->all();

        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'payment_method' => 'required|in:credit_card',
            'items.*.id' => 'required|numeric|exists:menus,id,deleted_at,NULL',
            'items.*.quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $items = $request_data['items'];
        $request_data['order_total'] = 0;
        $request_data['order_delivery'] = 0;
        $request_data['order_tax'] = 0;

        $request_data['user_id'] = $userid;
        $request_data['order_number'] = "test";
        $request_data['distance'] = 0;

        foreach ($items as $item) {
            $price = Menu::find($item['id'])->price;
            $request_data['order_total'] += $item['quantity'] * $price;
        }

        $request_data['order_grand_total'] =  ($request_data['order_total'] + $request_data['order_delivery']);
        $request_data['order_tax'] = $request_data['order_grand_total'] * 0.1;
        $request_data['order_grand_total'] += $request_data['order_tax'];

        $order = Order::create($request_data);

        foreach ($items as $item) {
            $item['menu_id'] = $item['id'];
            $item['order_id'] = $order['id'];
            OrderItem::create($item);
        }

        OrderStatus::create([
            'order_id' => $order->id,
            'order_status'=> 'confirmed'
        ]);

        $path = $this->createInvoice($order->id);

        $order->receipt = $path;
        $order->save();

        return $this->sendResponse($order, 'Order created', 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found');
        }

        return $this->sendResponse($order, 'Order retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found');
        }

        $order->delete();
        return $this->sendResponse(null, 'Order deleted successfully.');
    }

    public function createInvoice($id) {
        $order = Order::find($id);
        $user = User::find($order->user_id);
        $customer = new Buyer([
            'name'          => $user->name,
            'custom_fields' => [
                'address' => $order->address,
                'telephone' => $user->customer->telephone,
            ],
        ]);

        $client = new Party([
            'name'          => 'Melcafe Delivery',
            'phone'         => '(0274) 2808881',
            'custom_fields' => [
                'address'        => 'Jl. Babarsari No.3, Janti, Caturtunggal Kec. Depok, Kab. Sleman, DI Yogyakarta INDONESIA 55281',
                'email' => 'kontak@melcafe.tugasbesar.com'
            ],
        ]);

        $items = [];
        $order_item = $order->order_item;

        foreach ($order_item as $item) {
            array_push($items, (new InvoiceItem())->title($item->menu->name)->pricePerUnit($item->menu->price)->quantity($item->quantity));
        }


        $invoice = Invoice::make()
            ->series($order->order_number)
            ->serialNumberFormat('{SERIES}')
            ->buyer($customer)
            ->seller($client)
            ->discountByPercent(10)
            ->currencySymbol('Rp.')
            ->currencyCode('IDR')
            ->taxRate(10)
            ->date(new Carbon($order->created_at))
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyThousandsSeparator('.')
            ->currencyDecimalPoint(',')
            ->addItems($items)
            ->payUntilDays(-1)
            ->name("Melcafe Receipt")
            ->filename($order->order_number)
            ->save('public');

        return $invoice->url();
    }
}
