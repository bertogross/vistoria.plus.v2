<?php $__env->startSection('title'); ?>
    Meu <?php echo e(appName()); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/dropzone/dropzone.css')); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/filepond/filepond.min.css')); ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('url'); ?>
            <?php echo e(url('settings')); ?>

        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_1'); ?>
            <?php echo app('translator')->get('translation.settings'); ?>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Meu <?php echo e(appName()); ?>

        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <p>Atualize seus dados cadastrais e monitore o faturamento de forma contínua</p>

    <?php echo $__env->make('components.alerts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $subscriptionData = getSubscriptionData();
        //appPrintR($subscriptionData);
        $subscriptionType = $subscriptionData['subscription_type'] ?? 'free';
        //appPrintR($subscriptionType);

        $tab = request('tab', null);
        $tab = $tab && in_array($tab, ['invoices', 'subscription', 'users', 'addons', 'account']) ? $tab : null;
    ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-2">
                    <div class="nav nav-pills flex-column nav-pills-tab verti-nav-pills custom-verti-nav-pills nav-pills-theme" role="tablist" aria-orientation="vertical">
                        <a class="nav-link text-uppercase <?php echo e(!$tab || $tab == 'subscription' ? 'active show' : ''); ?>" id="v-pills-stripe-subscription-tab" data-bs-toggle="pill" href="#v-pills-stripe-subscription" role="tab" aria-controls="v-pills-stripe-subscription" aria-selected="false">Assinatura</a>

                        <a class="nav-link text-uppercase <?php echo e($tab == 'invoices' ? 'active show' : ''); ?>" id="v-pills-invoices-tab" data-bs-toggle="pill" href="#v-pills-invoices" role="tab" aria-controls="v-pills-invoices" aria-selected="false">Faturamento</a>

                        <a class="nav-link text-uppercase <?php echo e($tab == 'users' ? 'active show' : ''); ?>" id="v-pills-stripe-users-tab" data-bs-toggle="pill" href="#v-pills-stripe-users" role="tab" aria-controls="v-pills-stripe-users" aria-selected="false">Usuários Conectados</a>

                        <?php if(env('APP_DEBUG')): ?>
                            <a class="nav-link text-uppercase <?php echo e($tab == 'addons' ? 'active show' : ''); ?>" id="v-pills-stripe-addons-tab" data-bs-toggle="pill" href="#v-pills-stripe-addons" role="tab" aria-controls="v-pills-stripe-addons" aria-selected="false">Complementos</a>
                        <?php endif; ?>

                        <a class="nav-link text-uppercase <?php echo e($tab == 'account' ? 'active show' : ''); ?>" href="#v-pills-account" id="v-pills-account-tab" data-bs-toggle="pill" role="tab" aria-controls="v-pills-account" aria-selected="true">Dados da Conta</a>
                    </div>
                </div> <!-- end col-->
                <div class="col-lg-10">
                    <div class="tab-content text-muted mt-3 mt-lg-2">
                        <div class="tab-pane fade <?php echo e(!$tab || $tab == 'subscription' ? 'active show' : ''); ?>" id="v-pills-stripe-subscription" role="tabpanel" aria-labelledby="v-pills-stripe-subscription-tab">
                            <?php echo $__env->make('settings.stripe.subscription', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade <?php echo e($tab == 'invoices' ? 'active show' : ''); ?>" id="v-pills-invoices" role="tabpanel" aria-labelledby="v-pills-invoices-tab">
                            <?php echo $__env->make('settings.stripe.invoices', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade <?php echo e($tab == 'users' ? 'active show' : ''); ?>" id="v-pills-stripe-users" role="tabpanel" aria-labelledby="v-pills-stripe-users-tab">
                            <?php echo $__env->make('settings.stripe.users', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade <?php echo e($tab == 'addons' ? 'active show' : ''); ?>" id="v-pills-stripe-addons" role="tabpanel" aria-labelledby="v-pills-stripe-addons-tab">
                            <?php echo $__env->make('settings.stripe.addons', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade <?php echo e($tab == 'account' ? 'active show' : ''); ?>" id="v-pills-account" role="tabpanel" aria-labelledby="v-pills-account-tab">
                            <?php echo $__env->make('settings.account-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div><!--end tab-pane-->
                    </div>
                </div> <!-- end col-->
            </div> <!-- end row-->
        </div><!-- end card-body -->
    </div><!--end card-->

    <?php echo $__env->make('settings.stripe.modal-subscription-details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('settings.stripe.modal-upcoming', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/js/pages/password-addon.init.js')); ?>"></script>

    <script src="<?php echo e(URL::asset('build/libs/dropzone/dropzone-min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond/filepond.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')); ?>"></script>

    <script>
        var uploadLogoURL = "<?php echo e(route('uploadLogoURL')); ?>";
        var deleteLogoURL = "<?php echo e(route('deleteLogoURL')); ?>";
        var assetURL = "<?php echo e(URL::asset('/')); ?>";
        var stripeSubscriptionURL = "<?php echo e(route('stripeSubscriptionURL')); ?>";
        var stripeCancelSubscriptionURL = "<?php echo e(route('stripeCancelSubscriptionURL')); ?>";
        //var stripeSubscriptionDetailsURL = "";
        //var stripeCartAddonURL = "";
    </script>
    <script src="<?php echo e(URL::asset('build/js/settings-account.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script>
        var settingsIndexURL = "<?php echo e(route('settingsIndexURL')); ?>";
        var uploadAvatarURL = "<?php echo e(route('uploadAvatarURL')); ?>";
        var uploadCoverURL = "<?php echo e(route('uploadCoverURL')); ?>";
        var getUserFormContentURL = "<?php echo e(route('getUserFormContentURL')); ?>";
        var settingsUsersStoreURL = "<?php echo e(route('settingsUsersStoreURL')); ?>";
        var settingsUsersUpdateURL = "<?php echo e(route('settingsUsersUpdateURL')); ?>";
        var settingsAccountShowURL = "<?php echo e(route('settingsAccountShowURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/settings-users.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script type="module">
        import { attachImage } from '<?php echo e(URL::asset('build/js/settings-attachments.js')); ?>';

        var uploadAvatarURL = "<?php echo e(route('uploadAvatarURL')); ?>";

        attachImage("#member-image-input", ".avatar-img", uploadAvatarURL, false);
    </script>

    <script src="<?php echo e(URL::asset('build/js/settings-stripe.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/settings/account.blade.php ENDPATH**/ ?>