<h4 class="mb-0">Faturamento</h4>
<p>Acesse faturas, atualize pagamentos e monitore seu faturamento facilmente</p>

@if($customerId)
    @if(isset($invoices) && is_object($invoices) || is_object($upcoming))
        <div class="table-responsive">

            <table class="table table-hover table-bordered table-striped table-compact">
                <thead class="table-light">
                    <th class="d-none text-uppercase">ID</th>
                    <th class="text-uppercase">Decrição</th>
                    <th class="text-uppercase">Vencimento</th>
                    <th class="text-center text-uppercase">Status</th>
                    <th class="text-uppercase">Período de Atividade</th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody>
                    @if (!empty($upcoming) && isset($upcoming) && is_object($upcoming) && count($upcoming->lines->data) > 0)

                        @php
                            $next_payment_attempt = !empty($upcoming['next_payment_attempt']) ? date('d/m/Y', $upcoming['next_payment_attempt']) : '';

                            $period_start = !empty($upcoming['period_start']) ? date('d/m/Y', $upcoming['period_start']) : '';
                            $period_end = !empty($upcoming['period_end']) ? date('d/m/Y', $upcoming['period_end']) : '';

                            $total = '';
                            $total = $upcoming['total'] > 0 ? ($upcoming['total']/100) : 0;
                            $total = $total > 0 ? brazilianRealFormat($total, 2) : '';

                            $status = !empty($upcoming['status']) ? $upcoming['status'] : '';

                            $btnAction = '<button class="btn btn-sm btn-outline-info text-uppercase btn-subscription-upcoming" type="button" title="Visualizar Detalhes"><i class="ri-error-warning-line me-1 float-start"></i>Detalhes</button>';
                        @endphp

                        @if( !empty($status) && $status == 'draft' )
                            <tr data-listing="upcoming">
                                <td class="d-none align-middle" data-label="ID">
                                    #{{isset($invoice['receipt_number']) ? $invoice['receipt_number'] : ''}}
                                </td>
                                <td class="align-middle" data-label="Descrição">
                                    {{-- $upcoming->lines->data[0]->description ?? '-' --}}
                                    -
                                </td>
                                <td class="align-middle" data-label="Faturamento">
                                    {{$next_payment_attempt}}
                                </td>
                                <td class="align-middle text-center" data-label="Status">
                                    <span class="badge text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-warning text-warning" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Faturamento agendado" data-stripe-case="paid"><i class="ri-checkbox-blank-circle-fill me-1"></i>Agendado</span>
                                </td>
                                <td class="align-middle" data-label="Período">
                                    {{--
                                    {{$period_start}} <span class="text-theme">&#8646;</span> {{$period_end}}
                                    --}}
                                    -
                                </td>
                                <td class="align-middle text-end" data-label="Total">
                                    {{!empty($total) ? $total : '-'}}
                                </td>
                                <td class="align-middle text-end" data-label="Actions">
                                    {!! $btnAction !!}
                                </td>
                            </tr>
                        @endif
                    @endif

                    @foreach($invoices->autoPagingIterator() as $invoice)
                        @php
                            $period_start = !empty($invoice['lines']['data'][0]['period']['end']) ? date('d/m/Y', $invoice['lines']['data'][0]['period']['start']) : '';
                            $period_end = !empty($invoice['lines']['data'][0]['period']['end']) ? date('d/m/Y', $invoice['lines']['data'][0]['period']['end']) : '';

                            $invoice_subscription = !empty($invoice['lines']['data'][0]['subscription']) ? $invoice['lines']['data'][0]['subscription'] : '';

                            $post_payment_credit_notes_amount = $invoice['post_payment_credit_notes_amount'] > 0 ? $invoice['post_payment_credit_notes_amount'] : '';

                            $total = '';
                            $total = $invoice['total'] > 0 ? ($invoice['total']/100) : 0;
                            $total = $total > 0 ? brazilianRealFormat($total, 2) : '';

                            $adjusted_invoice_total = $post_payment_credit_notes_amount ? brazilianRealFormat(($invoice['total'] - $post_payment_credit_notes_amount)/100, 2) : '';

                            $status_label = '-';
                            $btnAction = '-';
                            $status = !empty($invoice['status']) ? $invoice['status'] : '';

                            /**
                             * Check if refunded
                             * https://stripe.com/docs/api/payment_intents/retrieve
                             */
                            $payment_intent = $invoice->payment_intent;
                            if( !empty($payment_intent) ){
                                try {
                                    $paymentIntents = $stripe->paymentIntents->retrieve(
                                        $payment_intent
                                    );
                                    $refunded = isset($paymentIntents->charges->data[0]->refunded) ? $paymentIntents->charges->data[0]->refunded : false;
                                    $status = $refunded == true ? 'refunded' : $status;
                                }catch (\Exception $e){
                                    //echo $e->getError()->message;
                                }
                            }
                            $linethrough = $status == 'refunded' ? 'text-decoration-line-through fw-normal text-primary' : '';

                            //https://stripe.com/docs/invoicing/overview
                            switch ($status) {
                                case 'draft':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-info text-info" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Período contábil ainda não foi encerrado" data-stripe-case="'.$status.'">Rascunho</span>';
                                    break;
                                case 'refunded':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-primary text-primary" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Valor Reembolsado" data-stripe-case="'.$status.'"><i class="ri-checkbox-blank-circle-fill me-1"></i>Reembolsado</span>';

                                    $btnAction = !empty($total) ? '<a class="btn btn-sm btn-outline-theme text-uppercase" href="'.$invoice['hosted_invoice_url'].'" target="_blank" title="Visualizar Recibo"><i class="ri-file-paper-line float-start me-1"></i>Recibo</a>' : '-';
                                    break;
                                case 'open':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-warning text-warning" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Aguardando pagamento" data-stripe-case="'.$status.'"><i class="ri-checkbox-blank-circle-fill me-1"></i>Processando</span>';
                                    break;
                                case 'past_due':
                                case 'unpaid':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-warning text-warning" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Não foi possível debitar o valor. Por favor, atualize o método de pagamento." data-stripe-case="'.$status.'"><i class="ri-checkbox-blank-circle-fill align-middle blink"></i>Requer Atenção</span>';

                                    $btnAction = !empty($total) ? '<a class="btn btn-sm btn-outline-warning btn-invoice-regularize" href="'.$invoice['hosted_invoice_url'].'" title="Pagar">Pagar</a>' : '-';
                                    break;
                                case 'paid':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-theme text-theme" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Esta fatura foi paga" data-stripe-case="'.$status.'"><i class="ri-checkbox-blank-circle-fill me-1"></i>Pago</span>';

                                    $btnAction = !empty($total) ? '<a class="btn btn-sm btn-outline-theme text-uppercase" href="'.$invoice['hosted_invoice_url'].'" target="_blank" title="Visualizar Recibo"><i class="ri-file-paper-line float-start me-1"></i>Recibo</a>' : '-';
                                    break;
                                case 'void':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-danger text-danger" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Este erro será corrigido" data-stripe-case="'.$status.'"><i class="ri-checkbox-blank-circle-fill me-1"></i>Erro</span>';
                                    break;
                                case 'uncollectible':
                                    $status_label = '<span class="badge bg-transparent text-uppercase fs-11px p-1 d-inline-flex align-items-center border small border-danger text-danger btn-invoice-uncollectible" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="O débito no cartão não foi efetuado pois a assinatura está/estava suspensa" data-stripe-case="'.$status.'"><i class="ri-checkbox-blank-circle-fill me-1"></i>Não Debitado</span>';
                                    break;
                                default:
                                    $status_label = '-';
                                    $btnAction = '-';
                            }
                        @endphp

                        @if(!empty($status) && $status != 'draft' && !empty($total))
                            <tr data-listing="invoive">
                                <td class="d-none align-middle" data-label="ID">
                                    #{{ $invoice['number'] ?? '' }}
                                </td>
                                <td class="align-middle" data-label="Descrição">
                                    {{ $invoice['lines']['data'][0]['description'] }}
                                    <br><code title="Invoice Subscription ID">{{ $invoice_subscription }}</code>
                                </td>
                                <td class="align-middle" data-label="Faturamento">
                                    {{ !empty($total) && !empty($invoice['status_transitions']['paid_at']) ? date('d/m/Y', $invoice['status_transitions']['paid_at']) : '-' }}
                                </td>
                                <td class="align-middle text-center" data-label="Status">
                                    {!! !empty($total) ? $status_label : '-' !!}
                                </td>
                                <td class="align-middle" data-label="Período">
                                    {{ $period_start }} <span class="text-theme">&#8646;</span> {{ $period_end }}
                                </td>
                                <td class="align-middle text-end" data-label="Total">
                                    @if($adjusted_invoice_total)
                                        {!! !empty($adjusted_invoice_total) ? '<strong>'.$adjusted_invoice_total.'</strong>' : '-' !!}
                                        {!! !empty($total) ? '<br><strong class="text-decoration-line-through small text-muted">'.$total.'</strong>' : '-' !!}
                                    @else
                                        {!! !empty($total) ? '<strong class="'.$linethrough.'">'.$total.'</strong>' : '-' !!}
                                    @endif
                                </td>
                                <td class="align-middle text-end" data-label="Actions">
                                    {!! $btnAction !!}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        Não há dados Stripe
    @endif
@else
    Não foi possível carregar os dados de faturamento na Stripe.<br>Certifique-se se já é assinante do {{appName()}}.

    <br><br>

    <a href="{{route('settingsAccountShowURL')}}/?tab=subscription" class="btn btn-theme" title="Clique para visualizar o plano de assinatura do {{appName()}}">Assinar o {{appName()}}</a>
@endif
