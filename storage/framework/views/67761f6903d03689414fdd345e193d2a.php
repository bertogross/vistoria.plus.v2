<?php $__env->startSection('title'); ?>
    Offline
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-page-content">
            <div class="container">
                <div class="text-center mt-sm-5 pt-4">
                    <div class="mb-5 text-white-50">
                        <h1 class="h1 coming-soon-text">No momento, você não está conectado a nenhuma rede.</h1>
                        <p class="fs-14">Verifique sua conexão a internet</p>
                        <div class="mt-4 pt-2">
                            <a href="<?php echo e(url('/')); ?>" class="btn btn-theme"><i class="mdi mdi-home me-1"></i> Voltar ao início</a>
                        </div>
                    </div>

                    <div class="row justify-content-center mb-5">
                        <div class="col-xl-4 col-lg-8">
                            <div>
                                <img src="<?php echo e(URL::asset('build/images/widget-img.png')); ?>" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/vendor/laravelpwa/offline.blade.php ENDPATH**/ ?>