<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserConnections;
use App\Models\Stripe;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{

    public function createStripeSession(Request $request)
    {
        $currentUserId = auth()->id();

        try {
            $stripe = Stripe::getStripeClient();

        } catch (\Exception $e) {
            \Log::error('createStripeSession: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        // Assuming you have a method to get or create a customer
        $customerId = Stripe::getOrCreateStripeCustomer($currentUserId);

        $priceId = $request->input('price_id', '');
        $quantity = max(1, (int) $request->input('quantity', 1));

        try {
            $checkoutSession = $stripe->checkout->sessions->create([
                'customer' => $customerId,
                'client_reference_id' => $currentUserId,
                'success_url' => url('/settings/account?tab=subscription'),
                'cancel_url' => url('/settings/account?tab=subscription'),
                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => $quantity,
                    ],
                ],
                'mode' => 'subscription',
                'allow_promotion_codes' => false,
            ]);

            return response()->json(['success' => true, 'stripe' => $checkoutSession]);
        } catch (\Exception $e) {
            \Log::error('createStripeSession: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar sessão Stripe: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelStripeSubscriptions(Request $request)
    {
        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('createStripeSession: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }


        try {
            // Deactive all connected users
            UserConnections::unsetUsersConnectedInMyAccount();

            // Delete subscriptions
            Stripe::cancelStripeSubscriptionItems();

            return response()->json(['success' => true, 'message' => 'Assinatura cancelada!']);
        } catch (\Exception $e) {
            \Log::error('createStripeSession: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar assinatura: ' . $e->getMessage()
            ]);
        }
    }

    /*public function updateStripeSubscriptionItem(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));

        // Assuming you have a middleware to check for user login
        // and a method to get onboard data
        $subscriptionData = getSubscriptionData();
        $subscriptionId = $subscriptionData['subscription_id'] ?? '';
        $subscriptionStatus = $subscriptionData['subscription_status'] ?? '';

        $priceId = $request->input('price_id', '');
        $subscriptionItemId = $request->input('subscription_item_id', '');
        $productQuantity = $request->input('quantity', 0);

        if ($subscriptionStatus != 'active') {
            $message = '<p>Para prosseguir você deverá primeiramente ativar sua assinatura</p>';

            return response()->json([
                'success' => false,
                'message' => $message
            ]);
        }

        if (!empty($subscriptionItemId) && $productQuantity > 0) {
            try {
                $stripe->subscriptionItems->update(
                    $subscriptionItemId,
                    ['price' => $priceId, 'quantity' => $productQuantity]
                );

                //And, update users table via webhook case 'customer.subscription.updated'

                return response()->json(['success' => true, 'title' => 'Assinatura Atualizada']);
            } catch (\Exception $e) {
                \Log::error('updateStripeSubscriptionItem: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Algo errado ocorreu. Por favor, atualize a página e tente novamente. '
                ]);
            }
        }
    }
    */

    /*
    public function addonCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $subscriptionData = getSubscriptionData();
            $subscriptionStatus = $subscriptionData['subscription_status'] ?? '';

            if ($subscriptionStatus != 'active') {
                return response()->json(['error' => 'Subscription not active'], 403);
            }

            $cart = $request->input('cart', []);
            $cartDetails = $this->getCartDetails($cart);

            return response()->json(['cartDetails' => $cartDetails]);
        } catch (\Exception $e) {
            \Log::error('addonCart: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter detalhes dos Addons'
            ]);
        }
    }
    */


}
