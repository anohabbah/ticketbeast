<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
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
        $concert = Concert::published()->findOrFail($concertId);
        $this->validate(\request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'numeric', 'min:1'],
            'payment_token' => ['required'],
        ]);

        try {
            /** @var Concert $concert */
            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));
            $concert->orderTickets(request('email'), request('ticket_quantity'));
            return response()->json([], Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
