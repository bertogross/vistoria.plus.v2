<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    

    <?php if( Request::is('settings*') ): ?>
        <?php $__env->startComponent('settings.components.nav'); ?>
        <?php echo $__env->renderComponent(); ?>
    <?php endif; ?>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>