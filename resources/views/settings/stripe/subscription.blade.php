@php
    $currentQuantity = 0;
    $subscriptionData = getSubscriptionData();
    //appPrintR($subscriptionData);
    $subscriptionId = $subscriptionData['subscription_id'] ?? null;
    $subscriptionStatus = $subscriptionData['subscription_status'] ?? null;

    $dataBs = 'data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top"';

    $contentBs = "
        <h5>Assinatura PRO</h5>
        <ul class='list-unstyled'>
            <li><i class='ri-check-fill text-success me-2'></i>Anexar arquivos</li>
            <li><i class='ri-check-fill text-success me-2'></i>Registrar tarefas</li>
            <li><i class='ri-check-fill text-success me-2'></i>Adicionar usuários</li>
        </ul>
        <hr>
        <h5>Assinatura FREE</h5>
        <ul class='list-unstyled'>
            <li><i class='ri-close-line text-danger me-2'></i>Anexar arquivos</li>
            <li><i class='ri-close-line text-danger me-2'></i>Registrar tarefas</li>
            <li><i class='ri-close-line text-danger me-2'></i>Adicionar usuários</li>
        </ul>
    ";
@endphp

@switch($subscriptionStatus)
    @case('active')
        <span class="badge bg-success-subtle text-success badge-border float-end" {!! $dataBs !!} data-bs-title="Sua Assinatura: Pro" data-bs-content="{!! $contentBs !!}">
            Pro
        </span>
        @break
    @default
        @php
        @endphp
        <span class="badge bg-info-subtle text-info badge-border float-end" {!! $dataBs !!} data-bs-title="Sua Assinatura: Free" data-bs-content="{!! $contentBs !!}">
            Free
        </span>
@endswitch

<h4 class="mb-0">Plano de Assinatura</h4>
<p>Maximize seu {{ appName() }} com nosso plano de assinatura! Acesso a recursos exclusivos e atualizações constantes</p>

@if ($subscriptionStatus != 'active')
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
                //appPrintR($retrieveSubscription);

                $currentPriceId = $retrieveSubscription->plan->id ?? '';
                $currentQuantity = $retrieveSubscription->quantity ?? 0;

            } catch (\Exception $e) {
                $error = $e->getMessage();
            }

            try {
                $subscriptionItems = $stripe->subscriptionItems->all([
                    'subscription' => $subscriptionId,
                ]);
                //appPrintR($subscriptionItems);

                $subscriptionItemId = $subscriptionId && isset($subscriptionItems->data[0]->id) ? $subscriptionItems->data[0]->id : '';
            } catch (\Exception $e) {
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
        //appPrintR($products);

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

                    @include('settings.stripe.product-card-wide', ['type' => 'primary'])

                    @break
                @endforeach
            @endif
        @endforeach
    @else
        <div class="alert alert-warning">Ainda não há produtos disponíveis.</div>
    @endif
</div>

<div id="load-cart"></div>
