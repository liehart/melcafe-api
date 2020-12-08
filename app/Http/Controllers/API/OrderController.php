<?php

namespace App\Http\Controllers\API;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Facades\Invoice;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    /**
     * Display a listing of the orders.
     * Return all orders if the request's role is admin.
     * Return all orders associated with current request's
     * user if the role is customer or driver.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->id == null || $user->user_role == null) {
            /**
             * Return unauthorized request if no user present checked from JWT token.
            */
            return $this->sendError('Unauthorized');
        }

        if ($request->input('status')){
            /**
             * Check request url query parameter ?status=ongoing
             * if status filter was ongoing return only non completed orders
             * otherwise return respective status: confirmed, on_process, on_delivery, completed
             */
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
            if ($user->user_role == 'admin') {
                $orders = Order::with('user')->with('order_status')->with('order_item', 'order_item.menu')->get();
            } else if ($user->user_role == 'customer') {
                $orders = Order::where('user_id', $user->id)->get();
            } else if ($user->user_role == 'driver') {
                $orders = Order::where('driver_id', $user->id)->get();
            } else {
                return $this->sendError('No permission to access this route');
            }
        }

        if (count($orders) > 0) {
            /**
             * Check the query result
             * if the result more than 0 return the orders
             * otherwise return orders is empty
             */
            return $this->sendResponse($orders, 'Retrieve all orders success', 201);
        }

        return $this->sendError('No orders available');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        /**
         * Get request's user id from JWT token
         */
        $userid = Auth::guard('api')->user()->id;

        $request_data = $request->all();

        /**
         * Validate data sent from client
         */
        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'payment_method' => 'required|in:credit_card',
            'items.*.id' => 'required|numeric|exists:menus,id,deleted_at,NULL',
            'items.*.quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            /**
             * Return error validation if one or many validation fail
             */
            return $this->sendError('Validation error', $validator->errors());
        }

        /**
         * Prepare data to store on database from request
         */
        $items = $request_data['items'];
        $request_data['order_total'] = 0;
        $request_data['order_delivery'] = 0;
        $request_data['order_tax'] = 0;

        $request_data['user_id'] = $userid;
        $request_data['order_number'] = 'ORDER-'.strtoupper(date('M',$_SERVER['REQUEST_TIME'])).'-'.strtoupper(uniqid());
        $distance = (new DistanceController)->getDistance($request_data['lat'], $request_data['lon'])->rows[0]->elements[0]->distance->value;
        $request_data['distance'] = $distance / 1000;
        $request_data['order_delivery'] = $distance * 2;

        foreach ($items as $item) {
            /**
             * Calculate order total by iterating items received
             */
            $price = Menu::find($item['id'])->price;
            $request_data['order_total'] += $item['quantity'] * $price;
        }

        $request_data['order_grand_total'] =  ($request_data['order_total'] + $request_data['order_delivery']);
        $request_data['order_tax'] = $request_data['order_total'] * 0.1;
        $request_data['order_grand_total'] += $request_data['order_tax'];

        $order = Order::create($request_data);

        foreach ($items as $item) {
            /**
             * Create orderItem detail by iterating items again.
             */
            $item['menu_id'] = $item['id'];
            $item['order_id'] = $order['id'];
            OrderItem::create($item);
        }

        /**
         * Set order status to confirmed automatically for new order
         */
        OrderStatus::create([
            'order_id' => $order->id,
            'order_status'=> 'confirmed'
        ]);

        /**
         * Generate PDF invoice
         */
        $path = $this->createInvoice($order->id);

        $order->receipt = $path;
        $order->save();

        return $this->sendResponse($order, 'Order created', 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
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
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found');
        }

        $request_data = $request->all();

        /**
         * Validate data sent from client
         */
        $validator = Validator::make($request->all(), [
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            /**
             * Return error validation if one or many validation fail
             */
            return $this->sendError('Validation error', $validator->errors());
        }

        $order->address = $request_data['address'];
        $order->save();

        return $this->sendResponse($order, 'Order retrieved successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
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
