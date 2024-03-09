<div class="col">
    <div class="card pricing-box ribbon-box right text-center">
        @if($subscriptionType == 'pro' && $productMetadata->type  == 'primary')
            <div class="ribbon-two ribbon-two-theme"><span class="small">Vigente</span></div>
        @endif

        <div class="row g-0" data-price-id="{{$PriceId}}">
            <div class="{{ $productFeatures ? 'col-lg-6' : '' }}">
                <div class="card-body h-100 bg-body">
                    <div>
                        <h5 class="mb-1 text-uppercase">{{ $productName }}</h5>
                        {{--
                            <p class="text-muted">Professional plans</p>
                        --}}
                    </div>

                    <div class="py-4">
                        <h2>
                            <sup><small class="small">R$</small></sup>
                            <span class="price-wrap-{{ $PriceId }} text-theme" data-unit_amount="{{ numberFormat($unitAmount, 0) }}">
                                {{ isset($currentPriceId) && $currentPriceId == $PriceId ? number_format((($unitAmount/$intervalCount) * $currentQuantity), 0, ',', '.') : number_format(($unitAmount/$intervalCount), 0, ',', '.') }}
                            </span>
                        </h2>
                        <div class="form-text text-center text-body">
                            <span>
                                @if ($productMetadata->type == 'primary')
                                    recorrência mensal
                                @else
                                    {{$productDescription ? 'Cada '.$productDescription.': ' : ''}}
                                    {{ brazilianRealFormat(($unitAmount/$intervalCount), 0) }}/mês
                                @endif
                            </span>
                            {{-- !empty($planTypeText) ? '<span class="text-danger fs-13">*</span>' : '' --}}
                        </div>
                    </div>

                    <div class="text-center plan-btn mt-2">
                        @if ($productMetadata->type == 'primary')
                            <input class="quantity-{{ $PriceId }}" type="hidden" value="1" readonly autocomplete="off">
                        @else
                            <div class="input-step full-width light mb-3 {{ isset($currentPriceId) && $currentPriceId == $PriceId ? 'bg-soft-primary' : '' }}">
                                <button type="button" class="minus btn-minus-plus" data-action="minus" data-target="{{ $PriceId }}">-</button>

                                <input class="quantity-{{ $PriceId }}" type="text" placeholder="{{ isset($currentPriceId) && $currentPriceId == $PriceId && isset($currentQuantity) ? $currentQuantity : 'Quantidade' }}" readonly autocomplete="off">

                                <button type="button" class="plus btn-minus-plus" data-action="plus" data-target="{{ $PriceId }}">+</button>
                            </div>
                        @endif
                        <button
                            class="btn w-100
                            @if($productMetadata->type  == 'primary')
                                {{ $subscriptionType == 'pro' ? ' btn-subscription-cancel btn-outline-light ' : ' btn-subscription btn-theme ' }}
                            @elseif($productMetadata->type  == 'addon')
                                {{ isset($currentPriceId) && $currentPriceId == $PriceId ? ' btn-subscription-update btn-outline-light ' : ' btn-subscription btn-theme' }}
                            @endif
                            waves-effect waves-light text-uppercase"
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
                                @if($productMetadata->type  == 'primary')
                                    {{ $subscriptionType == 'pro' ? 'Cancelar' : 'Atualizar para Versão PRO' }}
                                @elseif($productMetadata->type  == 'addon')
                                    {{ isset($currentPriceId) && $currentPriceId == $PriceId ? 'Atualizar' : 'Contratar' }}
                                @else
                                    <div class="alert alert-danger">Necessário via Stripe declarar o Metadado type</div>
                                @endif
                        </button>
                    </div>
                </div>
            </div>
            <!--end col-->
            @if ($productFeatures)
                <div class="col-lg-6">
                    <div class="card-body h-100 border-start mt-4 mt-lg-0 bg-body">
                        <div class="card-header bg-light">
                            <h5 class="fs-15 mb-0">Recursos:</h5>
                        </div>
                        <div class="card-body pb-0">
                            <ul class="list-unstyled vstack gap-3 mb-0">
                                @foreach ( $productFeatures as $key => $value )
                                    <li><i class="ri-add-line me-2 text-theme align-middle"></i>{{$value->name}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!--end col-->
            @endif
        </div>
        <!--end row-->
    </div>
</div>
