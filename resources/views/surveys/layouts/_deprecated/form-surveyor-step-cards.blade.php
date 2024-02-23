@if ( $data )
    @php
        //appPrintR($data);
        //appPrintR($responsesData);
        $radioIndex = $badgeIndex = $countFinished = $countTopics = 0;
    @endphp
    @foreach ($data as $stepIndex => $step)
        @php
            if( isset($purpose) && $purpose == 'validForm' ){
                $stepId = isset($step['step_id']) ? intval($step['step_id']) : '';
                $termId = isset($step['term_id']) ? intval($step['term_id']) : '';
                $termName = $termId >= 100000 ? getWarehouseTermNameById($termId) : getTermNameById($termId);
                //$type =
                $originalPosition = isset($step['step_order']) ? intval($step['step_order']) : 0;
                $newPosition = $originalPosition;
                $topics = $step['topics'];
            }else{
                $stepId = '';
                $stepData = $step['stepData'] ?? null;
                $termName = $stepData['term_name'] ?? '';
                $termId = $stepData['term_id'] ?? '';
                //$type = $stepData['type'] ?? 'custom';
                $originalPosition = $stepData['original_position'] ?? $stepIndex;
                $newPosition = $stepData['new_position'] ?? $originalPosition;
                $topics = $step['topics'] ?? null;
            }
        @endphp

        @if( $topics )
            <div class="card joblist-card">
                <div class="card-body">
                    <h5 class="job-title text-theme text-uppercase">{{ $termName }}</h5>
                </div>
                @if ( $topics && is_array($topics))
                    @php
                        $bg = 'bg-opacity-75';

                        $topicBadgeIndex = 0;
                    @endphp
                    @foreach ($topics as $topicIndex => $topic)
                        @php
                            $bg = $bg == 'bg-opacity-75' ? 'bg-opacity-50' : 'bg-opacity-75';

                            $radioIndex++;

                            $countTopics++;

                            $topicBadgeIndex++;

                            if( isset($purpose) && $purpose == 'validForm' ){
                                $topicId = isset($topic['topic_id']) ? intval($topic['topic_id']) : '';
                                $question = $topic['question'] ?? '';

                                $originalPosition = 0;
                                $newPosition = 0;

                                $stepIdToFind = $stepId;
                                $topicIdToFind = $topicId;

                                $filteredItems = array_filter($responsesData, function ($item) use ($stepIdToFind, $topicIdToFind) {
                                    return $item['step_id'] == $stepIdToFind && $item['topic_id'] == $topicIdToFind;
                                });

                                // Reset array keys
                                $filteredItems = array_values($filteredItems);

                                $responseId = $filteredItems[0]['id'] ?? '';

                                $attachmentIds =  $filteredItems[0]['attachments_survey'] ?? '';
                                $attachmentIds = $attachmentIds ? json_decode($attachmentIds, true) : '';

                                if($responseId){
                                    $countFinished++;
                                }
                                $commentSurvey = $filteredItems[0]['comment_survey'] ?? '';
                                $complianceSurvey = $filteredItems[0]['compliance_survey'] ?? '';
                            }else{
                                $topicId = '';
                                $question = $topic['question'] ?? '';
                                $originalPosition = $topic['original_position'] ?? $topicIndex;
                                $newPosition = $topic['new_position'] ?? $originalPosition;

                                $responseId = '';
                                $commentSurvey = '';
                                $complianceSurvey = '';
                            }
                        @endphp
                        <div class="card-footer border-top-dashed {{ $bg }}">
                            <form class="responses-data-container" autocomplete="off">
                                <input type="hidden" name="topic_id" value="{{$topicId ?? ''}}">
                                <input type="hidden" name="response_id" value="{{$responseId ?? ''}}">

                                <div class="row">
                                    <div class="col">
                                        <h5 class="mb-0">
                                            <span class="badge bg-light-subtle text-body badge-border text-theme align-bottom me-1">{{ $topicBadgeIndex }}</span>
                                            {{ $question ? ucfirst($question) : 'NI' }}
                                        </h5>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fs-5 ri-time-line text-warning-emphasis {{ !$complianceSurvey ? '' : 'd-none'}}"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Status: Pendente"></i>

                                        <i class="fs-5 ri-check-double-fill text-theme {{ $complianceSurvey ? '' : 'd-none'}}"
                                        data-bs-toggle="tooltip"
                                        data-bs-trigger="hover"
                                        data-bs-placement="top" title="Status: Concluído"></i>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-sm-12 col-md">
                                        <div class="input-group">
                                            @if( $surveyorStatus != 'auditing' && $surveyorStatus != 'losted' )
                                                <label for="input-attachment-{{$radioIndex}}" class="btn btn-outline-light waves-effect waves-light ps-1 pe-1 mb-0 d-flex align-content-center flex-wrap" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Anexar Fotografia" data-step-id="{{$stepId}}" data-topic-id="{{$topicId}}">
                                                    <i class="ri-image-add-fill text-body fs-5 m-2"></i>
                                                </label>
                                                <input type="file" id="input-attachment-{{$radioIndex}}" class="input-upload-photo d-none" accept="image/jpeg" {{ isset($purpose) && $purpose == 'validForm' ? '' : 'disabled' }}>
                                            @endif

                                            <textarea tabindex="-1" class="form-control border-light" maxlength="1000" {{ $purpose == 'validForm' ? 'rows="3"' : 'readonly' }} name="comment_survey" placeholder="Observações..." {{$surveyorStatus == 'auditing' || $surveyorStatus == 'losted' ? 'disabled readonly' : ''}} style="max-height: 70px;">{{$commentSurvey ?? ''}}</textarea>
                                        </div>

                                        @if( $surveyorStatus != 'auditing' && $surveyorStatus != 'losted' )
                                            <button tabindex="-1"
                                                type="button"
                                                @if ( isset($purpose) && $purpose == 'validForm' )
                                                    data-assignment-id="{{$assignmentId}}"
                                                    data-step-id="{{$stepId}}"
                                                    data-topic-id="{{$topicId}}"
                                                @endif
                                                data-bs-toggle="tooltip"
                                                data-bs-trigger="hover"
                                                data-bs-placement="left"
                                                title="{{ $responseId ? 'Atualizar' : 'Salvar'}}"
                                                class="btn btn-outline-light waves-effect waves-light ps-1 pe-1 {{ isset($purpose) && $purpose == 'validForm' ? 'btn-response-update' : '' }} d-none">
                                                    <i class="{{ $responseId ? 'ri-refresh-line' : 'ri-save-3-line'}} text-theme fs-3 m-2"></i>
                                            </button>
                                        @endif

                                        <div class="gallery-wrapper mt-2 row">
                                            @if ( !empty($attachmentIds) && is_array($attachmentIds) )
                                                @foreach ($attachmentIds as $attachmentId)
                                                    @php
                                                        $attachmentUrl = $dateAttachment = '';
                                                        if (!empty($attachmentId)) {
                                                            $attachmentUrl = App\Models\Attachments::getAttachmentPathById($attachmentId);

                                                            $dateAttachment = App\Models\Attachments::getAttachmentDateById($attachmentId);
                                                        }
                                                    @endphp
                                                    @if ($attachmentUrl)
                                                        <div id="element-attachment-{{$attachmentId}}" class="element-item col-auto">
                                                            <div class="gallery-box card p-0 mb-0 mt-1">
                                                                <div class="gallery-container">
                                                                    <a href="{{ $attachmentUrl }}" class="image-popup" title="Imagem capturada em {{$dateAttachment}}hs" data-gallery="gallery-{{$radioIndex}}">
                                                                        <img class="rounded gallery-img" alt="image" height="70" src="{{ $attachmentUrl }}">

                                                                        <div class="gallery-overlay">
                                                                            <h5 class="overlay-caption fs-10">{{$dateAttachment}}</h5>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            @if( $surveyorStatus != 'auditing' && $surveyorStatus != 'losted' )
                                                                <div class="position-absolute translate-middle mt-n2 ms-2">
                                                                    <div class="avatar-xs">
                                                                        <button type="button" class="avatar-title bg-light border-0 rounded-circle text-danger cursor-pointer btn-delete-photo" data-attachment-id="{{$attachmentId}}" title="Deletar Arquivo">
                                                                            <i class="ri-delete-bin-2-line"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            <input type="hidden" name="attachment_id[]" value="{{$attachmentId}}">
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-auto">
                                        <div class="row">
                                            <div class="col col-md-12">
                                                <div class="form-check form-switch form-switch-sm form-switch-theme mt-2" title="Em conformidade">
                                                    <input tabindex="-1" class="form-check-input" type="radio" name="compliance_survey" role="switch" id="YesSwitchCheck{{ $topicIndex.$radioIndex }}" {{$surveyorStatus == 'auditing' || $surveyorStatus == 'losted' ? 'disabled' : ''}} value="yes" {{$complianceSurvey && $complianceSurvey == 'yes' ? 'checked' : ''}}>
                                                    <label class="form-check-label" for="YesSwitchCheck{{ $topicIndex.$radioIndex }}">Conforme</label>
                                                </div>
                                            </div>
                                            <div class="col col-md-12">
                                                <div class="form-check form-switch form-switch-sm form-switch-danger mt-2" title="Não conforme">
                                                    <input tabindex="-1" class="form-check-input" type="radio" name="compliance_survey" role="switch" id="NoSwitchCheck{{ $topicIndex.$radioIndex }}" {{$surveyorStatus == 'auditing' || $surveyorStatus == 'losted' ? 'disabled' : ''}} value="no" {{$complianceSurvey && $complianceSurvey == 'no' ? 'checked' : ''}}>
                                                    <label class="form-check-label" for="NoSwitchCheck{{ $topicIndex.$radioIndex }}">Não Conforme</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif
    @endforeach

    @if ( isset($purpose) && $purpose == 'validForm' && $surveyorStatus != 'auditing' && $surveyorStatus != 'losted' )
        <button tabindex="-1"
            type="button"
            class="btn btn-lg btn-theme waves-effect w-100 {{ $countFinished < $countTopics ? 'd-none' : '' }}"
            id="btn-response-finalize"
            data-assignment-id="{{$assignmentId}}"
            title="Finalizar e Disponibilizar para eventual Auditoria">
            <i class="ri-send-plane-fill align-bottom m-2"></i> Finalizar
        </button>
    @endif
@endif
