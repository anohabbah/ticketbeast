<p>{{ $order->confirmation_number }}</p>
<p>${{ number_format($order->amount / 100, 2) }}</p>
<p>**** **** **** {{ $order->card_last_four_number }}</p>
@foreach($order->tickets as $ticket)
    <p>{{ $ticket->code }}</p>
@endforeach
