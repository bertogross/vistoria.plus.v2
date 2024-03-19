<div id="scrollbar">
    <div class="container-fluid">

        <div id="two-column-menu">
        </div>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title"><i class="ri-more-fill"></i> <span><?php echo app('translator')->get('translation.components'); ?></span></li>

            <li class="nav-item">
                <a class="nav-link menu-link <?php echo e(request()->is('settings/account*') ? 'active' : ''); ?>" href="<?php echo e(route('settingsAccountShowURL')); ?>">
                    <i class="ri-add-fill"></i> <span>Meu <?php echo e(appName()); ?></span>
                </a>
            </li>

            

            

            <li class="nav-item">
                <a class="nav-link menu-link <?php echo e(request()->is('settings/connections') ? 'active' : ''); ?>" href="<?php echo e(route('settingsConnectionsIndexURL')); ?>">
                    <i class="ri-share-line"></i> <span>Minhas Conexões</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-link <?php echo e(request()->is('settings/companies') ? 'active' : ''); ?>" href="<?php echo e(route('settingsCompaniesIndexURL')); ?>">
                    <i class="ri-store-3-fill"></i> <span>Unidades Corporativas</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-link <?php echo e(request()->is('settings/storage') ? 'active' : ''); ?>" href="<?php echo e(route('settingsStorageIndexURL')); ?>">
                    <i class="ri-server-line"></i> <span>Armazenamento</span>
                </a>
            </li>


            <!--
            <li class="nav-item">
                <a class="nav-link menu-link <?php echo e(request()->is('settings/security') ? 'active' : ''); ?>" href="#">
                    <i class="ri-shield-keyhole-line"></i> <span><?php echo app('translator')->get('translation.security'); ?></span>
                </a>
            </li>
            -->
        </ul>
    </div>
</div>
<div class="sidebar-background d-none"></div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/settings/components/nav.blade.php ENDPATH**/ ?>