<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.error'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
<body>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
        <div class="auth-page-wrapper pt-5">
            <!-- auth page bg -->
            <div class="auth-one-bg-position auth-one-bg"  id="auth-particles">
                <div class="bg-overlay"></div>

                <div class="shape">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                        <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                    </svg>
                </div>
            </div>

            <!-- auth page content -->
            <div class="auth-page-content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center pt-4">
                                <div class="">
                                    <img src="<?php echo e(URL::asset('build/images/error400-cover.png')); ?>" alt="" class="error-basic-img move-animation">
                                </div>
                                <div class="mt-n2">
                                    <!--<h1 class="display-1 fw-medium">404</h1>-->
                                    <h3 class="text-uppercase">Desculpe, 😭</h3>
                                    <p class="text-muted mb-4">A sessão que você está procurando não está disponível!</p>
                                    <a href="<?php echo e(url('/')); ?>" class="init-loader btn btn-theme"><i class="mdi mdi-home me-1"></i>Voltar ao início</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->

                </div>
                <!-- end container -->
            </div>
            <!-- end auth page content -->

            <!-- footer -->
            <footer class="footer d-none d-lg-block d-xl-block">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <p class="mb-0 text-muted">&copy; <?php echo e(date('Y')); ?> <?php echo e(appName()); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>
        <!-- end auth-page-wrapper -->

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<!-- particles js -->
<script src="<?php echo e(URL::asset('build/libs/particles.js/particles.js')); ?>"></script>
<!-- particles app js -->
<script src="<?php echo e(URL::asset('build/js/pages/particles.app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/errors/auth-404-basic.blade.php ENDPATH**/ ?>