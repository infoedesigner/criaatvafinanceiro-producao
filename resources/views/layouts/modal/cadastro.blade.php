<!-- Modal -->
<div class="modal fade @isset($chamadaCadastroModal) {{   $chamadaCadastroModal }} @endisset" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog  modal-lg modal-dialog-centered modal-lg" role="document" style="max-width: 780vh !important;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCenterTitle">
              @isset($tituloCadastroModal) {{   $tituloCadastroModal }} @endisset
        </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="recarrega();">
            <span aria-hidden="true">&times;</span>
          </button>
          
        </div>

            <iframe class="modal-body" src="{{ url('/'. $rotaCadastroModal ) }}" id="{{ $idFrame }}" frameborder="0" style="height: 60vh;">
            </iframe>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="recarrega();" data-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
</div>
