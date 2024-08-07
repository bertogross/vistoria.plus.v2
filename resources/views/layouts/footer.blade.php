<footer class="footer d-none d-lg-block d-xl-block">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                {{date('Y')}} © {{ appName() }}
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    {{ subscriptionLabel() }}
                </div>
            </div>
        </div>
    </div>
</footer>
