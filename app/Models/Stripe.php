<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
            \Log::error('getStripeClient on cancelStripeSubscription: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        // Assuming you have a method to get the customer ID
        $customerId = self::getStripeCustomerId();

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

    // Stripe routine to cancel/active user subscription item with metadata product_type == users
    public static function subscriptionGuestUserAddonUpdate($hostId = null)
    {
        if (!$hostId) {
            \Log::error('hostId is null');

            return false;
        }
        $user = User::find($hostId);
        $hostEmail = $user->email;

        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('getStripeClient on subscriptionGuestUserAddonUpdate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        $subscriptionItemId = $subscriptionItemPriceId = null;

        $subscriptionData = getSubscriptionData($hostId);
        $subscriptionId = $subscriptionData && isset($subscriptionData['subscription_id']) ? $subscriptionData['subscription_id'] : null;

        $customerId = self::getStripeCustomerId($hostEmail);

        // Example: Count active user connections from your database
        $activeUserConnectionsCount = UserConnections::where('connection_status', 'active')->count();

        $metaKey = 'product_type';
        $metaValue = 'users';

        //Get all subscriptions by metadata product_type == users
        try {
            $allSubscriptionItems = $stripe->subscriptionItems->all([
                'limit' => 100,
                'subscription' => $subscriptionId,
            ]);

            foreach ($allSubscriptionItems->autoPagingIterator() as $item) {
                // Retrieve the price object
                $price = $stripe->prices->retrieve($item->price->id, ['expand' => ['product']]);

                // Now, $price->product contains the product object associated with this price
                // Check if the product's metadata matches your criteria
                if (isset($price->product->metadata[$metaKey]) && $price->product->metadata[$metaKey] == $metaValue) {
                    $subscriptionItemId = $item->id; // Capture the subscription item ID

                    break; // Found the match, exit the loop
                }
            }
        } catch (\Exception $e) {
            // Handle any errors that occur during the API call
            \Log::error('Failed to get all subscriptions from hostId: '.$hostId.' and subscriptionData: '.json_encode($subscriptionData).': ' . $e->getMessage());
            //return response()->json(['success' => false, 'error' => $e->getMessage()]);
            return false;
        }

        // First: if are, find the current user subscription item id
        if($subscriptionItemId){
            try {
                // Update the quantity of the subscription item
                $stripe->subscriptionItems->update($subscriptionItemId, [
                    'quantity' => $activeUserConnectionsCount
                ]);

                //return response()->json(['success' => true, 'message' => 'Subscription add-on quantity updated successfully.']);
                return true;
            } catch (\Exception $e) {
                // Handle any errors that occur during the update
                \Log::error('Failed first step to update subscription add-on users quantity: ' . $e->getMessage());

                //return response()->json(['success' => false, 'message' => 'Failed to update subscription add-on users quantity: ' . $e->getMessage()]);
                return false;
            }
        }else{
            // Second: else, find the price item id by metadata product_type == users and update current subscription with new item
            try {
                $allPrices = $stripe->prices->all([
                    'limit' => 100,
                    'active' => true,
                    'expand' => ['data.product'] // Expand the product data within each price object
                ]);

                foreach ($allPrices->data as $price) {
                    // Since the product is expanded in the price object, you can directly access the product's metadata
                    if (isset($price->product->metadata[$metaKey]) && $price->product->metadata[$metaKey] == $metaValue) {
                        $subscriptionItemPriceId = $price->id; // Capture the price ID

                        break; // Found the match, exit the loop
                    }
                }
            } catch (\Exception $e) {
                // Handle any errors that occur during the API call
                \Log::error('Failed to get all prices: ' . $e->getMessage());
                //return response()->json(['success' => false, 'error' => $e->getMessage()]);
                return false;
            }

            try {
                $stripe->subscriptionItems->create([
                    'subscription' => $subscriptionId,
                    'price' => $subscriptionItemPriceId,
                    'quantity' => $activeUserConnectionsCount
                ]);

                //return response()->json(['success' => true, 'subscriptionItem' => $subscriptionItem]);
                return true;
            } catch (\Exception $e) {
                // Handle any errors that occur during the API call
                \Log::error('Failed second step to update subscription add-on users quantity: ' . $e->getMessage());
                //return response()->json(['success' => false, 'error' => $e->getMessage()]);
                return false;
            }
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
            \Log::error('getStripeClient on getOrCreateStripeCustomer: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        try {
            $customerId = self::getStripeCustomerId($userEmail);

            // If customer doesn't exist, create
            if (!$customerId) {
                $customerId = self::createStripeCustomer($userEmail);
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

    public static function getStripeCustomerId($userEmail = null)
    {
        if(!$userEmail){
            $user = auth()->user();
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ]);
            }
            $userEmail = $user->email;
            $userName = $user->name ?? explode('@', $userEmail)[0];
        }else{
            $user = User::findUserByEmail($userEmail);
            if(!$user){
                $user = auth()->user();
            }
            $userEmail = $user->email;
            $userName = $user->name ?? explode('@', $userEmail)[0];
        }

        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('getStripeClient on getStripeCustomerId: ' . $e->getMessage());

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

    public static function createStripeCustomer($userEmail = null)
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
            \Log::error('getStripeClient on createStripeCustomer: ' . $e->getMessage());

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

    public static function handleWebhookSubscriptionCheckoutSession($checkout)
    {
        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('getStripeClient on handleWebhookSubscriptionCheckoutSession: ' . $e->getMessage());

            http_response_code(302);
        }

        $customerId = $checkout->customer;
        $subscriptionId = $checkout->subscription;

        if (!empty($subscriptionId)) {
            try {
                $retrieveSubscription = $stripe->subscriptions->retrieve(
                    $subscriptionId,
                    []
                );
            } catch (\Exception $e) {
                // Log error and return response
                \Log::error('Retrieve subscription failure handleWebhookSubscriptionCheckoutSession: ' . $e->getMessage());

                http_response_code(302);
            }

            try {
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
                DB::connection('vpOnboard')->table('users')
                    ->where('stripe_customer_id', $customerId)
                    ->update([
                        'subscription_data' => $data
                    ]);

            } catch (\Exception $e) {
                // Log error and return response
                \Log::error('Database failure handleWebhookSubscriptionCheckoutSession: ' . $e->getMessage());

                http_response_code(302);
            }
        }

        return response()->json(['success' => true], 200); // 200 OK
    }

    public static function handleWebhookSubscriptionUpdated($subscription)
    {
        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('getStripeClient on handleWebhookSubscriptionUpdated: ' . $e->getMessage());

            http_response_code(302);
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
                $products[] = array(
                    'item' => isset($item->id) ? $item->id : '',
                    'price' => isset($item->price->id) ? $item->price->id : '',
                    'product' => isset($item->price->product) ? $item->price->product : '',
                );
            }
        }

        $products = array_filter($products);
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
                'products' => $products
            ];
            try{
                $result = DB::connection('vpOnboard')->table('users')
                    ->where('stripe_customer_id', $customerId)
                    ->update([
                        'subscription_data' => $data
                    ]);

            } catch (\Exception $e) {
                // Log error and return response
                \Log::error('handleWebhookSubscriptionUpdated: ' . $e->getMessage());

                http_response_code(302);
            }
        }
    }

    public static function handleWebhookSubscriptionDeleted($subscription)
    {
        $subscriptionId = isset($subscription->id) ? $subscription->id : '';

        $customerId = isset($subscription->customer) ? $subscription->customer : '';

        if( !empty($customerId) && !empty($subscriptionId) ){

            $data = [
                'customer_id' => $customerId,
                'subscription_id' => $subscriptionId,
                'subscription_status' => 'inactive',
                'subscription_quantity' => 0,
                'subscription_type' => 'free'
            ];
            try{
                $result = DB::connection('vpOnboard')->table('users')
                    ->where('stripe_customer_id', $customerId)
                    ->update([
                        'subscription_data' => $data,
                    ]);

            } catch (\Exception $e) {
                // Log error and return response
                \Log::error('handleWebhookSubscriptionDeleted: ' . $e->getMessage());

                http_response_code(302);
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
