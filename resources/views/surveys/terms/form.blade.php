<h6 class="text-info">
    Registre um novo Termo @if($terms->isNotEmpty()) ou selecione entre os existentes @endif
</h6>

<div class="form-group mt-4">
    <label for="termiInput" class="form-label">
        Registrar Termo:
        <i class="ri-question-line text-info non-printable align-top" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="Explicação" data-bs-content="Considere <u>Termo</u> o mesmo que Setor/Departamento/Ambiente/Local/Categoria.<br>Ao construir seu modelo, o Termo será o nome do bloco que conterá os Tópicos (questões)."></i>
    </label>
    <div class="input-group">
        <input type="text" class="form-control" id="termiInput" maxlength="100" autocomplete="off">
        <button id="btn-add-survey-term" type="button" class="btn btn-label btn-outline-theme" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Adicionar Termo"><i class="ri-add-line label-icon align-middle fs-16 me-2"></i> Termo</button>
    </div>
</div>

<hr class="w-50 start-50 position-relative translate-middle-x clearfix mt-4 mb-4">

@if($terms->isNotEmpty())
    <form id="surveysPopulateTermForm" method="POST" class="needs-validation" novalidate autocomplete="off">
        <label class="form-label">
            Selecione:
            <i class="ri-question-line text-info non-printable align-top" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="Termos Disponíveis" data-bs-content="Os Termos aqui listados não foram ainda inseridos na listagem"></i>
        </label>
        <div class="row">
            @foreach ($terms as $term)
                <div class="col-sm-12 col-md-6 col-lg-6 form-check-container">
                    <div class="form-check form-switch form-switch-success mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="step_terms[]" value="{{$term->id}}" id="SwitchCheck{{$term->id}}" required>
                        <label class="form-check-label" for="SwitchCheck{{$term->id}}">{{ $term->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="wrap-form-btn d-none mt-3">
            <button type="button" id="btn-add-multiple-blocks" class="btn btn-sm btn-outline-theme float-end" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Os Termos Selecionados serão adicionados a sua lista" title="Popular a Listagem"><i class="ri-folder-add-line align-middle fs-16 me-2"></i>Popular Listagem</button>
        </div>
    </form>
@else
    <div class="text-muted text-center">Novos Termos ainda não foram registrados</div>
@endif
