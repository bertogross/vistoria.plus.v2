import {
    toastAlert,
    ajaxContentFromURL,
    goTo
} from './helpers.js';

import {
    removeInputIfTheSameExistOnTheListing,
    reloadTermsForm
} from './surveys-terms.js';

document.addEventListener('DOMContentLoaded', function() {


    document.addEventListener('click', function(event) {
        // The event.target contains the clicked element
        const clickedElement = event.target;
        //console.log('Clicked element:', clickedElement);

        if(clickedElement){
            // Check if the clicked element is a remove block button
            if (clickedElement.classList.contains('btn-remove-block')) {
                event.preventDefault();

                const targetId = clickedElement.getAttribute("data-target");
                const isConfirmed = confirm('Certeza que deseja deletar este bloco de Termo?');
                if (!isConfirmed) {
                    event.stopPropagation();

                    return;
                }

                const blockElement = document.querySelector('.block-item[id="' + targetId + '"]');
                if (blockElement) {
                    blockElement.remove();

                    attachNestedListeners()

                    reloadTermsForm();

                    attachTemplateAutosave();

                    event.stopPropagation();

                    return;
                }
            }

            // Check if the clicked element is a remove topic button
            if (clickedElement.classList.contains('btn-remove-topic')) {
                event.preventDefault();

                const targetId = clickedElement.getAttribute("data-target");
                const isConfirmed = confirm('Certeza que deseja deletar este Tópico?');
                if (!isConfirmed) {
                    event.stopPropagation();

                    return;
                }

                const topicElement = document.querySelector('.step-topic[id="' + targetId + '"]');
                if (topicElement) {
                    topicElement.remove();

                    attachNestedListeners();

                    attachTemplateAutosave();

                    event.stopPropagation();

                    return;
                }
            }

            // Attach event listener to the new "Add Topic" button
            if (clickedElement.classList.contains('btn-add-topic')) {
                event.preventDefault();

                const stepIndex = parseInt(clickedElement.getAttribute('data-block-index'));
                const termId = parseInt(clickedElement.getAttribute('data-block-step-id'));

                addNewTopic(termId, stepIndex);
            }

            // Check if the clicked element is the one you're interested in
            if (clickedElement.id === 'btn-add-multiple-blocks') {
                event.preventDefault();

                const form = document.getElementById('surveysPopulateTermForm');
                if (!form) {
                    console.error('Form not found');
                    return;
                }

                removeInputIfTheSameExistOnTheListing();

                var checkboxes = document.querySelectorAll('input[name="step_terms[]"]:checked');
                var checkedItems = Array.from(checkboxes).map(function(checkbox) {
                    var label = document.querySelector('label[for="' + checkbox.id + '"]');
                    let term_label = label ? label.textContent.trim() : '';
                    let term_id = checkbox.value;

                    setTimeout(function() {
                        addNewBlock(term_label, term_id);
                    }, 500);
                });

                if (checkedItems) {
                    toastAlert('Termos adicionados', 'success', 5000);
                    document.querySelector('[data-bs-dismiss="modal"]').click();
                }
            }

            // Attach event listeners to accordion toggle buttons
            // Check if the clicked element or its parent has the 'btn-accordion-toggle' class
            if (clickedElement.classList.contains('btn-accordion-toggle') || clickedElement.closest('.btn-accordion-toggle')) {
                event.preventDefault();

                // Find the closest accordion-toggle button, in case the clicked element is a child
                const accordionToggleButton = clickedElement.closest('.btn-accordion-toggle');
                const accordionCollapse = accordionToggleButton.closest('.accordion-item').querySelector('.accordion-collapse');

                // Toggle the accordion collapse
                if (accordionCollapse.classList.contains('show')) {
                    accordionCollapse.classList.remove('show');
                    accordionToggleButton.classList.remove('ri-arrow-up-s-line');
                    accordionToggleButton.classList.add('ri-arrow-down-s-line');
                } else {
                    accordionCollapse.classList.add('show');
                    accordionToggleButton.classList.remove('ri-arrow-down-s-line');
                    accordionToggleButton.classList.add('ri-arrow-up-s-line');
                }
            }

        }
    });

    // Add a new step block to the form
    function addNewBlock(value, termId) {
        const blocksContainer = document.querySelector('.nested-sortable-block');
        if (blocksContainer) {
            const blockIndex = blocksContainer.children.length;
            const newBlock = document.createElement('div');
            newBlock.id = termId;
            newBlock.className = 'accordion-item block-item mt-3 mb-0 border-dark border-1 rounded rounded-2 p-0 bg-body';

            newBlock.innerHTML = `
                <div class="input-group">
                    <input type="text" class="form-control text-theme text-uppercase" name="steps[${blockIndex}]['stepData']['term_name']" placeholder="Informe o Setor/Etapa" maxlength="100" value="${value}" readonly required tabindex="-1">
                    <div class="btn btn-ghost-dark btn-icon rounded-pill cursor-n-resize handle-block ri-arrow-up-down-line text-body" title="Reordenar bloco de Termo"></div>

                    <button type="button" class="btn btn-ghost-dark btn-icon rounded-pill btn-accordion-toggle ri-arrow-up-s-line" tabindex="-1"></button>
                </div>

                <input type="hidden" name="steps[${blockIndex}]['stepData']['term_id']" value="${termId}">
                <input type="hidden" name="steps[${blockIndex}]['stepData']['type']" value="custom">
                <input type="hidden" name="steps[${blockIndex}]['stepData']['original_position']" value="${blockIndex}">
                <input type="hidden" name="steps[${blockIndex}]['stepData']['new_position']" value="${blockIndex}">

                <div class="accordion-collapse collapse show">
                    <div class="nested-sortable-topic mt-0 p-1"></div>

                    <div class="clearfix">
                        <button type="button" class="btn btn-ghost-dark btn-icon btn-add-topic rounded-pill float-end cursor-copy text-theme ri-menu-add-line" data-block-index="${blockIndex}" data-block-step-id="${termId}" title="Adicionar Tópico"></button>

                        <button type="button" class="btn btn-ghost-danger btn-icon rounded-pill btn-remove-block float-end ri-delete-bin-7-fill" data-target="${termId}" title="Remover Bloco" tabindex="-1"></button>
                    </div>
                </div>
            `;
            blocksContainer.appendChild(newBlock);

            setTimeout(function() {
                var selector = `.btn-add-topic[data-block-index="${blockIndex}"]`;
                document.querySelector(selector).click();

                attachNestedListeners();

                attachTemplateAutosave();

                //attachRemoveButtonListeners();
                //choicesListeners(surveysTermsSearchURL, surveysTermsStoreOrUpdateURL, choicesSelectorClass);
            }, 500);
        }
    }

    // Add a new topic to a step block
    function addNewTopic(termId, stepIndex) {
        const topicContainer = document.querySelector(`.block-item[id="${termId}"] .nested-sortable-topic`);
        if (topicContainer) {
            const topicIndex = topicContainer.children.length;
            const newTopic = document.createElement('div');
            newTopic.className = 'step-topic mt-1 mb-1';
            newTopic.id = `${termId}${topicIndex}`;

            newTopic.innerHTML = `
                <div class="row">
                    <div class="col-auto">
                        <button type="button" class="btn btn-ghost-danger btn-icon rounded-pill btn-remove-topic ri-delete-bin-3-line" data-target="${termId}${topicIndex}" title="Remover Bloco" tabindex="-1"></button>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control focus-${termId}${topicIndex} input-topic" title="Exemplo: Organização do setor?... Abastecimento de produtos/insumos está em dia?" data-placeholder="Tópico..." name="steps[${stepIndex}]['topics']['question']" placeholder="Exemplo: Organização do Setor" maxlength="150" required></input>
                    </div>
                    <div class="col-auto">
                        <div class="btn btn-ghost-dark btn-icon rounded-pill cursor-n-resize handle-topic ri-arrow-up-down-line" title="Reordenar Tópico"></div>
                    </div>
                    <input type="hidden" name="steps[${stepIndex}]['topics']['original_position']" tabindex="-1" value="${topicIndex}">
                    <input type="hidden" name="steps[${stepIndex}]['topics']['new_position']" tabindex="-1" value="${topicIndex}">
                </div>
            `;
            topicContainer.appendChild(newTopic);

            setTimeout(function() {
                attachNestedListeners();

                attachTemplateAutosave();

                //var inputName = `steps[${stepIndex}]['topics']['question']`;
                //document.querySelector('input[name="' + inputName + '"]').focus();
                document.querySelector('input.focus-' + termId + topicIndex + '').focus();

                //attachRemoveButtonListeners();
                //choicesListeners(surveysTermsSearchURL, surveysTermsStoreOrUpdateURL, choicesSelectorClass);
            }, 500);
        }
    }

    // Initialize nested sortable elements using Sortable.js
    function attachNestedListeners() {
        // Helper function to initialize Sortable with common settings
        function initializeSortable(selector, handleClass) {
            document.querySelectorAll(selector).forEach(function(element) {
                new Sortable(element, {
                    handle: handleClass,
                    swap: true,
                    swapClass: 'bg-warning-subtle',
                    animation: 150,
                    fallbackOnBody: true,
                    invertSwap: true,
                    swapThreshold: 0.85,
                    sort: true,
                    group: selector === '.nested-sortable-topic' ? 'shared' : null,
                    onUpdate: function () {
                        updateNestedPositions(selector);

                        attachTemplateAutosave();
                    }
                });
            });
        }

        // Initialize Sortable for nested receiver blocks
        initializeSortable('.nested-sortable-block', '.handle-block');

        // Initialize Sortable for nested receiver topics
        initializeSortable('.nested-sortable-topic', '.handle-topic');
    }
    attachNestedListeners();


    // Function to update positions of nested elements (blocks or topics)
    function updateNestedPositions(selector) {
        document.querySelectorAll(selector).forEach(function(nestedSortReceiver) {
            Array.from(nestedSortReceiver.children).forEach(function(child, idx) {
                const newPositionInput = child.querySelector('[name$="[\'new_position\']"]');
                if (newPositionInput) {
                    newPositionInput.value = idx;
                }
            });
        });
    }

    // Update positions for both blocks and topics
    updateNestedPositions('.nested-sortable-block');
    updateNestedPositions('.nested-sortable-topic');


    const selecteWarehouseTemplateButtons = document.querySelectorAll('.btn-warehouse-load-selected-template');
    if(selecteWarehouseTemplateButtons){
        selecteWarehouseTemplateButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var id = this.getAttribute("data-id");

                document.getElementById('nested-compose-area').style.display = 'block';

                toastAlert('Carregando modelo...', 'info', 3000, true);

                document.getElementById('load-selected-template-form').innerHTML = '<div class="text-center"><div class="spinner-border text-theme" role="status"><span class="sr-only">Loading...</span></div></div>';

                setTimeout(function() {

                    ajaxContentFromURL(id, surveysTemplateSelectedFromWarehouseURL, 'load-selected-template-form', '', false);

                    document.getElementById('accordion-templates-label').style.display = 'none';
                    document.getElementById('accordion-templates').style.display = 'none';

                    //goTo('load-selected-template-form');

                    setTimeout(function() {
                        attachNestedListeners();

                        // Update positions for both blocks and topics
                        updateNestedPositions('.nested-sortable-block');
                        updateNestedPositions('.nested-sortable-topic');

                    }, 1000);
                }, 3000);
            });
        });
    }


    const selecteSurveyTemplateButtons = document.querySelectorAll('.btn-SurveyTemplate-load-selected-template');
    if(selecteSurveyTemplateButtons){
        selecteSurveyTemplateButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var id = this.getAttribute("data-id");

                document.getElementById('nested-compose-area').style.display = 'block';

                toastAlert('Carregando modelo...', 'info', 3000, true);

                document.getElementById('load-selected-template-form').innerHTML = '<div class="text-center"><div class="spinner-border text-theme" role="status"><span class="sr-only">Loading...</span></div></div>';

                setTimeout(function() {

                    ajaxContentFromURL(id, surveysTemplateSelectedFromSurveyTemplateURL, 'load-selected-template-form', '', false);

                    document.getElementById('accordion-templates-label').style.display = 'none';
                    document.getElementById('accordion-templates').style.display = 'none';

                    //goTo('load-selected-template-form');

                    setTimeout(function() {
                        attachNestedListeners();

                        // Update positions for both blocks and topics
                        updateNestedPositions('.nested-sortable-block');
                        updateNestedPositions('.nested-sortable-topic');

                    }, 1000);
                }, 3000);
            });
        });
    }


    function attachTemplateAutosave(){
        const btnAutoSave = document.getElementById('btn-survey-template-autosave');

        if(btnAutoSave){
            setTimeout(() => {
                btnAutoSave.click();
            }, 100);
        }
    }


    // Function to observe DOM changes
    function observeDOMChanges() {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    removeInputIfTheSameExistOnTheListing();
                }
                //attachNestedListeners();
            });
        });

        var config = { childList: true, subtree: true };

        // Start observing the target node for configured mutations
        observer.observe(document.body, config);
    }



    // Attach event listeners to accordion toggle buttons
    /*
    function attachElementAccordionToggleButtonListeners() {
        var accordionToggleButtons = document.querySelectorAll('.btn-accordion-toggle');
        if (accordionToggleButtons) {
            accordionToggleButtons.forEach(function(button) {
                if (!button.hasAttribute('data-listener-attached')) {
                    button.setAttribute('data-listener-attached', 'true');

                    button.addEventListener('click', function(event) {
                        event.preventDefault();

                        const accordionCollapse = button.closest('.accordion-item').querySelector('.accordion-collapse');

                        if (accordionCollapse.classList.contains('show')) {
                            accordionCollapse.classList.remove('show');
                            button.classList.remove('ri-arrow-up-s-line');
                            button.classList.add('ri-arrow-down-s-line');
                        } else {
                            accordionCollapse.classList.add('show');
                            button.classList.remove('ri-arrow-down-s-line');
                            button.classList.add('ri-arrow-up-s-line');
                        }

                        return;
                    });
                }
            });
        }
    }
    */



    // Call the function when the DOM is fully loaded
    //attachNewBlockButtonListeners();
    //attachNewTopicButtonListeners();
    //attachRemoveButtonListeners();
    //attachElementAccordionToggleButtonListeners();
    observeDOMChanges();
    //attachbtnAddMultipleBlocksListeners();

});
