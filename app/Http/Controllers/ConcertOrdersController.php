<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
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
        $this->validate(request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'numeric', 'min:1'],
            'payment_token' => ['required'],
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));

            $order = $reservation->complete($this->paymentGateway, request('payment_token'));

            return response()->json($order, Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
