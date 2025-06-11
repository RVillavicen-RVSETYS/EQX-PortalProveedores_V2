<div class="border mx-1 my-1 px-1 py-1 formulario-nota" data-id="{{id}}">

    <div class="text-right mt-2">
        <button type="button" class="btn btn-danger btn-sm btn-eliminar-nota">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>

    <div class="form-group">
        <label for="notaCredito_{{id}}" class="col-4 col-form-label">Nota Crédito {{id}}</label>
        <div class="col-12 input-group mb-3">
            <select name="notaCredito[{{id}}][]" id="notaCredito_{{id}}" class="select2 form-control custom-select" style="width: 100%; height: 36px;" multiple required>

            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Nota de Crédito</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-file-pdf text-danger"></i> PDF</span>
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="notaCredPDF_{{id}}" name="notaCreditoArchivo[{{id}}][pdf]" accept="application/pdf" required>
                <label class="custom-file-label" for="notaCredPDF_{{id}}">Elegir PDF de Nota de Crédito...</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group mt-2">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-file-code text-info"></i> XML</span>
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="notaCredXML_{{id}}" name="notaCreditoArchivo[{{id}}][xml]" accept=".xml" required>
                <label class="custom-file-label" for="notaCredXML_{{id}}">Elegir XML de Nota de Crédito...</label>
            </div>
        </div>
    </div>
</div>