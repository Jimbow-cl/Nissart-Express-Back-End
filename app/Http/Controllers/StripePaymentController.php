<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use ErrorException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    // Paiement via Stripe
    public function payByStripe(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        Log::info($request);

        try {
            // Récuperation des données du Front
            $metadata = $request;

            // Créer un paiement avec les métadonnées
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $metadata['price'], // Utiliser $metadata['price'] au lieu de $metadata->price
                'currency' => 'eur',
                'description' => $metadata['type'],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'setup_future_usage' => 'on_session',
                'metadata' => [
                    'end' => $metadata['end'],
                    'start' => $metadata['start'],
                    'passenger' => $metadata['passenger'],
                    'schedule' => $metadata['schedule'],
                    'class' => $metadata['class'],
                    'user_id' => $metadata['user_id'],
                    'price' => $metadata['price'],
                    'voucher_id' => $metadata['voucher_id']
                ]
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency
            ];
            return response()->json($output);
        } catch (ErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
