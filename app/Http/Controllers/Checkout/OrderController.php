<?php

namespace App\Http\Controllers\Checkout;

use App\Events\OrderCompletedEvent;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Cartalyst\Stripe\Stripe;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $link = Link::whereCode($request->input('code'))->first();
        $order = new Order();

        DB::beginTransaction();

        $order->first_name = $request->input('first_name');
        $order->last_name = $request->input('last_name');
        $order->email = $request->input('email');
        $order->code = $link->code;
        $order->user_id = $link->user->id;
        $order->influencer_email = $link->user->email;
        $order->address = $request->input('address');
        $order->address2 = $request->input('address2');
        $order->city = $request->input('city');
        $order->zip = $request->input('zip');

        $order->save();

        $lineItems = [];
        foreach ($request->input('items') as $item) {
            $product = Product::find($item['product_id']);
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_title = $product->title;
            $orderItem->price = $product->price;
            $orderItem->quantity = $item['quantity'];
            $orderItem->influencer_revenue = 0.1 * $product->price * $item['quantity'];
            $orderItem->admin_revenue = 0.9 * $product->price * $item['quantity'];

            $orderItem->save();

            $lineItems[] = [
                'name' => $product->title,
                'description' => $product->description,
                'images' => [$product->image],
                'amount' => 100 * $product->price,
                'currency' => 'idr',
                'quantity' => $orderItem->quantity
            ];
        }

        $stripe = Stripe::make(env('STRIPE_SECRET'));

        $source = $stripe->checkout()->sessions()->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'success_url' => env('CHECKOUT_ENDPOINT') . "/success?source={CHECKOUT_SESSION_ID}",
            'cancel_url' => env('CHECKOUT_ENDPOINT') . "/error"
        ]);

        $order->transaction_id = $source['id'];
        $order->save();

        DB::commit();

        return $source;
    }

    public function confirm(Request $request)
    {
        if (!$order = Order::whereTransactionId($request->input('source'))->first()) {
            return response([
                'error' => 'Order not found!'
            ], Response::HTTP_NOT_FOUND);
        }

        $order->complete = 1;
        $order->save();

        event(new OrderCompletedEvent($order));

        return response([
            'message' => 'success'
        ]);
    }
}
