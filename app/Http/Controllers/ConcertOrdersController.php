<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConcertOrdersController extends Controller
{
    private PaymentGateway $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store(int $concertId)
    {
        /** @var Concert $concert */
        $concert = Concert::published()->findOrFail($concertId);
        $this->validate(\request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'numeric', 'min:1'],
            'payment_token' => ['required'],
        ]);

        try {
            $tickets = $concert->findTickets(request('ticket_quantity'));

            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

            $order = $concert->createOrder(\request('email'), $tickets);

            return response()->json($order->toArray(), Response::HTTP_CREATED);
        } catch (PaymentFailedException | NotEnoughTicketsException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
