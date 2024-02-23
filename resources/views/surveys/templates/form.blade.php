@foreach ($data as $stepIndex => $step)
    @php
        $stepData = $step['stepData'] ?? null;
        $termName = $stepData['term_name'] ?? 'NI';
        $termId = $stepData['term_id'] ?? 0;
        $type = $stepData['type'] ?? 'custom';
        $originalPosition = $stepData['original_position'] ?? $stepIndex;
        $newPosition = $stepData['new_position'] ?? $originalPosition;

        $topics = $step['topics'] ?? null;
        $topics = $topics && is_array($topics) ? array_filter($topics) : $topics;
    @endphp
    <div id="{{ $termId }}" class="accordion-item block-item mt-3 mb-0 border-light border-1 rounded rounded-2 p-0 bg-body">
        <div class="input-group">
            <input type="text" class="form-control text-theme text-uppercase" name="steps[{{$stepIndex}}]['stepData']['term_name']" value="{{ $termName }}" placeholder="Setor/Etapa" maxlength="100" readonly required tabindex="-1">

            <div class="btn btn-ghost-dark btn-icon rounded-pill cursor-n-resize handle-block ri-arrow-up-down-line text-body" title="Reordenar bloco de Termo"></div>

            <button type="button" class="btn btn-ghost-dark btn-icon rounded-pill btn-accordion-toggle ri-arrow-up-s-line" tabindex="-1"></button>
        </div>

        <input type="hidden" name="steps[{{$stepIndex}}]['stepData']['term_id']" value="{{ $termId }}">
        <input type="hidden" name="steps[{{$stepIndex}}]['stepData']['type']" value="{{ $type }}">
        <input type="hidden" name="steps[{{$stepIndex}}]['stepData']['original_position']" value="{{ $originalPosition }}">
        <input type="hidden" name="steps[{{$stepIndex}}]['stepData']['new_position']" value="{{ $newPosition }}">

        <div class="accordion-collapse collapse show">
            <div class="nested-sortable-topic mt-0 p-1">@if ( isset($topics) && is_array($topics) && count($topics) > 0 )
                @foreach ($topics as $topicIndex => $topic)
                    @php
                        $question = $topic['question'] ?? '';
                        $originalPosition = $topic['original_position'] ?? $topicIndex;
                        $newPosition = $topic['new_position'] ?? $originalPosition;
                    @endphp
                    <div id="{{ $termId . $topicIndex }}" class="step-topic mt-1 mb-1">
                        <div class="row">
                            <div class="col-auto">
                                <button type="button" class="btn btn-ghost-danger btn-icon rounded-pill btn-remove-topic ri-delete-bin-3-line" data-target="{{ $termId . $topicIndex }}" title="Remover Tópico" tabindex="-1"></button>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control input-topic" title="Exemplo: Organização do setor?... Abastecimento de produtos/insumos está em dia?" placeholder="Tópico..." name="steps[{{$stepIndex}}]['topics']['question']" value="{{$question}}" maxlength="150" required>
                            </div>
                            <div class="col-auto">
                                <div class="btn btn-ghost-dark btn-icon rounded-pill cursor-n-resize handle-topic ri-arrow-up-down-line" title="Reordenar Tópico"></div>
                            </div>
                            <input type="hidden" name="steps[{{$stepIndex}}]['topics']['original_position']" tabindex="-1" value="{{ $originalPosition }}">
                            <input type="hidden" name="steps[{{$stepIndex}}]['topics']['new_position']" tabindex="-1" value="{{ $newPosition }}">
                        </div>
                    </div>
                @endforeach
            @endif</div>

            <div class="clearfix">
                <button type="button" class="btn btn-ghost-dark btn-icon btn-add-topic rounded-pill float-end cursor-copy text-theme ri-menu-add-line" data-block-step-id="{{ $termId }}" data-block-index="{{ $stepIndex }}" title="Adicionar Tópico"></button>

                @if ( $type == 'custom' )
                @endif
                <button type="button" class="btn btn-ghost-danger btn-icon rounded-pill btn-remove-block float-end ri-delete-bin-7-fill" data-target="{{ $termId }}" title="Remover Bloco" tabindex="-1"></button>
            </div>

        </div>
    </div>
@endforeach
