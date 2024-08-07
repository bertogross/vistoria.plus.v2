<body class="{{ str_contains($_SERVER['SERVER_NAME'], 'development.') ? 'development' : 'production' }}">
    <script>0</script>

    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-theme avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
