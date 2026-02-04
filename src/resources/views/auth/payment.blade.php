@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/payment.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">{{ $item->name }}を購入</h1>
    <div class="price">金額(税込): ¥<?php echo number_format($item->price,0); ?></div>

    <form class="payment-form" id="payment-form">
        @csrf
        <div class="payment__card" id="card-element" style="display: {{ $payment === 'card' ? 'block' : 'none' }};"></div>
        <button class="payment__form--button" id="submit">支払う</button>
    </form>

    <div id="error-message" style="color:red;"></div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const card = elements.create('card', { hidePostalCode: true });
        card.mount('#card-element');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (element) => {
            element.preventDefault();
            document.getElementById('submit').disabled = true;

            let paymentMethodId = null;

            @if($payment === 'card')
                // カード払いのときだけpaymentMethod作成
                const {paymentMethod, error} = await stripe.createPaymentMethod({
                    type: 'card',
                    card: card
                });

                if (error) {
                    document.getElementById('error-message').textContent = error.message;
                    document.getElementById('submit').disabled = false;
                    return;
                }

                paymentMethodId = paymentMethod.id;
            @endif

            // store に送信
            const response = await fetch("/payment/store", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_method: paymentMethodId
                })
            });

            const result = await response.json();

            if(result.error) {
                document.getElementById('error-message').textContent = result.error;
                document.getElementById('submit').disabled = false;
            } else {
                alert(result.message);
                window.location.href = result.redirect;
            }
        });
    </script>

</div>
@endsection
