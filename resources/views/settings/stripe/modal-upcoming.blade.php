@php
    global $stripe, $customer_ID, $check_customer_ID, $next_payment_attempt, $sum_amount_to_modal;

    $line_items = [];

    if ($check_customer_ID) {
        try {
            // ... (Your existing PHP code for Stripe API calls)
        } catch (Exception $e) {
            if (env('APP_DEBUG')) {
                echo $e->getError()->message;
            }
        }
    }
@endphp

@if ($check_customer_ID)
    <div class="modal fade" id="modal-upcoming" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        data-focus="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header p-3 bg-soft-info">
                    <h5 class="modal-title">Próxima Fatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1"></button>
                </div>
                <div class="modal-body">
                    @if (count($line_items) > 0)
                        <p class="mb-4">Os valores poderão mudar se a assinatura for alterada adicionando/removendo
                            Empresas ou até mesmo cancelada</p>
                        <table class="table table-striped table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase">Assinatura</th>
                                    <th class="text-uppercase" style="max-width: 300px;">Descrição</th>
                                    <th class="text-uppercase">Período</th>
                                    <th class="text-uppercase text-center">Status</th>
                                    <th class="text-uppercase" width="110">Unidade</th>
                                    <th class="text-uppercase text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($line_items as $key => $value)
                                    @php
                                        $name = $value['name'] ?? '';
                                        $description = $value['description'] ?? '';
                                        $unit_amount = $value['unit_amount'] ?? '';
                                        $quantity = $value['quantity'] ?? '';
                                        $period_start = $value['period']['start'] ?? '';
                                        $period_end = $value['period']['end'] ?? '';
                                        $total = $value['total'] ?? '';
                                        $sum_amount_to_modal += $total;
                                        $proration = $value['proration'] ?? '';
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            <strong>{{ $name }}</strong>
                                        </td>
                                        <td>{{ $description }}</td>
                                        <td class="text-nowrap">
                                            {{ $period_start }} <span class="text-theme">⇆</span> {{ $period_end }}
                                        </td>
                                        <td class="text-center">
                                            {{-- Status logic here --}}
                                        </td>
                                        <td>
                                            {{ !empty($unit_amount) ? APP_money_format($unit_amount / 100, 2) : '' }}
                                            {!! !empty($unit_amount) && intval($quantity) > 1
                                                ? '<sup><span class="text-danger"> x </span>' . $quantity . '</sup>'
                                                : '' !!}
                                        </td>
                                        <td class="text-end">
                                            {{ APP_money_format($total / 100, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="fw-bold text-end">
                                        {{ APP_money_format($sum_amount_to_modal / 100, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <p>A Stripe não retornou dados</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
