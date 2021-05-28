<?php


namespace App;


class RandomOrderConfirmationNumber implements OrderConfirmationNumberGenerator
{
    public function generate(): string
    {
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
