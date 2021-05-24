<?php

namespace App;

use App\Models\Order;

class Reservation
{
    private $tickets;
    /**
     * @var string
     */
    private $email;

    /**
     * Reservation constructor.
     * @param $tickets
     * @param string $email
     */
    public function __construct($tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function cancel()
    {
        $this->tickets->each->release();
    }

    public function tickets()
    {
        return $this->tickets;
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->email;
    }

    public function complete($paymentGateway, $paymentToken)
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);

        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }
}
