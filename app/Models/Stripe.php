<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Stripe extends Model
{
    use HasFactory;

    protected $connection = 'vpAppTemplate';

    public $timestamps = true;


    protected static function getStripeClient()
    {
        return new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public static function cancelStripeSubscriptionItems()
    {

        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('cancelStripeSubscription: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        // Assuming you have a method to get the customer ID
        $customerId = self::handleGetStripeCustomerId();

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ]);
        }

        try {
            $subscriptionItems = $stripe->subscriptions->all([
                'customer' => $customerId
            ]);

            foreach ($subscriptionItems->data as $subscription) {
                $stripe->subscriptions->cancel(
                    $subscription->id,
                    ['prorate' => true]
                );
            }
        } catch (\Exception $e) {
            \Log::error('cancelStripeSubscriptionItems: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar assinatura'
            ]);
        }
    }

    public static function getOrCreateStripeCustomer($userId)
    {
        $user = User::find($userId);

        // Check if user was found
        if (!$user) {
            // Handle the error, e.g., log it or return a custom error response
            \Log::error("User with ID {$userId} not found.");
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ]);
        }

        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('cancelStripeSubscription: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        try {
            $customerId = self::handleGetStripeCustomerId($userEmail);

            // If customer doesn't exist, create
            if (!$customerId) {
                $customerId = self::handleCreateStripeCustomer($userEmail);
            }

            return $customerId;
        } catch (\Exception $e) {
            \Log::error('getOrCreateStripeCustomer: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar dados Stripe'
            ]);
        }
    }

    public static function handleGetStripeCustomerId($userEmail = null)
    {
        if($userEmail){
            $user = User::findUserByEmail($userEmail);
            if(!$user){
                $user = auth()->user();
            }
            $userEmail = $user->email;
            $userName = $user->name ?? explode('@', $userEmail)[0];
        }else{
            $user = auth()->user();
            $userEmail = $user->email;
            $userName = $user->name ?? explode('@', $userEmail)[0];
        }

        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('cancelStripeSubscription: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        $customers = $stripe->customers->search([
            'query' => "email:'{$userEmail}'",
        ]);

        $customerId = null;
        if($customers){
            foreach ($customers->data as $customer) {
                $customerId = $customer->id;
                break;
            }
        }

        return $customerId;
    }

    public static function handleCreateStripeCustomer($userEmail = null)
    {
        if($userEmail){
            $user = User::findUserByEmail($userEmail);
            if(!$user){
                $user = auth()->user();
            }
            $userEmail = $user->email;
            $userName = $user->name ?? explode('@', $userEmail)[0];
        }else{
            $user = auth()->user();
            $userEmail = $user->email;
            $userName = $user->name ?? explode('@', $userEmail)[0];
        }


        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('cancelStripeSubscription: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        $customer = $stripe->customers->create([
            'name' => $userName,
            'email' => $userEmail,
            'preferred_locales' => ['pt-BR'],
            //'metadata' => ['onboard_id' => $userId]
        ]);

        if(!$customer->id){
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar cadastro na Stripe'
            ]);
        }
        $customerId = $customer->id;

        // Update user's Stripe customer ID in the database
        $user->stripe_customer_id = $customerId;
        $user->save();

        return $customerId;
    }

    public static function handleWebhookSubscriptionStatus($checkout)
    {
        $stripe = Stripe::getStripeClient();

        if (!$stripe) {
            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ], 500); // 500 Internal Server Error
        }

        $customerId = $checkout->customer;
        $subscriptionId = $checkout->subscription;

        // Cancel other subscriptions
        // TODO STRIPE ????
        // self::stripeSubscriptionCancelOthers($subscriptionId, $customerId);

        if (!empty($subscriptionId)) {
            try {
                $retrieveSubscription = $stripe->subscriptions->retrieve(
                    $subscriptionId,
                    []
                );

                $subscriptionStatus = !empty($retrieveSubscription->status) ? $retrieveSubscription->status : 'trialing';
                $quantity = isset($retrieveSubscription->quantity) ? intval($retrieveSubscription->quantity) : 0;

                $data = [
                    'customer_id' => $customerId,
                    'subscription_id' => $subscriptionId,
                    'subscription_status' => $subscriptionStatus,
                    'subscription_quantity' => $quantity,
                    'subscription_type' => 'pro'
                ];
                // Update the database
                $result = DB::connection('vpOnboard')->table('users')
                    ->where('stripe_customer_id', $customerId)
                    ->update([
                        'subscription_data' => $data
                    ]);

                if (!$result) {
                    // Log error and return response
                    \Log::error('Falha na atualização do banco de dados', [
                        'method' => __METHOD__,
                        'path' => __FILE__
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Falha na atualização do banco de dados'
                    ], 500); // 500 Internal Server Error
                }
            } catch (\Exception $e) {
                // Log error and return response
                \Log::error($e->getMessage(), [
                    'method' => __METHOD__,
                    'path' => __FILE__,
                    'stripeCode' => $e->getCode()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500); // 500 Internal Server Error
            }
        }

        return response()->json(['success' => true], 200); // 200 OK
    }

    public static function handleWebhookSubscriptionUpdated($subscription)
    {
        $stripe = Stripe::getStripeClient();

        if (!$stripe) {
            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ], 500); // 500 Internal Server Error
        }

        $productIds = array();

        $subscriptionId = $subscription->id;

        $customerId = $subscription->customer;

        $subscriptionStatus = !empty($subscription->pause_collection['behavior']) ? $subscription->pause_collection['behavior'] : '';

        $quantity = isset($subscription->quantity) ? intval($subscription->quantity) : 0;

        $subscriptionStatus = !empty($subscriptionStatus) && $subscriptionStatus == 'mark_uncollectible' ? 'uncollectible' : 'active';


        /*************************
         * Start Extract Product IDs
         * https://stripe.com/docs/api/subscriptions/retrieve
         *************************/
        $subscriptions = $stripe->customers->retrieve(
            $customerId,
            [ 'expand' => ['subscriptions'] ]
        );
        $subscriptionsItems = $subscriptions->subscriptions->data[0]->items->data;

        if( !empty($subscriptionsItems) && is_array($subscriptionsItems) && count($subscriptionsItems) > 0 ){
            foreach($subscriptionsItems as $key => $item){
                $productIds[] = array(
                    'item' => isset($item->id) ? $item->id : '',
                    'price' => isset($item->price->id) ? $item->price->id : '',
                    'product' => isset($item->price->product) ? $item->price->product : '',
                );
            }
        }

        $productIds = array_filter($productIds);
        /**************************
         * End Extract Product IDs
         *************************/

        if( !empty($customerId) && !empty($subscriptionId) ){

            $data = [
                'customer_id' => $customerId,
                'subscription_id' => $subscriptionId,
                'subscription_status' => $subscriptionStatus,
                'subscription_quantity' => $quantity,
                'subscription_type' => $subscriptionStatus == 'active' ? 'pro' : 'free',
                'products' => $productIds
            ];
            $result = DB::connection('vpOnboard')->table('users')
                ->where('stripe_customer_id', $customerId)
                ->update([
                    'subscription_data' => $data
                ]);

            if(!$result){
                http_response_code(302);

                exit();
            }
        }
    }

    public static function handleWebhookSubscriptionDeleted($subscription)
    {
        $subscriptionId = isset($subscription->id) ? $subscription->id : '';

        $customerId = isset($subscription->customer) ? $subscription->customer : '';

        $subscriptionStatus = isset($subscription->status) ? $subscription->status : 'active';

        // TODO STRIPE??? delete/pause all subscription (users and addons)

        if( !empty($customerId) && !empty($subscriptionId) ){

            $data = [
                'customer_id' => $customerId,
                'subscription_id' => $subscriptionId,
                'subscription_status' => $subscriptionStatus,
                'subscription_quantity' => 0,
                'subscription_type' => 'free'
            ];
            $result = DB::connection('vpOnboard')->table('users')
                ->where('stripe_customer_id', $customerId)
                ->update([
                    'subscription_data' => $data,
                ]);

            if(!$result){
                http_response_code(302);

                exit();
            }
        }
    }

    public static function subscriptionStripeStatusTranslation($status)
    {
        //https://stripe.com/docs/api/subscriptions/object
        //Possible values are incomplete, incomplete_expired, trialing, active, past_due, canceled, or unpaid. The 'uncollectible' is a custom value returned when mark_uncollectible

        switch ($status) {
            case 'trialing':
            case 'incomplete':
            case 'incomplete_expired':
                $label = 'versão demonstrativa';
                $description = 'Esta versão permite a inserção de alguns registros com a finalidade de que você habitue-se com a aplicação. <br><br> Para mais recursos será necessário assinar o '.appName().'.';
                $color = 'warning';
                $class = '';
                break;
            case 'active':
                $label = 'Assinatura Ativa';
                $description = 'Este é um indicador de que a assinatura está financeiramente em dia.';
                $color = 'success';
                $class = '';
            break;
            case 'past_due':
            case 'unpaid':
                $label = 'Assinatura requer atenção';
                $description = 'Este indicador sugere que você verifique e atualize o método de pagamento.';
                $color = 'danger';
                $class = 'blink';
                break;
            case 'canceled':
                $label = 'Assinatura Cancelada';
                $description = 'Este é um indicador de que a assinatura foi cancelada.';
                $color = 'danger';
                $class = '';
                break;
            case 'uncollectible'://this ia s custom value, not stripe
                $label = 'Assinatura Suspensa';
                $description = 'Este é um indicador de que você solicitou a suspensão da assinatura.';
                $color = 'warning';
                $class = '';
                break;
            default:
                $label = '';
                $description = '';
                $color = '';
                $class = '';
        }

        $description .= "<br><br><small>*Etiqueta exibida somente ao Administrativo</small>";

        return array('label' => $label, 'description' => $description, 'color' => $color, 'class' => $class);
    }


}
