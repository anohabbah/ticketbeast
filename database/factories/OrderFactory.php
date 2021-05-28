<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'card_last_four_number' => ' 1234',
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'amount' => 5250,
            'email' => 'somebody@example.com',
        ];
    }
}
