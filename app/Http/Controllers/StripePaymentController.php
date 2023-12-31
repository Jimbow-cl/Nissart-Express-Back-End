<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use ErrorException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    // Paiement via Stripe
    public function payByStripe(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Récuperation des données du Front
            $metadata = $request->all();;

            // Créer un paiement avec les métadonnées
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $metadata['price'], // Utiliser $metadata['price'] au lieu de $metadata->price
                'currency' => 'eur',
                'description' => $metadata['type'],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'setup_future_usage' => 'on_session',
                // Appel de la fonction de choix des metadatas
                'metadata' => $this->getMetadata($metadata)
            ]);
            $order = Order::create([
                'user_id' => Auth::id(),
                'paiement_id' => $paymentIntent->id,
                'type' => $metadata['type'],
                'status' => "WAITING",
                'metadata' => json_encode($paymentIntent->metadata)
            ]);
            $output = [
                'clientSecret' => $paymentIntent->client_secret,
                'order' => $order->id
            ];
            return response()->json($output);
        } catch (ErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getMetadata($metadata)
    {

        if ($metadata['type'] == "card") {
            return ([
                    'value' => $metadata['value'],
                    'user_id' => $metadata['user_id'],
                    'price' => $metadata['price'],
                ]);
        } else if ($metadata['type'] == "ticket") {
            return ([
                    'end' => $metadata['end'],
                    'start' => $metadata['start'],
                    'passenger' => $metadata['passenger'],
                    'schedule' => $metadata['schedule'],
                    'class' => $metadata['class'],
                    'user_id' => $metadata['user_id'],
                    'price' => $metadata['price'],
                ]);
        } else if ($metadata['type'] == "fine") {
            return ([
                    'description' => $metadata['description'],
                    'user_id' => $metadata['user_id'],
                    'price' => $metadata['price'],
                ]);
        }
    }
}
