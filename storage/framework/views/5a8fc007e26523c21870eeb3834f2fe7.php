    <script src="<?php echo e(URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/simplebar/simplebar.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/node-waves/waves.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/feather-icons/feather.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>

<?php echo $__env->yieldContent('script'); ?>
<?php echo $__env->yieldContent('script-bottom'); ?>

<script>
    var assetURL = "<?php echo e(URL::asset('/')); ?>";
    var appVersion = "<?php echo e(env('APP_VERSION')); ?>";
    var profileChangeLayoutModeURL = "<?php echo e(route('profileChangeLayoutModeURL')); ?>";
    var profileChangeConnectionURL = "<?php echo e(route('profileChangeConnectionURL')); ?>";
</script>

<script src="<?php echo e(URL::asset('build/js/app.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/app-custom.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

<?php
    $HTTP_HOST = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $SUBDOMAIN = $HTTP_HOST ? strtok($HTTP_HOST, '.') : '';
?>
<?php if( $SUBDOMAIN && ( $SUBDOMAIN != 'app' && $SUBDOMAIN != 'checklist' ) ): ?>
    <?php
        $replacements = [
            'localhost:8000' => 'local',
            'localhost' => 'local',
            'development' => 'dev',
            'testing' => 'test'
        ];

        foreach ($replacements as $search => $replace) {
            $SUBDOMAIN = $SUBDOMAIN != 'vistoria' ? str_replace($search, $replace, $SUBDOMAIN) : '';
        }
    ?>
    <?php if($SUBDOMAIN): ?>
        <div class="ribbon-box border-0 ribbon-fill position-fixed top-0 start-0 d-none d-lg-block d-xl-block" data-bs-toggle="tooltip" data-bs-placement="right" title="<?php echo e($SUBDOMAIN); ?> Environment" style="z-index:5000; width: 60px; height:60px;">
            <div class="ribbon ribbon-<?php echo e($SUBDOMAIN == 'development' ? 'danger' : 'warning'); ?> text-uppercase"><?php echo e($SUBDOMAIN); ?></div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH D:\www\vistoria.plus\application\development2.vistoria.plus\public_html\resources\views/layouts/vendor-scripts.blade.php ENDPATH**/ ?>