<h4 class="mb-0">Plano de Assinatura</h4>

@if (isset($subscriptionData['subscription_status']) && $subscriptionData['subscription_status'] != 'active')
    <p>Ative seu {{ appName() }}</p>
@endif

<div class="row mt-4">
    @if ($subscriptionId)
        @php
            try {
                $retrieveSubscription = $stripe->subscriptions->retrieve(
                    $subscriptionId,
                    []
                );

                $currentPriceId = !empty($retrieve_subscription->plan->id) ? $retrieve_subscription->plan->id : '';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            try {
                $subscriptionItems = $stripe->subscriptionItems->all([
                    'subscription' => $subscriptionId,
                ]);
                $subscriptionItemId = $subscriptionId && isset($subscriptionItems->data[0]->id) ? $subscriptionItems->data[0]->id : '';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        @endphp

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif
    @endif

    @php
        $products = $stripe->products->all([
            'active' => true,
            'limit' => 100
        ]);
        $products = $products->data ?? [];
    @endphp
    @if (!empty($products))
        @foreach ($products as $product)
            @php
                $productId = $product['id'] ?? '';
                $productName = $product['name'] ?? '';
                $productDescription = $product['description'] ?? '';
                $productFeatures = $product['features'] ?? '';
                $productMetadata = $product['metadata'] ?? '';

                //https://stripe.com/docs/api/prices/list
                $prices = $stripe->prices->all(
                    ['product' => $productId,
                        'active' => true,
                        'limit' => 100,
                        'expand' => ['data.product']
                    ]
                );
                $prices = isset($prices->data) ? $prices->data : '';

                asort($prices);
            @endphp
            @if( !empty($prices) && is_array($prices) && count($prices) > 0 && $productMetadata->type == 'primary' )
                @foreach( $prices as $key => $price)
                    @php
                        $PriceId = isset($price['id']) ? $price['id'] : '';

                        $nickname = $price['nickname'] ? $price['nickname'] : '';

                        $unitAmount = $price['unit_amount'] ? intval($price['unit_amount'])/100 : '';

                        $recurring = isset($price['recurring']) ? $price['recurring']->interval : '';

                        $intervalCount = isset($price['recurring']) ? $price['recurring']->interval_count : '';

                        $interval = isset($price['recurring']) ? $price['recurring']->interval : '';

                        $metadata = isset($price['product']) ? $price['product']->metadata : '';
                        $planType = isset($metadata->plan_type) ? trim($metadata->plan_type) : '';
                        switch ($planType) {
                            case 'annual':
                                $planTypeText = 'contrato 12 meses';
                                break;
                            case 'quarterly':
                                $planTypeText = 'contrato 3 meses';
                                break;
                            default:
                                $planTypeText = '';
                        }
                    @endphp

                     @include('settings.stripe.product-card-wide')
                @endforeach
            @endif
        @endforeach
    @else
        <div class="alert alert-warning">Ainda não há produtos disponíveis.</div>
    @endif
</div>

<div id="load-cart"></div>
