<?php


namespace App;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    /**
     * @var \Hashids\Hashids
     */
    private $hashids;

    public function __construct($salt)
    {
        $this->hashids = new \Hashids\Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function generateFor($ticket): string
    {
        return $this->hashids->encode($ticket->id);
    }
}
