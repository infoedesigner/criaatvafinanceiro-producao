<?php 
    $intervaloCelulas = "A1:F1"; 
    $rotaapi = "apientradareceitarecebidas";
    $titulo  = "Receitas Recebidas Diversas";
    $campodata = 'datapagamentoreceita';
    $relatorioKendoGrid = true;

?>


<head>
    <meta charset="utf-8">
    <title>{{$titulo}}</title>
</head>

@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2 class="text-center">Relatório de {{$titulo}}</h2>
        </div>
    </div>
</div>

@include('layouts/helpersview/mensagemRetorno')



<div id="filter-menu"></div>
<br /><br />
<div id="grid"></div>

<script>

    @include('layouts/helpersview/iniciotabela')
    @can('rel-entradasdereceitasrecebidas')

            dataSource: {
                data: data,
                pageSize: 15,
                schema: {
                    model: {
                        fields: {
                            valorreceita: { type: "number" },
                            datapagamentoreceita: { type: "date"},
                            idosreceita: { type: "string"},
                            descricaoreceita: { type: "string"},
                            nomeFormaPagamento: { type: "string"},
                            nomeConta: { type: "string"},
                        }
                    },
                },

                group: [{
                    field: "cliente", dir: "asc", aggregates: [
                        { field: "valorreceita", aggregate: "sum" } 
                    ],
                }],

                aggregate: [
                { field: "valorreceita", aggregate: "sum" }],

            },
                        
            columns: [
                { field: "descricaoreceita", title: "Descrição da Receita", filterable: true, width: 100 },
                { field: "datapagamentoreceita", title: "Vencimento", filterable: true, width: 85, format: "{0:dd/MM/yyyy}", filterable: { cell: { template: betweenFilter}} },
                { field: "nomeFormaPagamento", title: "Forma Pagamento", filterable: true, width: 120 },
                { field: "valorreceita", title: "Valor", filterable: true, width: 80, decimals: 2, aggregates: ["sum"], groupHeaderColumnTemplate: "Total na conta: #: kendo.toString(sum, 'c', 'pt-BR') #", footerTemplate: "Total Geral: #: kendo.toString(sum, 'c', 'pt-BR') #", format: '{0:0.00}' },
                { field: "cliente", title: "Cliente", filterable: true, width: 60 },
                { field: "nomeConta", title: "Conta", filterable: true, width: 60 }
            ],
            @include('layouts/helpersview/finaltabela')
            @include('layouts/filtradata')


</script>


@else  
@include('layouts/helpersview/finalnaoautorizado')
@endcan
@endsection