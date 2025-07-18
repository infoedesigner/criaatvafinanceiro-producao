@extends('ordemdeservicos.estilo')
@include('layouts/cadastracliente')

<div class="form-group row">
    <label for="dataVendaOrdemdeServico" class="col-sm-2 col-form-label">Data Início</label>
    <div class="col-sm-6">
        {!! Form::date('dataCriacaoOrdemdeServico', $dataInicio, [
            'placeholder' => 'Preencha este campo',
            'class' => 'col-sm-4 form-control',
            'maxlength' => '100',
        ]) !!}
    </div>
</div>

<div class="form-group row">

    <label for="idClienteOrdemdeServico" class="col-sm-2 col-form-label">Cliente</label>
    <div class="col-sm-4">
        <select name="idClienteOrdemdeServico" id="idClienteOrdemdeServico" class="selecionaComInput form-control">
            <option value="" selected disabled>Selecione...</option>
            @foreach ($cliente as $clientes)
                <option value="{{ $clientes->id }}"
                    @if (isset($ordemdeservico)) @if ($ordemdeservico->idClienteOrdemdeServico == $clientes->id) selected @endif @endif>{{ $clientes->razaosocialCliente }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-sm-2 pl-0">
        <button type="button" onclick="recarregaComboCliente()" class="btn btn-dark"><i
                class="fas fa-sync"></i></i></button>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".cliente"><i
                class="fas fa-industry pr-1"></i>Novo Cliente</button>
    </div>


    <label for="valorOrdemdeServico" class="col-sm-2 col-form-label">Valor do Projeto</label>
    <div class="col-sm-2">
        {!! Form::text('valorOrdemdeServico', $valorInput, [
            'class' => 'campo-moeda-com-zero form-control valorOrdemdeServico',
            'step' => 'any',
            'id' => 'valorOrdemdeServico',
        ]) !!}
    </div>
</div>

<div class="form-group row">

    <label for="vendedor" class="col-sm-2 col-form-label">Vendedor</label>
    <div class="col-sm-4">
        <select name="vendedor" id="vendedor" class="selecionaComInput form-control">
            <option value="" selected disabled>Selecione...</option>
            @foreach ($vendedores as $vendedor)
                <option value="{{ $vendedor->id }}"
                    @if (isset($ordemdeservico)) @if ($ordemdeservico->vendedor == $vendedor->id) selected @endif @endif>{{ $vendedor->razaosocialFornecedor }}</option>
            @endforeach
        </select>
    </div>

</div>

<div class="form-group row">

    {{ Form::label('percentual', 'Percentual Permitido', ['class' => 'col-sm-2 col-form-label']) }}
    <div class="col-sm-6">
        <div class="input-group">
            {{ Form::text('percentualPermitido', null, ['maxlength' => 3, 'step' => 1, 'class' => 'form-control col-sm-1 percentualPermitido', 'onfocusout' => "javascript: if (this.value < -1) this.value = 0; if (this.value.length >= 3) this.value = 100;"]) }}
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
    </div>
    <label for="valorOrcamento" class="col-sm-2 col-form-label">Valor do Orçamento</label>
    <div class="col-sm-2">

        {!! Form::text('valorOrcamento', $valorInput, [
            'class' => 'campo-moeda-com-zero form-control valorOrcamento',
            'step' => 'any',
            'id' => 'valorOrcamento',
            ]) !!}
    </div>
</div>


<div class="form-group row">
    <label for="eventoOrdemdeServico" class="col-sm-2 col-form-label">Evento</label>
    <div class="col-sm-10">
        {!! Form::text('eventoOrdemdeServico', $valorInput, [
            'placeholder' => 'Preencha este campo',
            'class' => 'form-control',
            'maxlength' => '100',
            'required',
        ]) !!}

    </div>
</div>

<div class="form-group row">
    <label for="obsOrdemdeServico" class="col-sm-2 col-form-label">Observação</label>
    <div class="col-sm-10">
        {!! Form::text('obsOrdemdeServico', $valorInput, [
            'placeholder' => 'Preencha este campo',
            'class' => 'form-control',
            'maxlength' => '100',
        ]) !!}
    </div>
</div>

<div class="form-group row">
    <label for="fatorR" class="col-sm-2 col-form-label">Serviço com fator R?</label>
    <div class="ml-4 row mt-3">
        
        <input type="radio"  name="fatorR" id="fatorR" value="0"
        @if (isset($ordemdeservico->fatorR) && $ordemdeservico->fatorR == '0') checked @endif>
        <label for="fatorR" class="col-sm-1 p-0 col-form-label">Não</label>
    </div>
    <div class="ml-4 row mt-3">
        
            <input type="radio" name="fatorR" id="fatorR" value="1"
                @if (isset($ordemdeservico->fatorR) && $ordemdeservico->fatorR == '1') checked @endif>
            <label for="fatorR" class="col-sm-1 p-0 col-form-label">Sim</label>
    </div>
</div>


{!! Form::hidden('ehcompra', '0', [
    'placeholder' => 'Preencha este campo',
    'class' => 'form-control',
    'maxlength' => '100',
]) !!}


{!! Form::hidden('idOS', 'null', [
    'placeholder' => 'Id OS ',
    'class' => 'form-control',
    'maxlength' => '1',
    'id' => 'idOS',
]) !!}
{!! Form::hidden('excluidoDespesa', '0', [
    'placeholder' => 'Excluído ',
    'class' => 'form-control',
    'maxlength' => '1',
    'id' => 'excluidoDespesa',
]) !!}

<div class="pull-left">
    <h4>Forma de Pagamento</h4>
</div>

<hr>


<table class="styled-table rwd-table" id="tabelaPagamento">
    <thead>
        <tr class="col-sm-10">

            <th class="col-sm-2" style="width:20%;">Forma Pagamento</th>
            <th class="col-sm-1" style="width:-webkit-fill-available;">
                <nobr>Valor Parcela</nobr>
            </th>
            <th class="col-sm-1" style="width:-webkit-fill-available;">Pago</th>
            <th class="col-sm-2" style="width:-webkit-fill-available;">Data Emissão NF</th>
            <th class="col-sm-2" style="width:-webkit-fill-available;">Data de Pagamento</th>
            <th class="col-sm-1" style="width:-webkit-fill-available;">Conta</th>
            <th class="col-sm-1" style="width:-webkit-fill-available;">
                <nobr>Nota Fiscal<nobr>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody id="habilita_receita" class="habilita_receita">

        @if (Route::currentRouteName() === 'ordemdeservicos.create')
            @include('ordemdeservicos/trcreate')
        @else
            @foreach ($receitasPorOS as $dadosreceita)
                @if ($dadosreceita->valorreceita != '0.00' && $dadosreceita->valorreceita != '0,00')
                    @include('ordemdeservicos/trview')
                @endif
            @endforeach

        @endif
    </tbody>


</table>
<div class="novaDivReceita styled-table" id="novaDivReceita">
</div>

{!! Form::hidden('registroreceita', '0', [
    'placeholder' => 'Preencha este campo',
    'class' => 'col-sm-8 form-control',
    'maxlength' => '100',
]) !!}

{!! Form::hidden('idosreceita', 'null', [
    'placeholder' => 'Id OS Receita',
    'class' => 'form-control',
    'maxlength' => '1',
    'id' => 'idosreceita',
]) !!}

<input type="hidden" id="valorProjetoOrdemdeServico" class="form-control" name="valorProjetoOrdemdeServico"
    value="0.00" placeholder="Preencha o preço real" /><br>

{!! Form::hidden('dataOrdemdeServico', '00-00-0000', [
    'placeholder' => 'Preencha este campo',
    'class' => 'form-control',
    'maxlength' => '100',
]) !!}

{!! Form::hidden('servicoOrdemdeServico', 'Campo Serviço', [
    'placeholder' => 'Preencha este campo',
    'class' => 'form-control',
    'maxlength' => '100',
]) !!}


{!! Form::hidden('dataExclusaoOrdemdeServico', '00', [
    'placeholder' => 'Data Exclusão',
    'class' => 'form-control',
    'maxlength' => '1',
    'id' => 'dataExclusaoOrdemdeServico',
]) !!}
{!! Form::hidden('ativoOrdemdeServico', '1', [
    'placeholder' => 'Ativo',
    'class' => 'form-control',
    'maxlength' => '1',
    'id' => 'ativoOrdemdeServico',
]) !!}
{!! Form::hidden('excluidoOrdemdeServico', '0', [
    'placeholder' => 'Excluído',
    'class' => 'form-control',
    'maxlength' => '1',
    'id' => 'excluidoOrdemdeServico',
]) !!}


<script>
    $(document).ready(function() {

        // Função para remover pontos de milhar e substituir vírgula por ponto
        function parseCurrencyValue(value) {
            return parseFloat(value.replace(/\./g, '').replace(',', '.'));
        }

        // Validar os campos "Valor do Projeto" e "Percentual Permitido" para aceitar apenas números e vírgula
        $(".valorOrdemdeServico, .percentualPermitido").on('change', function () {
            var value = $(this).val().replace(/[^0-9,]/g, ''); // Aceita apenas números e vírgula
            $(this).val(value);

            // Após alterar o valor de um dos campos, chame a função de cálculo
            calcularValorTotal();
        });
        
        
         // Função para calcular o valor total
        function calcularValorTotal() {
            // Capturar os valores dos campos
            var valorOrdemdeServico = parseCurrencyValue($(".valorOrdemdeServico").val());
            var percentualPermitido = parseCurrencyValue($(".percentualPermitido").val());

            // Verificar se os valores são números válidos
            if (!isNaN(valorOrdemdeServico) && !isNaN(percentualPermitido)) {
                // Construir os parâmetros da requisição
                var formData = new FormData();
                formData.append('value1', valorOrdemdeServico);
                formData.append('percent', percentualPermitido);

                // Configurações da requisição AJAX
                var settings = {
                    "url": "/api/calculatePercent",
                    "method": "POST",
                    "data": formData,
                    "contentType": false,
                    "processData": false
                };

                // Enviar requisição AJAX
                $.ajax(settings).done(function (response) {
                    $(".valorOrcamento").val(response); // Atualizar o campo com o valor calculado
                });
            }
        }

    });

    function recarregaComboCliente() {
        $('#idClienteOrdemdeServico').select2('destroy');

        let dropdown = $('#idClienteOrdemdeServico');
        dropdown.empty();
        dropdown.append('<option selected="true" disabled>SELECIONE O CLIENTE...</option>');
        dropdown.prop('selectedIndex', 0);

        const url = "{{ route('listaClientes') }}";

        $.getJSON(url, function(data) {
            $.each(data, function(key, dadosjson) {
                dropdown.append($('<option></option>').attr('value', dadosjson.id).text(dadosjson.razaosocialCliente));
            })
        });
        $('#idClienteOrdemdeServico').select2();
    }

    function pegaIdFornecedor() {
        var selecionado = $('#pagoreceita').find(':selected').val();
    }

    $("#btnAddColumn").click(function() {
        var row = $("#tabelaPagamento tr:last");

        row.find(".selecionaComInput").each(function(index) {
            $(this).select2('destroy');
        });

        var newrow = row.clone();

        $("#tabelaPagamento").append(newrow);

        $("select.selecionaComInput").select2();

        var tr = $(this).closest('tr');
        console.log(tr.attr(newrow));
    });

    function idSemValor() {
        $('input.idReceita').val('000000');
    }

    $('body').on('click', '.duplicar', function() {
        var row = $(this).closest('tr');
        row.find(".selecionaComInput").each(function(index) {

            $(this).select2('destroy');

        });
        row.find(".campo-moeda").each(function(index) {
            $(this).maskMoney('destroy');
        });
        var newrow = row.clone();
        $("#tabelaPagamento").append(newrow);
        $("select.selecionaComInput").select2();
        $('input.campo-moeda')
            .maskMoney({
                prefix: 'R$ ',
                allowNegative: false,
                thousands: '.',
                decimal: ',',
                affixesStay: false
            });
        newrow.find(".idReceita").each(function(index) {
            $(this).val('novo');
        });
        $('input[type=text].idReceita').val('novo');
    });

    function removerCampos() {
        $('.novaDivReceita').empty();
    }

    $('body').on('click', '.deletar', function() {

        var $tr = $(this).closest('tr');
        if ($tr.attr('class') == 'linhaTabela1') {
            @if (Request::path() == 'ordemdeservicos/create')
                $tr.nextUntil('tr[class=linhaTabela1]').andSelf().remove();
            @else
                $tr.find(".excluidoreceita").each(function(index) {
                    $(this).val('1');
                });
                $tr.nextUntil('tr[class=linhaTabela1]').andSelf().hide();
            @endif
        } else {
            @if (Request::path() == 'ordemdeservicos/create')
                $tr.remove();
            @else
                $tr.find(".excluidoreceita").each(function(index) {
                    $(this).val('1');
                });
                $tr.hide();
            @endif
        }


    });

    function alteraRetornoCadastroOS(e, retorno) {
        e.preventDefault();
        validador = validaFormulario();
        if (validador == 0) {
            // document.getElementById("tpRetorno").value = retorno;
            // $('#btnSalvareVisualizar').attr('disabled', 'disabled');
            $('#btnSalvar').attr('disabled', 'disabled');
            $("#manipulaOS").submit();
        }
    }

    function sum(input) {

        if (toString.call(input) !== "[object Array]")
            return false;

        var total = 0;
        for (var i = 0; i < input.length; i++) {
            if (isNaN(input[i])) {
                continue;
            }
            total += Number(input[i]);
        }
        return total;
    }

    function alertaErros(texto, contadorErros) {
        if (contadorErros > 0) {

            var span = document.createElement("span");
            span.innerHTML = texto;

            Swal.fire({
                icon: 'error',
                html: span,
                title: 'Validações Necessárias',
                footer: 'Restam  ' + contadorErros + ' pendências.'
            })
        }
        return contadorErros;
    }

    function validaFormulario() {
        contadorErros = 0;
        contadorDatasDiferentesAnoAtual = 0;

        texto = '';
        textoData = '';

        /* ---------------------------------------------------
                    VERIFICAÇÃO DE DATA - RECEITA
        ----------------------------------------------------*/
        const dataAtual = new Date();
        const anoAtual = dataAtual.getFullYear();
        
        data = [];
        tabela                  = document.getElementById('tabelaPagamento');
        var dataCriacao         = $('input[name="dataCriacaoOrdemdeServico"]').val();
        vencimentoTabela        = tabela.getElementsByClassName('datapagamentoreceita');
        var idCliente           = $('#idClienteOrdemdeServico').val();
        var vendedor            = $('#vendedor').val();
        var valorProjeto        = $('input[name="valorOrdemdeServico"]').val();
        var valorOrcamento      = $('input[name="valorOrcamento"]').val();
        var percentualPermitido = $('input[name="percentualPermitido"]').val();
        var evento              = $('input[name="eventoOrdemdeServico"]').val();

        if ((dataCriacao.trim() === '') || (dataCriacao == null) || (dataCriacao == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Data Início</label></div>';
            contadorErros++;     
        }

        if ((idCliente == '') || (idCliente == null) || (idCliente == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Cliente</label></div>';
            contadorErros++;
        }

        if ((vendedor == '') || (vendedor == null) || (vendedor == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Vendedor</label></div>';
            contadorErros++;
        }

        if ((valorProjeto.trim() === '') || (valorProjeto == null) || (valorProjeto == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Valor do projeto</label></div>';
            contadorErros++;     
        }

        if ((percentualPermitido.trim() === '') || (percentualPermitido == null) || (percentualPermitido == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Perc. Permitido (mín 40%)</label></div>';
            contadorErros++;     
        }

        if ((valorOrcamento.trim() === '') || (valorOrcamento == null) || (valorOrcamento == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Valor do Orçamento</label></div>';
            contadorErros++;     
        }

        if ((evento.trim() === '') || (evento == null) || (evento == undefined)) {
            texto = texto +
            '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Evento não informado</label></div>';
            contadorErros++;     
        }

        for (var i = 0; i < vencimentoTabela.length; i++) {
            data.push(vencimentoTabela[i].value);
            vencimento = vencimentoTabela[i].value;

            @if($modoSandbox->ativo == '0' || $modoSandbox->ativo == 0)
            function addHours(numOfHours, date = new Date()) {
                date.setTime(date.getTime() + numOfHours * 60 * 60 * 1000);
                return date;
            }
            vencimentoNovo = addHours(26.999, new Date(vencimento));

            if (vencimentoNovo < dataAtual) {
                texto = texto +
                '<div class="badge-left mb-1"><span class="badge badge-danger">Operação Proibida</span><label class="fontenormal pl-2">Data vencimento menor do que a data atual - linha: ' + sum([i, 1])  +'</label></div>';
                alertaErros(texto, contadorErros++);
            }
            @endif
            
            if ((vencimento == '') || (vencimento == null) || (vencimento == undefined)) {
                texto = texto +
                '<div class="badge-left mb-1"><span class="badge badge-warning">Informar</span><label class="fontenormal pl-2">Data Pagamento na linha: '+
                    sum([i, 1])  +'</label></div>';
                contadorErros++;
            }else{
                vencimento = parseInt(vencimento.slice(0, 4));
            }

            if ((vencimento < 2000) || (vencimento > 2099)) {
                texto = texto +
                    '<div class="badge-left mb-1"><span class="badge badge-danger">Alterar</span><label class="fontenormal pl-2">Vencimento Inválido (ano ' + 
                    vencimento + ') na linha ' +
                    sum([i, 1]) + '</label></div>';
                contadorErros++;
            } else if ((vencimento > 2000) && (vencimento < 2099) && (vencimento != anoAtual)) {

                textoData = textoData +
                    '<div class="badge-left mb-1"><span class="badge badge-warning">Atenção</span><label class="fontenormal pl-2">Vencimento com ano ' +
                    vencimento + ' na linha ' + sum([i, 1]) + '</label></div>';
                contadorDatasDiferentesAnoAtual++;
            }
        }
        if (contadorErros > 0) {
                alertaErros(texto, contadorErros);
            }
        else if (contadorDatasDiferentesAnoAtual > 0) {
            var areaInformeData = document.createElement("span");
            areaInformeData.innerHTML = textoData;
            Swal.fire({
                title: 'Data informada diferente do ano atual. Confirmar?',
                html: areaInformeData,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, confirmo',
                cancelButtonText: 'Não, irei alterar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#btnSalvar').attr('disabled', 'disabled');
                    $("#manipulaOS").submit();
                }
                if (result.isDenied) {
                    swal.close();
                }
            })
        }

        if (contadorDatasDiferentesAnoAtual == 0 && contadorErros == 0) {
            return contadorErros;
        }

    }
</script>