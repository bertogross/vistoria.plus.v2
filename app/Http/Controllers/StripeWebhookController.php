<?php

// app/Http/Controllers/StripeWebhookController.php
namespace App\Http\Controllers;

use Log;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SettingsStripeController;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {

        /**
         *
        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = 'whsec_fa12b509fae590245ebd2db1a24429c054bd4d57e2834a2aa0448fcf9041e065';
        */
        
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, env('STRIPE_WEBHOOK_SECRET_KEY')
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload.'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                // Handle the successful payment intent.

                break;
            case 'account.updated':
                $account = $event->data->object;
                // Handle....

                break;
            case 'customer.subscription.created':
                // Handle....

                break;
            case 'customer.subscription.deleted':
                SettingsStripeController::handleWebhookSubscriptionDeleted($event->data->object);

                break;
            case 'customer.subscription.updated': //https://stripe.com/docs/api/subscriptions/object
                SettingsStripeController::handleWebhookSubscriptionUpdated($event->data->object);

                break;
            case 'charge.succeeded':
            case 'charge.updated':
                // Handle....

                break;
            case 'checkout.session.completed':
                SettingsStripeController::subscriptionStatus($event->data->object);

                break;
            default:
                // Unexpected event type
                return response()->json(['error' => 'Unexpected event type.'], 400);
        }

        return response()->json(['success' => 'Webhook handled.']);
    }
}
