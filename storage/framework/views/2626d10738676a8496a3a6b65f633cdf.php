<div class="col">
    <div class="card pricing-box ribbon-box right text-center">
        <?php if($subscriptionType == 'pro' && $productMetadata->product_type  == 'primary'): ?>
            <div class="ribbon-two ribbon-two-theme"><span class="small">Vigente</span></div>
        <?php endif; ?>

        <div class="row g-0" data-price-id="<?php echo e($PriceId); ?>">
            <div class="<?php echo e($productFeatures ? 'col-lg-6' : ''); ?>">
                <div class="card-body h-100 bg-body">
                    <div>
                        <h5 class="mb-1 text-uppercase"><?php echo e($productName); ?></h5>
                        
                    </div>

                    <div class="py-4">
                        <h2>
                            <sup><small class="small">R$</small></sup>
                            <span class="price-wrap-<?php echo e($PriceId); ?> text-theme" data-unit_amount="<?php echo e(numberFormat($unitAmount, 0)); ?>">
                                <?php echo e(isset($currentPriceId) && $currentPriceId == $PriceId ? number_format((($unitAmount/$intervalCount) * $currentQuantity), 0, ',', '.') : number_format(($unitAmount/$intervalCount), 0, ',', '.')); ?>

                            </span>
                        </h2>
                        <div class="form-text text-center text-body">
                            <span>
                                <?php if($productMetadata->product_type == 'primary'): ?>
                                    recorrência mensal
                                <?php else: ?>
                                    <?php echo e($productDescription ? 'Cada '.$productDescription.': ' : ''); ?>

                                    <?php echo e(brazilianRealFormat(($unitAmount/$intervalCount), 0)); ?>/mês
                                <?php endif; ?>
                            </span>
                            
                        </div>
                    </div>

                    <div class="text-center plan-btn mt-2">
                        <?php if($productMetadata->product_type == 'primary'): ?>
                            <input class="quantity-<?php echo e($PriceId); ?>" type="hidden" value="1" readonly autocomplete="off">
                        <?php else: ?>
                            <div class="input-step full-width light mb-3 <?php echo e(isset($currentPriceId) && $currentPriceId == $PriceId ? 'bg-soft-primary' : ''); ?>">
                                <button type="button" class="minus btn-minus-plus" data-action="minus" data-target="<?php echo e($PriceId); ?>">-</button>

                                <input class="quantity-<?php echo e($PriceId); ?>" type="text" placeholder="<?php echo e(isset($currentPriceId) && $currentPriceId == $PriceId && isset($currentQuantity) ? $currentQuantity : 'Quantidade'); ?>" readonly autocomplete="off">

                                <button type="button" class="plus btn-minus-plus" data-action="plus" data-target="<?php echo e($PriceId); ?>">+</button>
                            </div>
                        <?php endif; ?>
                        <button
                            class="btn w-100
                            <?php if($productMetadata->product_type  == 'primary'): ?>
                                <?php echo e($subscriptionType == 'pro' ? ' btn-subscription-cancel btn-outline-light ' : ' btn-subscription btn-theme '); ?>

                            <?php elseif($productMetadata->product_type  == 'storage'): ?>
                                <?php echo e(isset($currentPriceId) && $currentPriceId == $PriceId ? ' btn-subscription-update btn-outline-light ' : ' btn-subscription btn-theme'); ?>

                            <?php endif; ?>
                            text-uppercase"
                            data-product_id="<?php echo e($productId); ?>"
                            data-price_id="<?php echo e($PriceId); ?>"
                            data-product_type="<?php echo e($productMetadata->product_type ?? ''); ?>"
                            data-recurring="<?php echo e($recurring); ?>"
                            data-interval_count="<?php echo e($intervalCount); ?>"
                            <?php if($productMetadata->product_type == 'primary'): ?>
                                data-current-quantity="1"
                                data-quantity="1"
                            <?php else: ?>
                                data-current-quantity="<?php echo e(isset($currentQuantity) ? $currentQuantity : 0); ?>"
                                data-quantity="<?php echo e(isset($currentPriceId) && $currentPriceId == $PriceId && isset($currentQuantity) ? $currentQuantity : 0); ?>"
                            <?php endif; ?>
                            data-current-price_id="<?php echo e(isset($currentPriceId) ? $currentPriceId : ''); ?>"
                            data-subscription_item_id="<?php echo e($subscriptionItemId); ?>"
                            <?php if($productMetadata->product_type == 'storage'): ?>
                                disabled
                            <?php endif; ?>
                            >
                                <?php if($productMetadata->product_type  == 'primary'): ?>
                                    <?php echo e($subscriptionType == 'pro' ? 'Cancelar' : 'Atualizar para Versão PRO'); ?>

                                <?php elseif($productMetadata->product_type  == 'storage'): ?>
                                    <?php echo e(isset($currentPriceId) && $currentPriceId == $PriceId ? 'Atualizar' : 'Contratar'); ?>

                                <?php else: ?>
                                    <div class="alert alert-danger">Necessário via Stripe declarar o Metadado type</div>
                                <?php endif; ?>
                        </button>
                    </div>
                </div>
            </div>
            <!--end col-->
            <?php if($productFeatures): ?>
                <div class="col-lg-6">
                    <div class="card-body h-100 border-start mt-4 mt-lg-0 bg-body">
                        <div class="card-header bg-light">
                            <h5 class="fs-15 mb-0">Recursos:</h5>
                        </div>
                        <div class="card-body pb-0">
                            <ul class="list-unstyled vstack gap-3 mb-0">
                                <?php $__currentLoopData = $productFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><i class="ri-add-line me-2 text-theme align-middle"></i><?php echo e($value->name); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <!--end col-->
            <?php endif; ?>
        </div>
        <!--end row-->
    </div>
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/settings/stripe/product-card-wide.blade.php ENDPATH**/ ?>