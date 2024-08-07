import {
    toastAlert,
    showPreloader,
    swalWithBootstrapButtons
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {

    const swalWithBootstrap = swalWithBootstrapButtons();

    // Update subscription (change subscription plan)
    document.addEventListener('click', function(event) {
        const clickedElement = event.target;

        if(clickedElement){
            // Create Session for Subscription
            if (clickedElement.classList.contains('btn-subscription')) {
                event.preventDefault();
                clickedElement.blur();

                var priceId = clickedElement.getAttribute('data-price_id');
                var currentPriceId = clickedElement.getAttribute('data-current-price_id');
                var quantity = clickedElement.getAttribute('data-quantity');
                quantity = parseInt(quantity);

                if( !quantity ){
                    toastAlert('Informe a quantidade', 'danger', 10000);
                    return;
                }

                var params = {
                    'current_price_id': currentPriceId,
                    'price_id': priceId,
                    'quantity': quantity
                };

                showPreloader();

                fetch(stripeSubscriptionURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(params)
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        toastAlert(data.message, 'danger', 10000);
                        return;
                    }

                    var checkoutURL = data.stripe.url;
                    /*swalWithBootstrap.fire({
                        icon: '',
                        title: '',
                        html: '<img src="' + assetURL + 'build/images/stripe/white-small.png" title="Stripe" width="100" class="mb-3"><br>Redirecionado para a página de pagamento...'
                    });*/

                    toastAlert('Redirecionando para a página de pagamento...', 'info', 10000);

                    clickedElement.setAttribute('disabled', true);

                    setTimeout(function() {
                        window.location.href = checkoutURL;
                    }, 3000);
                })
                .catch(error => {
                    var message = error.message || 'Não foi possível proceder com a solicitação.<br>Tente novamente mais tarde.';

                    toastAlert(message, 'danger', 10000);
                })
                .finally(() => {
                    //APP_loading();
                    showPreloader(false);
                });
            }

            if (clickedElement.classList.contains('btn-subscription-cancel')) {
                event.preventDefault();
                clickedElement.blur();

                var priceId = clickedElement.getAttribute('data-price_id');
                var currentPriceId = clickedElement.getAttribute('data-current-price_id');

                var params = {
                    'current_price_id': currentPriceId,
                    'price_id': priceId
                };

                swalWithBootstrap.fire({
                    title: 'Cancelar assinatura?',
                    html: 'Com o cancelamento, os usuários conectados serão desativados.<br><br><span class="text-warning small fs-12">Você poderá reativar sua assinatura a qualquer momento.</span>',
                    icon: 'question',
                    confirmButtonText: 'Sim, cancelar',
                    cancelButtonText: 'Não',
                        showCancelButton: true,
                    denyButtonText: 'Nunca',
                        showDenyButton: false,
                    buttonsStyling: false,
                    showCloseButton: false,
                    allowOutsideClick: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        showPreloader();

                        fetch(stripeCancelSubscriptionURL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(params)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                toastAlert(data.message, 'danger', 10000);

                                showPreloader(false);

                                return;
                            }

                            toastAlert(data.message, 'success', 10000);
                        })
                        .catch(error => {
                            var message = error.message || 'Não foi possível proceder com a solicitação.<br>Tente novamente mais tarde.';

                            toastAlert(message, 'danger', 10000);

                            showPreloader(false);
                        })
                        .finally(() => {
                            //APP_loading();

                            showPreloader();

                            location.reload(true);
                        });

                    }
                })
            }

            if (clickedElement.classList.contains('btn-subscription-upcoming')) {
                event.preventDefault();
                clickedElement.blur();

                showPreloader();

                const modalElement = document.getElementById('stripeUpcomingModal');
                if(modalElement){
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false,
                    });
                    modal.show();
                }


                showPreloader(false);
            }

            // Update subscription (change subscription plan)
            /*
            if (clickedElement.id === 'btn-subscription-details') {
                event.preventDefault();
                clickedElement.blur();

                var subscriptionId = clickedElement.getAttribute('data-subscription_id');
                var priceId = clickedElement.getAttribute('data-price_id');
                var quantity = clickedElement.getAttribute('data-quantity');
                quantity = parseInt(quantity);

                if( !quantity ){
                    toastAlert('Informe a quantidade', 'danger', 10000);
                    return;
                }

                var params = {
                    'subscription_id': subscriptionId,
                    'price_id': priceId,
                    'quantity': quantity
                };

                showPreloader();

                fetch(stripeSubscriptionDetailsURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(params)
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        toastAlert(data.message, 'danger', 10000);
                        return;
                    }

                    var checkoutURL = data.stripe.url;

                    //swalWithBootstrap.fire({
                        //icon: '',
                        //title: '',
                        //html: '<img src="' + assetURL + 'build/images/stripe/white-small.png" title="Stripe" />/width="100" class="mb-3"><br>Redirecionado para a página de pagamento...'
                    //});

                    clickedElement.setAttribute('disabled', true);

                    toastAlert('Redirecionando para a página de pagamento...', 'info', 10000);

                    setTimeout(function() {
                        window.location.href = checkoutURL;
                    }, 2000);
                })
                .catch(error => {
                    var message = error.message || 'Não foi possível proceder com a solicitação.<br>Tente novamente mais tarde.';
                    toastAlert(message, 'danger', 10000);
                })
                .finally(() => {
                    //APP_loading();
                    showPreloader(false);
                });
            }
            */

            // Remove from cart
            /*
            if (clickedElement.classList.contains('btn-addon-cart-remove')) {
                event.preventDefault();
                var addon = clickedElement.getAttribute('data-addon');
                document.querySelector('.btn-addon-cart[data-addon="' + addon + '"]').click();
            }
            */

            // START Addons Cart
            /*
            if (clickedElement.classList.contains('btn-addon-cart')) {
                event.preventDefault();
                var cart = [];
                clickedElement.blur();

                if (clickedElement.classList.contains('selected')) {
                    if (clickedElement.classList.contains('option-label')) {
                        clickedElement.classList.remove('selected');
                        clickedElement.closest('.option').querySelector('input').checked = false;
                    } else {
                        clickedElement.classList.remove('selected');
                        clickedElement.innerHTML = 'Adicionar ao Carrinho';
                    }
                } else {
                    if (clickedElement.classList.contains('option-label')) {
                        document.querySelectorAll('input[name="storage"]').forEach(function(input) {
                            input.closest('.option').querySelector('.option-label').classList.remove('selected');
                        });

                        clickedElement.classList.add('selected');
                        clickedElement.closest('.option').querySelector('input').checked = true;
                    } else {
                        clickedElement.classList.add('selected');
                        clickedElement.innerHTML = '<i class="ri-check-line"></i> Adicionado';
                    }
                }

                document.querySelectorAll('.btn-addon-cart.selected').forEach(function(selectedBtn) {
                    cart.push(selectedBtn.getAttribute('data-addon'));
                });

                var jsonStr = JSON.stringify(cart);
                sessionStorage.setItem('app_addon_cart_IDs', jsonStr);

                var params = {
                    'cart': cart
                };

                showPreloader();

                fetch(stripeCartAddonURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(params)
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('load-cart').innerHTML = data.html;
                })
                .finally(() => {
                    //APP_loading();
                    showPreloader(false);
                });

                var cartValue = sessionStorage.getItem('app_addon_cart_IDs');
                var cartObj = JSON.parse(cartValue);
                if (cartValue && cartObj) {
                    cartObj.forEach(function(value) {
                        if (value) {
                            document.querySelector('.btn-addon-cart[data-addon="' + value + '"]').click();
                        }
                    });
                } else {
                    document.getElementById('load-cart').innerHTML = 'Novos recursos ainda não foram selecionados';
                }
            }
            */
            // END Addons Cart
        }
    });


    document.addEventListener('click', function(event) {
        if (event.target.matches('.input-step .btn-minus-plus')) {
            event.preventDefault();

            var button = event.target;
            var step = 1;
            var target = button.getAttribute('data-target');
            var action = button.getAttribute('data-action');

            var subscriptionButton = document.querySelector('button[data-price_id="' + target + '"]');
            var currentQuantity = parseInt(subscriptionButton.getAttribute('data-quantity')) || 0;

            var newQuantity = action === 'minus' ? Math.max(currentQuantity - step, 0) : currentQuantity + step;

            if (newQuantity === 0) {
                subscriptionButton.setAttribute('disabled', true);
                subscriptionButton.classList.add('btn-outline-theme');
                subscriptionButton.classList.remove('btn-theme');
            } else {
                subscriptionButton.removeAttribute('disabled');
                subscriptionButton.classList.remove('btn-outline-theme');
                subscriptionButton.classList.add('btn-theme');
            }

            //var text = newQuantity > step ? newQuantity + ' unidades' : '1 unidade';
            var text = newQuantity > 0 ? newQuantity : 'Quantidade';
            var quantityInput = document.querySelector('input.quantity-' + target);
            quantityInput.value = text;
            quantityInput.setAttribute('data-quantity', newQuantity);

            var unitAmount = parseInt(document.querySelector('.price-wrap-' + target).getAttribute('data-unit_amount')) || 0;
            var totalPrice = newQuantity * unitAmount;
            document.querySelector('.price-wrap-' + target).textContent = totalPrice.toLocaleString('pt-BR');

            subscriptionButton.setAttribute('data-quantity', newQuantity);
        }
    });


});
