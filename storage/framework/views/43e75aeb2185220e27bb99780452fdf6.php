<h4 class="mb-0">Complementos</h4>

<p>Enriqueça seu <?php echo e(appName()); ?> incorporando recursos adicionais</p>

<div class="row mt-4">
    <?php if($subscriptionId): ?>
        <?php
            try {
                $retrieveSubscription = $stripe->subscriptions->retrieve(
                    $subscriptionId,
                    []
                );

                $currentPriceId = !empty($retrieveSubscription->plan->id) ? $retrieveSubscription->plan->id : '';
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }

            try {
                $subscriptionItems = $stripe->subscriptionItems->all([
                    'subscription' => $subscriptionId,
                ]);
                $subscriptionItemId = $subscriptionId && isset($subscriptionItems->data[0]->id) ? $subscriptionItems->data[0]->id : '';
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php
        $products = $stripe->products->all([
            'active' => true,
            'limit' => 100
        ]);
        $products = $products->data ?? [];
    ?>
    <?php if(!empty($products)): ?>
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
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
            ?>
            <?php if( !empty($prices) && is_array($prices) && count($prices) > 0
             && $productMetadata->product_type == 'storage' ): ?>
                <?php $__currentLoopData = $prices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $PriceId = isset($price['id']) ? $price['id'] : '';

                        $nickname = $price['nickname'] ? $price['nickname'] : '';

                        $unitAmount = $price['unit_amount'] ? intval($price['unit_amount'])/100 : '';

                        $recurring = isset($price['recurring']) ? $price['recurring']->interval : '';

                        $intervalCount = isset($price['recurring']) ? $price['recurring']->interval_count : '';

                        $interval = isset($price['recurring']) ? $price['recurring']->interval : '';

                        $metadata = isset($price['product']) ? $price['product']->metadata : '';
                        /*
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
                        */
                    ?>

                    <?php echo $__env->make('settings.stripe.product-card-wide', ['product_type' => 'storage'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="alert alert-warning">Ainda não há produtos disponíveis.</div>
    <?php endif; ?>
</div>

<div id="load-cart"></div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/settings/stripe/addons.blade.php ENDPATH**/ ?>