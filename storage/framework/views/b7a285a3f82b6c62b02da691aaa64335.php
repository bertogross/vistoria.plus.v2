<?php if( $stepsWithTopics ): ?>
    <?php
        //appPrintR($responsesData);
        $radioIndex = $badgeIndex = 0;
    ?>
    <?php $__currentLoopData = $stepsWithTopics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stepIndex => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $stepId = isset($step['step_id']) ? intval($step['step_id']) : '';
            $termId = isset($step['term_id']) ? intval($step['term_id']) : '';
            $termName = $termId >= 100000 ? getWarehouseTermNameById($termId) : getTermNameById($termId);
            $originalPosition = isset($step['step_order']) ? intval($step['step_order']) : 0;
            $newPosition = $originalPosition;
            $topics = $step['topics'];
        ?>

        <?php if( $topics ): ?>
            <div class="card">
                <div class="card-header text-theme text-uppercase fs-5">
                    <?php echo e($termName); ?>

                </div>
                <?php if( $topics && is_array($topics)): ?>
                    <?php
                        $bg = 'bg-opacity-75';

                        $topicBadgeIndex = 0;
                    ?>
                    <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topicIndex => $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
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

                            $complianceSurvey = $filteredItems[0]['compliance_survey'] ?? '';
                            $commentSurvey = $filteredItems[0]['comment_survey'] ?? '';

                            $complianceAudit = $filteredItems[0]['compliance_audit'] ?? '';
                            $commentAudit = $filteredItems[0]['comment_audit'] ?? '';
                        ?>
                <div class="card-body <?php echo e($bg); ?>">
                    <div class="card mb-0 bg-body">
                        <div class="card-body">
                            <form class="responses-data-container" autocomplete="off">
                                <input type="hidden" name="topic_id" value="<?php echo e($topicId ?? ''); ?>">
                                <input type="hidden" name="response_id" value="<?php echo e($responseId ?? ''); ?>">

                                <div class="row">
                                    <div class="col">
                                        <h5 class="mb-0">
                                            <span class="badge bg-light-subtle text-body badge-border text-theme align-bottom me-1"><?php echo e($topicBadgeIndex); ?></span>
                                            <?php echo e($question ? ucfirst($question) : 'NI'); ?>

                                        </h5>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fs-5 ri-time-line text-warning-emphasis <?php echo e(!$complianceAudit ? '' : 'd-none'); ?>"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Status: Pendente"></i>

                                        <i class="fs-5 ri-check-double-fill text-theme <?php echo e($complianceAudit ? '' : 'd-none'); ?>"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Status: Concluído"></i>
                                    </div>
                                </div>

                                <div class="card border border-dashed shadow-none bg-<?php echo e($complianceSurvey == 'yes' ? 'success-subtle' : 'danger-subtle'); ?> mt-2">
                                    <div class="card-body">
                                        <h6>Avaliação da Vistoria:</h6>
                                        <div class="row">
                                            <div class="col-auto">
                                                <?php echo $complianceSurvey == 'yes' ? '<i class="ri-thumb-up-line text-success fs-1" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Conforme"></i>' : '<i class="ri-thumb-down-line text-danger fs-1" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Não Conforme"></i>'; ?>

                                            </div>
                                            <div class="col">
                                                <?php echo e(nl2br($commentSurvey)); ?>

                                            </div>
                                            <div class="col-12">
                                                <?php if( !empty($surveyAttachmentIds) && is_array($surveyAttachmentIds) ): ?>
                                                    <?php $__currentLoopData = $surveyAttachmentIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachmentId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $attachmentUrl = $dateAttachment = '';
                                                            if (!empty($attachmentId)) {
                                                                $attachmentUrl = App\Models\Attachments::getAttachmentPathById($attachmentId);

                                                                $dateAttachment = App\Models\Attachments::getAttachmentDateById($attachmentId);
                                                            }
                                                        ?>
                                                        <?php if($attachmentUrl): ?>
                                                            <div id="element-attachment-<?php echo e($attachmentId); ?>" class="element-item col-auto me-1 float-start" style="max-width: 50px;">
                                                                <div class="gallery-box card p-0 mb-0 mt-1">
                                                                    <div class="gallery-container">
                                                                        <a href="<?php echo e($attachmentUrl); ?>" class="image-popup" title="Imagem capturada em <?php echo e($dateAttachment); ?>hs" data-gallery="gallery-<?php echo e($radioIndex); ?>">
                                                                            <img class="rounded gallery-img" alt="image" width="100%" src="<?php echo e($attachmentUrl); ?>" loading="lazy">

                                                                            <div class="gallery-overlay">
                                                                                <h5 class="overlay-caption fs-10"><?php echo e($dateAttachment); ?></h5>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-12">
                                        Você <span class="text-warning">discorda</span> ou <span class="text-secondary">concorda</span> desta avaliação?
                                    </div>
                                </div>

                                <div class="row">
                                    <?php if( $auditorStatus != 'completed' && $auditorStatus != 'losted' ): ?>
                                        <button tabindex="-1"
                                            type="button"
                                            data-assignment-id="<?php echo e($assignmentId); ?>"
                                            data-step-id="<?php echo e($stepId); ?>"
                                            data-topic-id="<?php echo e($topicId); ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-trigger="hover"
                                            data-bs-placement="left"
                                            title="<?php echo e($responseId ? 'Atualizar' : 'Salvar'); ?>"
                                            class="btn btn-outline-light ps-1 pe-1 btn-response-update d-none">
                                                <i class="<?php echo e($responseId ? 'ri-refresh-line' : 'ri-save-3-line'); ?> text-theme fs-3 m-2"></i>
                                        </button>
                                    <?php endif; ?>

                                    <div class="btn-group">
                                        <?php if( $auditorStatus != 'completed' && $auditorStatus != 'losted' ): ?>
                                            <label for="input-attachment-<?php echo e($radioIndex); ?>" class="btn btn-light d-flex align-content-center flex-wrap me-1 mb-0 rounded-2 btn-add-photo" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Anexar Fotografia" data-step-id="<?php echo e($stepId); ?>" data-topic-id="<?php echo e($topicId); ?>">
                                                <i class="ri-image-add-fill text-body fs-1 m-auto text-white"></i>
                                            </label>
                                            <input type="file" id="input-attachment-<?php echo e($radioIndex); ?>" class="input-upload-photo d-none" accept="image/jpeg">
                                        <?php endif; ?>

                                        <label class="btn btn-light d-flex align-content-center flex-wrap text-center ms-1 me-1 mb-0 btn-toggle-element rounded-2" data-toggle-target="textarea-<?php echo e($topicIndex.$radioIndex); ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Adicionar Observações">
                                            <i class="ri-feedback-line text-body fs-1 m-auto  text-white"></i>
                                        </label>

                                        <input tabindex="-1" class="d-none" type="radio" name="compliance_audit" role="switch" id="NoSwitchCheck<?php echo e($topicIndex.$radioIndex); ?>" <?php echo e($auditorStatus == 'losted' ? 'disabled' : ''); ?> value="no" <?php echo e($complianceAudit && $complianceAudit == 'no' ? 'checked' : ''); ?>>
                                        <label for="NoSwitchCheck<?php echo e($topicIndex.$radioIndex); ?>" class="btn btn-<?php echo e($complianceAudit && $complianceAudit == 'no' ? '' : 'outline-'); ?>warning d-flex align-content-center flex-wrap ms-1 me-1 mb-0 rounded-2 border-1 border-warning border-opacity-10 btn-compliance" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Discordo">
                                            <i class="ri-emotion-unhappy-line label-icon align-middle rounded-pill fs-1 m-auto text-white"></i>
                                        </label>

                                        <input tabindex="-1" class="d-none" type="radio" name="compliance_audit" role="switch" id="YesSwitchCheck<?php echo e($topicIndex.$radioIndex); ?>" <?php echo e($auditorStatus == 'losted' ? 'disabled' : ''); ?> value="yes" <?php echo e($complianceAudit && $complianceAudit == 'yes' ? 'checked' : ''); ?>>
                                        <label for="YesSwitchCheck<?php echo e($topicIndex.$radioIndex); ?>" class="btn btn-<?php echo e($complianceAudit && $complianceAudit == 'yes' ? '' : 'outline-'); ?>secondary d-flex align-content-center flex-wrap ms-1 me-0 mb-0 rounded-2 border-1 border-secondary border-opacity-10 btn-compliance" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Concordo">
                                            <i class="ri-emotion-happy-line label-icon align-middle rounded-pill fs-1 m-auto text-white"></i>
                                        </label>
                                    </div>

                                    <div class="col-sm-12">
                                        <textarea tabindex="-1" class="form-control border-light mt-3 mb-0" id="textarea-<?php echo e($topicIndex.$radioIndex); ?>" maxlength="1000" name="comment_audit" placeholder="Observações..." <?php echo e($auditorStatus == 'losted' ? 'disabled readonly' : ''); ?> style="height: 100px; display:<?php echo e(!$commentAudit ? 'none' : ''); ?>;"><?php echo e($commentAudit ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="gallery-wrapper row m-0 mt-3"><?php if( !empty($auditAttachmentIds) && is_array($auditAttachmentIds) ): ?>
                                    <?php $__currentLoopData = $auditAttachmentIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachmentId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $attachmentUrl = $dateAttachment = '';
                                            if (!empty($attachmentId)) {
                                                $attachmentUrl = App\Models\Attachments::getAttachmentPathById($attachmentId);

                                                $dateAttachment = App\Models\Attachments::getAttachmentDateById($attachmentId);
                                            }
                                        ?>
                                        <?php if($attachmentUrl): ?>
                                            <div id="element-attachment-<?php echo e($attachmentId); ?>" class="element-item col-auto">
                                                <div class="gallery-box card p-0 mb-0 mt-1">
                                                    <div class="gallery-container">
                                                        <a href="<?php echo e($attachmentUrl); ?>" class="image-popup" title="Imagem capturada em <?php echo e($dateAttachment); ?>hs" data-gallery="gallery-<?php echo e($radioIndex); ?>">
                                                            <img class="rounded gallery-img" alt="image" height="70" src="<?php echo e($attachmentUrl); ?>" loading="lazy">

                                                            <div class="gallery-overlay">
                                                                <h5 class="overlay-caption fs-10"><?php echo e($dateAttachment); ?></h5>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>

                                                <?php if( $auditorStatus != 'auditing' && $auditorStatus != 'losted' ): ?>
                                                    <div class="position-absolute translate-middle mt-n2 ms-2">
                                                        <div class="avatar-xs">
                                                            <button type="button" class="avatar-title bg-light border-0 rounded-circle text-danger cursor-pointer btn-delete-photo" data-attachment-id="<?php echo e($attachmentId); ?>" title="Deletar Arquivo">
                                                                <i class="ri-delete-bin-2-line"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <input type="hidden" name="attachment_id[]" value="<?php echo e($attachmentId); ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?></div>
                            </form>
                        </div>
                    </div>
                </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if( $auditorStatus != 'completed' && $auditorStatus != 'losted' ): ?>
        <button tabindex="-1"
            type="button"
            class="btn btn-lg btn-theme w-100 <?php echo e($countResponses < $countTopics ? 'd-none' : ''); ?> mb-3"
            id="btn-response-finalize"
            data-assignment-id="<?php echo e($assignmentId); ?>"
            title="Finalizar Auditoria">
            <i class="ri-save-3-line align-bottom m-2"></i> Finalizar Auditoria
        </button>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/layouts/form-auditor-step-cards.blade.php ENDPATH**/ ?>