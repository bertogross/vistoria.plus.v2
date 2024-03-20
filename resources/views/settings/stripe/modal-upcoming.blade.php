@if ($customerId && $upcoming && is_object($upcoming))
    <div class="modal fade" id="stripeUpcomingModal" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        data-focus="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header p-3 bg-soft-info">
                    <h5 class="modal-title">Próxima Fatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1" onclick="location.reload(true);"></button>
                </div>
                <div class="modal-body">
                    @if (count($upcoming->lines->data) > 0)
                        <p class="mb-3">Os valores poderão mudar se a assinatura for alterada adicionando/removendo usuarios ou até mesmo se for cancelada</p>
                        <table class="table table-striped table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase">Item</th>
                                    <th class="text-uppercase" style="max-width: 300px;">Descrição</th>
                                    <th class="text-uppercase">Período</th>
                                    <th class="text-uppercase" width="110">Quantidade</th>
                                    <th class="text-uppercase text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sumAmountToModal = 0;
                                @endphp
                                @foreach ($upcoming->lines->data as $key => $value)
                                    @php
                                        $subscriptionItem = $value['subscription_item'] ?? '';
                                        $name = $value['name'] ?? '';
                                        $description = $value['description'] ?? '';
                                        $quantity = $value['quantity'] ?? '';
                                        $periodStart = date('d/m/Y', $value['period']['start']) ?? '';
                                        $periodEnd = date('d/m/Y',$value['period']['end']) ?? '';
                                        $amount = $value['amount'] ?? 0;
                                        $sumAmountToModal += $amount;
                                        $proration = $value['proration'] ?? '';
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            <code title="Subscription Item ID">{{ $subscriptionItem }}</code>
                                        </td>
                                        <td>{{ $description }}</td>
                                        <td class="text-nowrap">
                                            {{ $periodStart }} <span class="text-theme">⇆</span> {{ $periodEnd }}
                                        </td>
                                        <td class="text-center">
                                            {{  $quantity }}
                                        </td>
                                        <td class="text-end {{$amount < 0 ? 'text-danger' : ''}}">
                                            {{ brazilianRealFormat($amount / 100, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="fw-bold text-end">
                                        {{ brazilianRealFormat($sumAmountToModal / 100, 2) }}</td>
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
