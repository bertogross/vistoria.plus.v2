
<div class="col-sm-12 col-md-12 col-lg-6 col-xl-4 m-auto">
    <div class="card pricing-box bg-black bg-opacity-10 ribbon-box right">
        <div class="card-body p-4 m-2 {{ isset($currentPriceId) && $currentPriceId == $PriceId ? 'bg-light' : '' }}">

            @if($subscriptionType == 'pro' && $productMetadata->type  == 'primary')
                <div class="ribbon-two ribbon-two-theme"><span class="small">Vigente</span></div>
            @endif

            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="mb-1 fw-semibold text-center text-uppercase">
                        {{ $productName }}
                    </h4>
                </div>
            </div>

            <div class="pt-4">
                <h1 class="text-center">
                    <sup><small class="small">R$</small></sup>
                    <span class="price-wrap-{{ $PriceId }} text-theme" data-unit_amount="{{ numberFormat($unitAmount, 0) }}">
                        {{ isset($currentPriceId) && $currentPriceId == $PriceId ? number_format((($unitAmount/$intervalCount) * $currentQuantity), 0, ',', '.') : number_format(($unitAmount/$intervalCount), 0, ',', '.') }}
                    </span>
                </h1>
                <div class="form-text text-center text-body">
                    <span>
                        {{$productDescription ? 'Cada '.$productDescription.': ' : ''}}
                        {{ brazilianRealFormat(($unitAmount/$intervalCount), 0) }}/mês
                    </span>
                    {{ !empty($planTypeText) ? '<span class="text-danger fs-13">*</span>' : '' }}
                </div>
            </div>

            <div class="mt-4 mb-4 text-center">
                {{ !empty($planTypeText) ? '<div class="small"><span class="text-danger fs-13">*</span>'.$planTypeText.'</div>' : '' }}

                @if ($productFeatures)
                    <h6>Recursos:</h6>
                    <ul class="list-unstyled text-muted vstack gap-2 text-center">
                        @foreach ( $productFeatures as $key => $value )
                            <li><i class="ri-add-line me-2 text-theme align-middle"></i>{{$value->name}}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($productMetadata->type == 'primary')
                <input class="quantity-{{ $PriceId }}" type="hidden" value="1" readonly autocomplete="off">
            @else
                <div class="input-step full-width light {{ isset($currentPriceId) && $currentPriceId == $PriceId ? 'bg-soft-primary' : '' }}">
                    <button type="button" class="minus btn-minus-plus" data-action="minus" data-target="{{ $PriceId }}">-</button>
                    <input class="quantity-{{ $PriceId }}" type="text" placeholder="{{ isset($currentPriceId) && $currentPriceId == $PriceId && isset($currentQuantity) ? $currentQuantity.' Unidades' : 'Quantidade' }}" readonly autocomplete="off">
                    <button type="button" class="plus btn-minus-plus" data-action="plus" data-target="{{ $PriceId }}">+</button>
                </div>
            @endif

            <div class="mt-4">
                <button
                    class="btn btn-outline-theme w-100 waves-effect waves-light text-uppercase {{ isset($currentPriceId) && $currentPriceId == $PriceId ? 'btn-subscription-update' : 'btn-subscription' }}"
                    data-product_id="{{ $productId }}"
                    data-price_id="{{ $PriceId }}"
                    data-type="{{ $productMetadata->type ?? '' }}"
                    data-recurring="{{ $recurring }}"
                    data-interval_count="{{  $intervalCount }}"
                    @if ($productMetadata->type == 'primary')
                        data-current-quantity="1"
                        data-quantity="1"
                    @else
                        data-current-quantity="{{ isset($currentQuantity) ? $currentQuantity : 0 }}"
                        data-quantity="{{ isset($currentPriceId) && $currentPriceId == $PriceId && isset($currentQuantity) ? $currentQuantity : 0 }}"
                    @endif
                    data-current-price_id="{{ isset($currentPriceId) ? $currentPriceId : '' }}"
                    data-subscription_item_id="{{ $subscriptionItemId }}"
                    @if ($productMetadata->type == 'addon')
                        disabled
                    @endif
                    >
                        {{ isset($currentPriceId) && $currentPriceId == $PriceId ? 'Atualizar' : 'Assinar' }}
                </button>
            </div>
        </div>
    </div>
</div>
