@if ( $stepsWithTopics )
    @php
        //appPrintR($responsesData);
        $radioIndex = $badgeIndex = 0;
    @endphp
    @foreach ($stepsWithTopics as $stepIndex => $step)
        @php
            $stepId = isset($step['step_id']) ? intval($step['step_id']) : '';
            $termId = isset($step['term_id']) ? intval($step['term_id']) : '';
            // use the term_id to get term name. If term_id is less than 9000, find the getDepartmentNameById(term_id)
            $termName = $termId >= 100000 ? getWarehouseTermNameById($termId) : getTermNameById($termId);
            //$type =
            $originalPosition = isset($step['step_order']) ? intval($step['step_order']) : 0;
            $newPosition = $originalPosition;
            $topics = $step['topics'];
        @endphp

        @if( $topics )
            <div class="card">
                <div class="card-header text-theme text-uppercase fs-5">
                    {{ $termName }}
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

                            $topicBadgeIndex++;

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

                            $surveyAttachmentIds =  $filteredItems[0]['attachments_survey'] ?? '';
                            $surveyAttachmentIds = $surveyAttachmentIds ? json_decode($surveyAttachmentIds, true) : '';

                            $auditAttachmentIds =  $filteredItems[0]['attachments_audit'] ?? '';
                            $auditAttachmentIds = $auditAttachmentIds ? json_decode($auditAttachmentIds, true) : '';

                            $commentSurvey = $filteredItems[0]['comment_survey'] ?? '';
                            $complianceSurvey = $filteredItems[0]['compliance_survey'] ?? '';

                            $commentAudit = $filteredItems[0]['comment_audit'] ?? '';
                            $complianceAudit = $filteredItems[0]['compliance_audit'] ?? '';
                        @endphp
                <div class="card-body {{ $bg }}">
                    <div class="card mb-0 bg-body">
                        <div class="card-body">
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
                                        <i class="fs-5 ri-time-line text-warning-emphasis {{ !$complianceAudit ? '' : 'd-none'}}"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Status: Pendente"></i>

                                        <i class="fs-5 ri-check-double-fill text-theme {{ $complianceAudit ? '' : 'd-none'}}"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Status: Concluído"></i>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    @if( $auditorStatus != 'completed' && $auditorStatus != 'losted' )
                                        <button tabindex="-1"
                                            type="button"
                                            data-assignment-id="{{$assignmentId}}"
                                            data-step-id="{{$stepId}}"
                                            data-topic-id="{{$topicId}}"
                                            data-bs-toggle="tooltip"
                                            data-bs-trigger="hover"
                                            data-bs-placement="left"
                                            title="{{ $responseId ? 'Atualizar' : 'Salvar'}}"
                                            class="btn btn-outline-light waves-effect waves-light ps-1 pe-1 btn-response-update d-none">
                                                <i class="{{ $responseId ? 'ri-refresh-line' : 'ri-save-3-line'}} text-theme fs-3 m-2"></i>
                                        </button>
                                    @endif
                                    
                                    <div class="col-md-4 pb-3">
                                        <div class="card border border-light rounded rounded-1 h-100">
                                            <div class="card-header bg-dark">
                                                <h6 class="card-title mb-0">
                                                    Checklist: {!! $complianceSurvey && $complianceSurvey == 'yes' ? '<span class="text-theme">Conforme</span>' : '<span class="text-danger">Não Conforme</span>' !!}
                                                </h6>
                                            </div>
                                            <div class="card-body bg-dark">
                                                {!! $commentSurvey ? '<p>'.nl2br($commentSurvey).'</p>' : '' !!}

                                                <div class="mt-2 row">
                                                    @if ( !empty($surveyAttachmentIds) && is_array($surveyAttachmentIds) )
                                                        @foreach ($surveyAttachmentIds as $attachmentId)
                                                            @php
                                                                $attachmentUrl = $dateAttachment = '';
                                                                if (!empty($attachmentId)) {
                                                                    $attachmentUrl = App\Models\Attachments::getAttachmentPathById($attachmentId);

                                                                    $dateAttachment = App\Models\Attachments::getAttachmentDateById($attachmentId);
                                                                }
                                                            @endphp
                                                            @if ($attachmentUrl)
                                                                <div class="element-item col-auto">
                                                                    <div class="gallery-box card p-0 mb-0 mt-1">
                                                                        <div class="gallery-container">
                                                                            <a href="{{ $attachmentUrl }}" class="image-popup" title="Imagem capturada em {{$dateAttachment}}hs" data-gallery="gallery-{{$responseId}}">
                                                                                <img class="rounded gallery-img" alt="image" height="70" src="{{ $attachmentUrl }}" loading="lazy">

                                                                                <div class="gallery-overlay">
                                                                                    <h5 class="overlay-caption fs-10">{{$dateAttachment}}</h5>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8 pb-3">
                                        <div class="card border border-light rounded rounded-1 h-100">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    Auditoria
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-12 col-md">
                                                        <div class="input-group">
                                                            @if( $auditorStatus != 'completed' && $auditorStatus != 'losted' )
                                                                <label for="input-attachment-{{$radioIndex}}" class="btn btn-outline-light waves-effect waves-light ps-1 pe-1 mb-0 d-flex align-content-center flex-wrap btn-add-photo" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Anexar Fotografia" data-step-id="{{$stepId}}" data-topic-id="{{$topicId}}">
                                                                    <i class="ri-image-add-fill text-body fs-5 m-2"></i>
                                                                </label>
                                                                <input type="file" id="input-attachment-{{$radioIndex}}" class="input-upload-photo d-none" accept="image/jpeg">
                                                            @endif

                                                            <textarea tabindex="-1" class="form-control border-light" maxlength="1000" rows="3" name="comment_audit" placeholder="Observações..." {{$auditorStatus == 'auditing' || $auditorStatus == 'losted' ? 'disabled readonly' : ''}} style="height: 70px;">{{$commentAudit ?? ''}}</textarea>
                                                        </div>



                                                        <div class="gallery-wrapper mt-2 row">
                                                            @if ( !empty($auditAttachmentIds) && is_array($auditAttachmentIds) )
                                                                @foreach ($auditAttachmentIds as $attachmentId)
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
                                                                                    <a href="{{ $attachmentUrl }}" class="image-popup" title="Imagem capturada em {{$dateAttachment}}hs" data-gallery="gallery-{{$responseId}}">
                                                                                        <img class="rounded gallery-img" alt="image" height="70" src="{{ $attachmentUrl }}">

                                                                                        <div class="gallery-overlay">
                                                                                            <h5 class="overlay-caption fs-10">{{$dateAttachment}}</h5>
                                                                                        </div>
                                                                                    </a>
                                                                                </div>
                                                                            </div>

                                                                            @if( $auditorStatus != 'completed' && $auditorStatus != 'losted' )
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
                                                                <div class="form-check form-switch form-switch-sm form-switch-theme mt-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Checklist efetuada de forma correta">
                                                                    <input tabindex="-1" class="form-check-input" type="radio" name="compliance_audit" role="switch" id="YesSwitchCheck{{ $topicIndex.$radioIndex }}" {{$auditorStatus == 'completed' || $auditorStatus == 'losted' ? 'disabled' : ''}} value="yes" {{$complianceAudit && $complianceAudit == 'yes' ? 'checked' : ''}}>
                                                                    <label class="form-check-label" for="YesSwitchCheck{{ $topicIndex.$radioIndex }}">De Acordo</label>
                                                                </div>
                                                            </div>
                                                            <div class="col col-md-12">
                                                                <div class="form-check form-switch form-switch-sm form-switch-danger mt-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Checklist efetuada de forma incorreta">
                                                                    <input tabindex="-1" class="form-check-input" type="radio" name="compliance_audit" role="switch" id="NoSwitchCheck{{ $topicIndex.$radioIndex }}" {{$auditorStatus == 'completed' || $auditorStatus == 'losted' ? 'disabled' : ''}} value="no" {{$complianceAudit && $complianceAudit == 'no' ? 'checked' : ''}}>
                                                                    <label class="form-check-label" for="NoSwitchCheck{{ $topicIndex.$radioIndex }}">Não Concordo</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                    @endforeach
                @endif
            </div>
        @endif
    @endforeach

    @if ( $auditorStatus != 'completed' && $auditorStatus != 'losted' )
        <button tabindex="-1"
            type="button"
            class="btn btn-lg btn-theme waves-effect w-100 {{ $countResponses < $countTopics ? 'd-none' : '' }}"
            id="btn-response-finalize"
            data-assignment-id="{{$assignmentId}}"
            title="Finalizar Auditoria">
            <i class="ri-save-3-line align-bottom m-2"></i> Finalizar Auditoria
        </button>
    @endif
@endif
