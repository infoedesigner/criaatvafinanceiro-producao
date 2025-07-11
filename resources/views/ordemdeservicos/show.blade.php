@extends('layouts.app')

<style>
    @import 'https://fonts.googleapis.com/css?family=Open+Sans:600,700';

    * {
        font-family: 'Open Sans', sans-serif;
    }

    .rwd-table {
        margin: auto;
        min-width: 300px;
        max-width: 100%;
        border-collapse: collapse;
    }

    .rwd-table tr:first-child {
        border-top: none;
        /* background: #428bca; */
        color: #fff;
    }

    .rwd-table tr {
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        background-color: #f5f9fc;
    }

    .rwd-table tr:nth-child(odd):not(:first-child) {
        background-color: #ebf3f9;
    }


    .rwd-table th {
        display: none;
    }

    .rwd-table td {
        display: block;
    }

    .rwd-table td:first-child {
        margin-top: .5em;
    }

    .rwd-table td:last-child {
        margin-bottom: .5em;
    }

    .rwd-table td:before {
        content: attr(data-th) ": ";
        font-weight: bold;
        width: 100%;
        display: inline-block;
        color: #000;
    }

    .rwd-table th,
    .rwd-table td {
        text-align: left;
    }

    .rwd-table {
        color: #333;
        border-radius: .4em;
        overflow: hidden;
    }

    .rwd-table tr {
        border-color: #bfbfbf;
    }

    .rwd-table th,
    .rwd-table td {
        padding: .5em 1em;
    }

    @media screen and (max-width: 601px) {
        .rwd-table tr:nth-child(2) {
            border-top: none;
        }

    }

    @media screen and (min-width: 600px) {
        .rwd-table tr:hover:not(:first-child) {
            background-color: #d8e7f3;
        }

        .rwd-table td:before {
            display: none;
        }

        .rwd-table th,
        .rwd-table td {
            display: table-cell;
            padding: .25em .5em;
        }

        .rwd-table th:first-child,
        .rwd-table td:first-child {
            padding-left: 0;
        }

        .rwd-table th:last-child,
        .rwd-table td:last-child {
            padding-right: 0;
        }

        .rwd-table th,
        .rwd-table td {
            padding: 1em !important;
        }
    }


    /* THE END OF THE IMPORTANT STUFF */

    /* Basic Styling */
    body {
        background: #4B79A1;
        background: -webkit-linear-gradient(to left, #4B79A1, #283E51);
        background: linear-gradient(to left, #4B79A1, #283E51);
    }

    h1 {
        text-align: center;
        font-size: 2.4em;
        color: #f2f2f2;
    }

    .container {
        display: block;
        text-align: center;
    }

    h3 {
        display: inline-block;
        position: relative;
        text-align: center;
        font-size: 1.5em;
        color: #cecece;
    }

    /* h3:before {
      content: "\25C0";
      position: absolute;
      left: -50px;
      -webkit-animation: leftRight 2s linear infinite;
      animation: leftRight 2s linear infinite;
    }
    h3:after {
      content: "\25b6";
      position: absolute;
      right: -50px;
      -webkit-animation: leftRight 2s linear infinite reverse;
      animation: leftRight 2s linear infinite reverse;
    } */
    @-webkit-keyframes leftRight {
        0% {
            -webkit-transform: translateX(0)
        }

        25% {
            -webkit-transform: translateX(-10px)
        }

        75% {
            -webkit-transform: translateX(10px)
        }

        100% {
            -webkit-transform: translateX(0)
        }
    }

    @keyframes leftRight {
        0% {
            transform: translateX(0)
        }

        25% {
            transform: translateX(-10px)
        }

        75% {
            transform: translateX(10px)
        }

        100% {
            transform: translateX(0)
        }
    }


    .delete {
        color: red;
        background-color: white;
        border-radius: 7%;
        padding: 3%;
    }


    table td::before {
        content: attr(data-label) !important;
        float: left !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        white-space: nowrap;
    }

    tr {
        background: white !important;
        border-color: black !important;
        border-top: 50px !important;
        /* box-shadow: 0 1rem 3rem rgb(0 0 0 / 50%) !important; */

    }


    tr:nth-child(2n+1) {
        background: #ebf3f9 !important;
        border-color: black !important;
        border-top: 50px !important;
        /* box-shadow: 0 1rem 3rem rgb(0 0 0 / 50%) !important; */
    }
</style>


@section('content')
    <link href="https://nightly.datatables.net/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
    <script src="https://nightly.datatables.net/js/jquery.dataTables.js"></script>
    <style>
        .shadowDiv {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .5) !important;
        }
    </style>

    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2 class="text-center"> Dados da OS: </h2>
                <h1 class="text-center" style="margin-top:-6px !important; color: red;">{{ $ordemdeservico->id }} -
                    {{ $ordemdeservico->eventoOrdemdeServico }}</h1>

                <div class="form-row d-flex justify-content-center mt-2">

                    <a class="btn btn-danger d-flex justify-content-center" href="{{ route('ordemdeservicos.index') }}">
                        Voltar</a>

                    @can('ordemdeservico-edit')
                        <a class="btn btn-primary d-flex justify-content-center"
                            href="{{ route('ordemdeservicos.edit', $ordemdeservico->id) }}">Editar</a>
                    @endcan
                    {{-- <form action="{{ route('ordemdeservicos.destroy', $ordemdeservico->id) }}" method="POST"> 
                                @csrf
                                @method('DELETE')
                                @can('ordemdeservico-delete')
                                <button type="submit" class="btn btn-danger">Excluir</button>
                                @endcan
                            </form>
                        --}}
                </div>
                <hr />
            </div>
        </div>
    </div>





    <script>
        $(document).ready(function() {
            sum();
            $("#num1, #num2").on("keydown keyup", function() {
                sum();
            });
        });

        function formatarValor(valor) {
            return valor.toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
        }

        function sum() {

            var num1 = document.getElementById('num1').value;
            var num2 = document.getElementById('num2').value;
            var num3 = document.getElementById('num3').value;
            var result = (parseFloat(num1) * 100 + parseFloat(num2) * 100) / 100;
            var result1 = (((parseFloat(num2) * 100 - parseFloat(num1) * 100) / 100) * -1);


            var receita = (formatarValor(parseFloat(num3)));
            var despesa = (formatarValor(parseFloat(num2)));
            var lucro = (formatarValor(result1));
            if (!isNaN(result)) {
                document.getElementById('subt').value = lucro;
                document.getElementById('despesa').value = despesa;
                document.getElementById('receita').value = receita;

            }
        }
    </script>

    <div class="d-flex justify-content-center">
        <?php $contadorReceitas = count($receitasPorOS);
        $readonlyOrNo = 'readonly';
        $disabledOrNo = 'disabled';
        ?>


        @if ($porcentagemReceitaAPagar == 0.0 && $contadorReceitas == 0)
            <div class="alert alert-danger col-sm-12" role="alert">
                Não há receitas para esta OS!
            </div>
        @elseif ($porcentagemReceitaAPagar == 0.0 && $contadorReceitas > 0)
            <h3 class="" style="color:blue;">100% PAGA</h3>
        @else
            <h3 style="color:red;"> {{ $porcentagemReceitaAPagar }} % A receber </h3>
        @endif
    </div>
    <div class="d-flex justify-content-center">
        <h3 class="text-center">Resumo Financeiro</h3>
    </div>
    <div class="shadowDiv p-3 mb-2 bg-white rounded row d-flex justify-content-center  pt-2 text-lg-center"
        style="background-color: black !important; color:white;">

        <form name="form1" method="post" action="">
            <div class="row">

                <div class="shadowDiv bg-white rounded col-sm-5 p-2 mb-3" style="background-color: white !important;">
                    <input class="form-control" type="hidden" name="num1" id="num1"
                        value="{{ $ordemdeservico->valorOrdemdeServico }}" readonly />

                    <div class="row ml-0">
                        <label class="btn badge-primary col-sm-4 mr-2" style="cursor: unset;">Receita</label>
                        <input class="form-control col-sm-4 mr-2" style="text-align:center;" type="text"
                            value="{{ $totalreceitas }}" readonly />
                        <label class="btn badge-primary col-sm-3" style="cursor: unset;">{{ $porcentagemReceita }}
                            %</label>
                    </div>

                    <div class="row ml-0">
                        <label class="btn badge-danger col-sm-4 mr-2" style="background-color:#FA8E3B; cursor: unset;">Desp.
                            Paga</label>
                        <input class="form-control col-sm-4 mr-2" style="text-align:center;" type="text"
                            value="{{ $totaldespesasPagas }}" readonly />
                        <label class="btn badge-danger col-sm-3"
                            style="background-color:#FA8E3B; cursor: unset;">{{ $porcentagemDespesaPagas }}
                            %</label>
                    </div>
                    <div class="row ml-0">
                        <label class="btn badge-danger col-sm-4 mr-2" style="cursor: unset;">Desp. A Pagar</label>
                        <input class="form-control col-sm-4 mr-2" style="text-align:center;" type="text"
                            value="{{ $totaldespesasAPagar }}" readonly />
                        <label class="btn badge-danger col-sm-3" style="cursor: unset;">{{ $porcentagemDespesaAPagar }}
                            %</label>
                    </div>

                </div>


                <div class="shadowDiv bg-white rounded col-sm-5 p-2 mb-3" style="background-color: white !important;">
                    <input class="form-control" type="hidden" name="num1" id="num1"
                        value="{{ $ordemdeservico->valorOrdemdeServico }}" readonly />

                    <div class="row ml-0">
                        <label class="btn badge-primary col-sm-4 mr-2" style="cursor: unset;">A Receber</label>
                        <input class="form-control col-sm-4 mr-2" style="text-align:center;" type="text"
                            value="{{ $totalreceitasAPagar }}" readonly />
                        <label class="btn badge-primary col-sm-3" style="cursor: unset;">{{ $porcentagemReceitaAPagar }}
                            %</label>
                    </div>

                    <div class="row ml-0">
                        <label class="btn badge-danger col-sm-4 mr-2" style="cursor: unset;">Total. Desp</label>
                        <input class="form-control col-sm-4 mr-2" style="text-align:center;" type="text"
                            value="{{ $totaldespesas }}" readonly />
                        <label class="btn badge-danger col-sm-3" style="cursor: unset;">{{ $porcentagemDespesa }}
                            %</label>
                    </div>
                    <div class="row ml-0">
                        <label class="btn badge-success col-sm-4 mr-2" style="cursor: unset;">Lucro</label>
                        <input class="form-control col-sm-4 mr-2" style="text-align:center;" type="text"
                            value="{{ $lucro }}" readonly />
                        <label class="btn badge-success col-sm-3" style="cursor: unset;">{{ $porcentagemLucro }}
                            %</label>
                    </div>
                </div>
            </div>

    </div>

    <div class="shadowDiv p-3 mb-5 bg-white rounded pt-2 text-lg-center"
        style="background-color: white !important; color:black;">


        <div class="form-group row mb-0">
            <label for="idClienteOrdemdeServico" class="col-sm-2 col-form-label">Cliente</label>
            <div class="col-sm-6">
                <label class="form-control">
                    @foreach ($cliente as $listaCliente)
                        {{ $listaCliente->razaosocialCliente }}
                    @endforeach
                </label>

            </div>
        </div>


        <div class="form-group row mb-0">
            <label for="dataVendaOrdemdeServico" class="col-sm-2 col-form-label">Data Venda</label>
            <div class="col-sm-6">
                <label
                    class="col-sm-4 form-control">{{ date('d/m/Y', strtotime($ordemdeservico->dataCriacaoOrdemdeServico)) }}</label>
            </div>

            <label for="valorOrdemdeServico" class="col-sm-2 col-form-label">Valor do Projeto</label>
            <div class="col-sm-2">
                <label class="form-control">{{ $totalOS }}</label>

            </div>
        </div>

        <div class="form-group row mb-0">
            <label for="eventoOrdemdeServico" class="col-sm-2 col-form-label">Evento</label>
            <div class="col-sm-10">
                <label class="form-control">{{ $ordemdeservico->eventoOrdemdeServico }}</label>

            </div>
        </div>

        <div class="form-group row mb-0">
            <label for="obsOrdemdeServico" class="col-sm-2 col-form-label">Observação</label>
            <div class="col-sm-10">
                <label class="form-control">{{ $ordemdeservico->obsOrdemdeServico }}</label>

            </div>
        </div>
    </div>

    <div>
        <h4 class="text-center">Parcelas</h4>
    </div>

    <hr />
    <br>

    <table class="styled-table rwd-table" id="tabelaPagamento">
        <thead>
            <tr class="col-sm-10" style="background-color:#3490dc !important;">
                {{-- <input style="cursor: pointer;" id="btnAddColumn" class="btn btn-primary" value="Adicionar Parcela"
                    readonly> --}}
                {{-- <input onclick="removerCampos()" class="btn btn-danger" value="Remover Parcelas" readonly
                    style="cursor:pointer;"> --}}

                <th title="Forma Pagamento" class="col-sm-2" style="width:20%;">
                    <nobr>Forma Pagamento</nobr>
                </th>
                <th data-label="Valor Parcela" class="col-sm-1" style="width:-webkit-fill-available;">
                    <nobr>Valor Parcela</nobr>
                </th>
                <th class="col-sm-1" style="width:-webkit-fill-available;">Pago</th>
                <th class="col-sm-2" style="width:-webkit-fill-available;">Data Emissão NF</th>
                <th class="col-sm-2" style="width:-webkit-fill-available;">Data de Pagamento</th>
                <th class="col-sm-1" style="width:-webkit-fill-available;">Conta</th>
                <th class="col-sm-1" style="width:-webkit-fill-available;">
                    <nobr>Nota Fiscal<nobr>
                </th>

            </tr>
        </thead>
        <tbody id="habilita_receita" class="habilita_receita">
            <?php $contador = count($receitasPorOS); ?>
            @if ($contador > 0)
                @foreach ($receitasPorOS as $dadosreceita)
                    @if ($dadosreceita->valorreceita != '0.00' && $dadosreceita->valorreceita != '0,00')
                        @include('ordemdeservicos/trview')
                    @endif
                @endforeach
            @else
                <div class="alert alert-danger" role="alert">
                    Não há receitas para esta OS!
                </div>
            @endif

        </tbody>
    </table>

    <script>
        function createFilter(table, columns) {

            $.fn.dataTable.ext.search.push(function(
                settings,
                searchData,
                index,
                rowData,
                counter
            ) {
                var val = input.val().toLowerCase();

                for (var i = 0, ien = columns.length; i < ien; i++) {
                    if (searchData[columns[i]].toLowerCase().indexOf(val) !== -1) {
                        return true;
                    }
                }

                return false;
            });

            return input;
        }

        $(document).ready(function() {


            $("#tabelaPercentualPorOS").DataTable({
                "language": {
                    "lengthMenu": "Exibindo _MENU_ registros por página",
                    "zeroRecords": "Nenhum dado cadastrado",
                    "info": "Exibindo página _PAGE_ de _PAGES_",
                    "infoEmpty": "Nenhum registro encontrado",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "search": "Pesquisar",
                    "paginate": {
                        "previous": "Anterior",
                        "next": "Próximo",
                    },
                },
            })


            var table = $("#tabelaPercentualPorOS").DataTable();

        });
    </script>



@endsection
