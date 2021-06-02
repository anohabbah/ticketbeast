<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrdersController extends Controller
{
    public function show(string $confirmationNumber)
    {
        $order = Order::findByConfirmationNumber($confirmationNumber);

        return view('orders.show', ['order' => $order]);
    }
}
