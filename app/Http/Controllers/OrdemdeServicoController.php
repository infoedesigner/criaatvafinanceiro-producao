<?php


namespace App\Http\Controllers;

use App\OrdemdeServico;
use App\Receita;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\FormatacoesServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use DataTables;
use App\Classes\Logger;
use App\Despesa;
use Gate;



class OrdemdeServicoController extends Controller
{

    private $Logger;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:ordemdeservico-list|ordemdeservico-create|ordemdeservico-edit|ordemdeservico-delete', ['only' => ['index']]);
        $this->middleware('permission:ordemdeservico-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ordemdeservico-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ordemdeservico-delete', ['only' => ['destroy']]);
        $this->middleware('permission:ordemdeservico-show', ['only' => ['show']]);

        $this->acao =  request()->segment(count(request()->segments()));
        $this->valorInput = null;
        $this->valorSemCadastro = null;

        $this->Logger = new Logger();

        if ($this->acao == 'create') {
            $this->valorInput = " ";
            $this->valorSemCadastro = "0";
            $this->variavelReadOnlyNaView = "";
            $this->variavelDisabledNaView = "";
        } elseif ($this->acao == 'edit') {
            $this->variavelReadOnlyNaView = "";
            $this->variavelDisabledNaView = "";
        } elseif ($this->acao != 'edit' && $this->acao != 'create') {
            $this->variavelReadOnlyNaView = "readonly";
            $this->variavelDisabledNaView = 'disabled';
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $consulta = $this->consultaIndexOrdemServicos();

        if ($request->ajax()) {

            return Datatables::of($consulta)
                ->addIndexColumn()
                ->addColumn('action', function ($consulta) {


                    $btnVisualizar = Gate::allows('ordemdeservico-show') ? '<a href="ordemdeservicos/' . $consulta->id . '" class="col-sm-6 edit btn btn-primary btn-sm" title="Visualizar Financeiro"><i style="color:white;" class="fa fa-eye" aria-hidden="true"></i></a>' : '';
                    $btnEditar     = Gate::allows('ordemdeservico-edit') ? '<a href="ordemdeservicos/' . $consulta->id . '/edit" class="col-sm-6 btn btn-primary btn-sm" title="Editar OS"><i class="fa fa-edit" aria-hidden="true"></i></a>' : '';

                    $grupoBtn = '<div class="row col-sm-12">' . $btnVisualizar . $btnEditar . '</div>';

                    return $grupoBtn;
                })

                ->rawColumns(['action'])
                ->make(true);
        } else {
            $data = Receita::orderBy('id', 'DESC')->paginate(5);
            return view('ordemdeservicos.index', compact('data'))
                ->with('i', ($request->input('page', 1) - 1) * 5);
        }
    }

    public function tabelaOrdemServicos(Request $request)
    {

        $consulta = $this->consultaIndexOrdemServicos();


        return Datatables::of($consulta)
            ->filter(function ($query) use ($request) {


                if (($request->has('id')) && ($request->id != NULL)) {
                    $query->where('ordemdeservico.id', '=', "{$request->get('id')}");
                }
                if (($request->has('idClienteOrdemdeServico')) && ($request->idClienteOrdemdeServico != NULL)) {
                    $query->where('idClienteOrdemdeServico', '=', "{$request->get('idClienteOrdemdeServico')}");
                }
                if (($request->has('eventoOrdemdeServico')) && ($request->eventoOrdemdeServico != NULL)) {
                    $query->where('eventoOrdemdeServico', '=', "{$request->get('eventoOrdemdeServico')}");
                }

                if (($request->has('valorOrdemdeServico')) && ($request->valorOrdemdeServico != NULL)) {
                    $query->where('valorOrdemdeServico', '=', "{$request->get('valorOrdemdeServico')}");
                }
            })
            ->addIndexColumn()
            ->addColumn('action', function ($consulta) {

                $btnVisualizar = Gate::allows('ordemdeservico-show') ? '<a href="ordemdeservicos/' . $consulta->id . '" class="col-sm-6 edit btn btn-primary btn-sm" title="Visualizar Financeiro"><i style="color:white;" class="fa fa-eye" aria-hidden="true"></i></a>' : '';
                $btnEditar     = Gate::allows('ordemdeservico-edit') ? '<a href="ordemdeservicos/' . $consulta->id . '/edit" class="col-sm-6 btn btn-primary btn-sm" title="Editar OS"><i class="fa fa-edit" aria-hidden="true"></i></a>' : '';

                $grupoBtn = '<div class="row col-sm-12">' . $btnVisualizar . $btnEditar . '</div>';

                return $grupoBtn;
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public function lastOS() {
        return OrdemdeServico::selectRaw('id + 1 as id')
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
    public function apiOS(Request $request, $id = null)
    {
        $query = OrdemdeServico::where('ativoOrdemdeServico', 1);

        if ($id) {
            $query->where('id', $id);
        }

        $listaOS = $query->with(['cliente:id,razaosocialCliente'])
            ->get();

        $listaOS = $listaOS->map(function ($item) use ($id) {
            if ($item->relationLoaded('cliente') && $item->cliente->isNotEmpty()) {
                $item->razaosocialCliente = $item->cliente->first()->razaosocialCliente;

                if ($id) {
                    $totals = DB::table('receita')
                        ->selectRaw('
                        SUM(CASE WHEN idosreceita = ? THEN valorreceita ELSE 0 END) as totalReceitaPorOS,
                        SUM(CASE WHEN idosreceita = ? AND ativoreceita = 1 AND excluidoreceita = 0 AND pagoreceita = "S" THEN valorreceita ELSE 0 END) as totalReceitasPagas,
                        SUM(CASE WHEN idosreceita = ? AND ativoreceita = 1 AND excluidoreceita = 0 AND pagoreceita = "N" THEN valorreceita ELSE 0 END) as totalReceitasNaoPagas,
                        (SELECT SUM(precoReal) FROM despesas WHERE ativoDespesa = 1 and excluidoDespesa = 0 and idOS = ?) as totalDespesas,
                        (SELECT SUM(precoReal) FROM despesas WHERE ativoDespesa = 1 and excluidoDespesa = 0 and idOS = ? AND pago = "S") as totalDespesasPagas,
                        (SELECT SUM(precoReal) FROM despesas WHERE ativoDespesa = 1 and excluidoDespesa = 0 and idOS = ? AND pago = "N") as totalDespesasNaoPagas,
                        (SUM(CASE WHEN idosreceita = ? THEN valorreceita ELSE 0 END) * 100) / ? as percReceita,
                        ((SELECT SUM(precoReal) FROM despesas WHERE ativoDespesa = 1 and excluidoDespesa = 0 and idOS = ?) * 100) / ? as percDespesa,
                        (SUM(CASE WHEN idosreceita = ? AND ativoreceita = 1 AND excluidoreceita = 0 AND pagoreceita = "N" THEN valorreceita ELSE 0 END) * 100) / ? as percReceitasNaoPagas,
                        ((SELECT SUM(precoReal) FROM despesas WHERE ativoDespesa = 1 and excluidoDespesa = 0 and idOS = ? AND pago = "N") * 100) / ? as percDespesasNaoPagas,
                        (SUM(CASE WHEN idosreceita = ? AND ativoreceita = 1 AND excluidoreceita = 0 AND pagoreceita = "S" THEN valorreceita ELSE 0 END) * 100) / ? as percReceitasPagas,
                        ((SELECT SUM(precoReal) FROM despesas WHERE ativoDespesa = 1 and excluidoDespesa = 0 and idOS = ? AND pago = "S") * 100) / ? as percDespesasPagas
                    ', [
                            $item->id,
                            $item->id,
                            $item->id, // Para os valores de receitas
                            $item->id,
                            $item->id,
                            $item->id, // Para os valores de despesas
                            $item->id,
                            $item->valorOrdemdeServico, // Para percentuais de receitas
                            $item->id,
                            $item->valorOrdemdeServico, // Para percentuais de despesas
                            $item->id,
                            $item->valorOrdemdeServico, // Para percentuais de receitas não pagas
                            $item->id,
                            $item->valorOrdemdeServico, // Para percentuais de despesas não pagas
                            $item->id,
                            $item->valorOrdemdeServico, // Para percentuais de receitas pagas
                            $item->id,
                            $item->valorOrdemdeServico  // Para percentuais de despesas pagas
                        ])
                        ->first();

                    // Atribui os valores calculados
                    $item->totalReceitaPorOS        = FormatacoesServiceProvider::validaValoresParaView($totals->totalReceitaPorOS);
                    $item->totalReceitasPagas       = FormatacoesServiceProvider::validaValoresParaView($totals->totalReceitasPagas);
                    $item->totalReceitasNaoPagas    = FormatacoesServiceProvider::validaValoresParaView($totals->totalReceitasNaoPagas);
                    $item->totalDespesas            = FormatacoesServiceProvider::validaValoresParaView($totals->totalDespesas);
                    $item->totalDespesasPagas       = FormatacoesServiceProvider::validaValoresParaView($totals->totalDespesasPagas);
                    $item->totalDespesasNaoPagas    = FormatacoesServiceProvider::validaValoresParaView($totals->totalDespesasNaoPagas);


                    $item->percReceita          = number_format($totals->percReceita ?? 0, 2);
                    $item->percDespesa          = number_format($totals->percDespesa ?? 0, 2);
                    $item->percReceitasNaoPagas = number_format($totals->percReceitasNaoPagas ?? 0, 2);
                    $item->percDespesasNaoPagas = number_format($totals->percDespesasNaoPagas ?? 0, 2);
                    $item->percReceitasPagas    = number_format($totals->percReceitasPagas ?? 0, 2);
                    $item->percDespesasPagas    = number_format($totals->percDespesasPagas ?? 0, 2);

                    // Cálculo do valorReceitaReal
                    $valorReceitaReal = $totals->totalReceitaPorOS - $totals->totalDespesas;

                    // Formatação do valorReceitaReal
                    // $item->valorReceitaReal = number_format($valorReceitaReal, 2);
                    $item->valorReceitaReal         = FormatacoesServiceProvider::validaValoresParaView($valorReceitaReal);

                    // Cálculo do perLucro com duas casas decimais, garantindo que $item->valorOrdemdeServico seja um número
                    $valorOrdemdeServico = floatval($item->valorOrdemdeServico ?? 1); // Evita divisão por zero
                    $item->perLucro = number_format(($valorReceitaReal * 100) / $valorOrdemdeServico, 2);
                    
                }

                unset($item->cliente);
            }
            return $item;
        });

        return response()->json($listaOS, 200);
    }

    public function consultaIndexOrdemServicos()
    {
        $consulta = DB::table('ordemdeservico')
            ->leftJoin('clientes', 'idClienteOrdemdeServico', 'clientes.id')

            ->select(['ordemdeservico.id', 'ordemdeservico.idClienteOrdemdeServico', 'ordemdeservico.dataVendaOrdemdeServico', 'ordemdeservico.valorOrdemdeServico', 'ordemdeservico.dataOrdemdeServico', 'clientes.id as idcliente', 'clientes.razaosocialCliente', 'ordemdeservico.eventoOrdemdeServico', 'ordemdeservico.servicoOrdemdeServico', 'ordemdeservico.obsOrdemdeServico', 'ordemdeservico.dataCriacaoOrdemdeServico', 'ordemdeservico.dataExclusaoOrdemdeServico', 'ordemdeservico.ativoOrdemdeServico', 'ordemdeservico.excluidoOrdemdeServico']);
        return $consulta;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $ultimaOS = DB::select('select max(id) idMaximo from ordemdeservico');
        if (isset($ultimaOS)) {
            $ultimaOSParseInt = $ultimaOS[0]->idMaximo;
            $novaOS = $ultimaOSParseInt + 1;
        }

        $listaContas            = DB::select('select id,apelidoConta, nomeConta from conta where ativoConta = 1');
        $listaForncedores       = DB::select('select id,nomeFornecedor, razaosocialFornecedor, contatoFornecedor from fornecedores where ativoFornecedor = 1');
        $cliente                = DB::select('select id, nomeCliente, razaosocialCliente from clientes where ativoCliente = 1');
        $formapagamento         = DB::select('select id,nomeFormaPagamento from formapagamento where ativoFormaPagamento = 1');
        $codigoDespesa          = DB::select('select id, idGrupoCodigoDespesa, despesaCodigoDespesa from codigodespesas where ativoCodigoDespesa = 1');
        $vendedores             = $listaForncedores;
        $dataInicio             = date("d/m/Y");

        $valorInput             = $this->valorInput;
        $valorSemCadastro       = $this->valorSemCadastro;
        $variavelReadOnlyNaView = $this->variavelReadOnlyNaView;
        $variavelDisabledNaView = $this->variavelDisabledNaView;

        return view('ordemdeservicos.create', compact('novaOS', 'cliente', 'formapagamento', 'listaContas', 'listaForncedores', 'codigoDespesa', 'ultimaOS', 'vendedores', 'dataInicio', 'valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function apiStore(Request $request)
    {
        $ordemdeservico = new OrdemdeServico();
        
        
        $request->validate([
            'idClienteOrdemdeServico'   => 'required',
            'eventoOrdemdeServico'      => 'required|min:3',
        ]);

        $ordemdeservico->dataCriacaoOrdemdeServico  = $request->dataCriacaoOrdemdeServico;
        $ordemdeservico->eventoOrdemdeServico       = $request->eventoOrdemdeServico;
        $ordemdeservico->fatorR                     = $request->fatorR;
        $ordemdeservico->idClienteOrdemdeServico    = $request->idClienteOrdemdeServico;
        $ordemdeservico->percentualPermitido        = $request->percentualPermitido;
        $ordemdeservico->valorOrcamento             = $request->valorOrcamento;
        $ordemdeservico->valorOrdemdeServico        = $request->valorOrdemdeServico;
        $ordemdeservico->servicoOrdemdeServico      = 'Campo Serviço';
        $ordemdeservico->vendedor                   = $request->vendedor;
        // $ordemdeservico->update($request->all());
        $salvaOS = $ordemdeservico->save($request->all());
        $idDaOS = $ordemdeservico->id;
        
        // Verifica se existem receitas a serem processadas
        $receitas = $request->get('receitas', []);

        foreach ($receitas as $index => $receitaData) {
            // Validação específica de cada receita
            $request->validate([
                "receitas.$index.idformapagamentoreceita" => 'required',
                "receitas.$index.datapagamentoreceita"    => 'required',
                "receitas.$index.pagoreceita"             => 'required',
                "receitas.$index.contareceita"            => 'required',
                "receitas.$index.nfreceita"               => 'required',
            ]);

            $receita = isset($receitaData['idReceita']) && $receitaData['idReceita'] != 0
                ? Receita::find($receitaData['idReceita'])
                : new Receita();

            // Ajuste para validar e formatar o valor monetário
            // $valorReceita = FormatacoesServiceProvider::validaValoresParaBackEnd($receitaData['valorreceita']);

            // Atribui os valores para a receita
            $receita->fill([
                'idformapagamentoreceita' => $receitaData['idformapagamentoreceita'],
                'datapagamentoreceita'    => $receitaData['datapagamentoreceita'],
                'dataemissaoreceita'      => $receitaData['dataemissaoreceita'],
                'valorreceita'            => $receitaData['valorreceita'],
                'pagoreceita'             => $receitaData['pagoreceita'],
                'contareceita'            => $receitaData['contareceita'],
                'nfreceita'               => $receitaData['nfreceita'],
                'descricaoreceita'        => $request->get('eventoOrdemdeServico'),
                'idclientereceita'        => $request->get('idClienteOrdemdeServico'),
                'excluidoreceita'         => $receitaData['excluidoreceita'],
                'idosreceita'             => $idDaOS,
                'ativoreceita'            => $receitaData['excluidoreceita'] == '1' ? 0 : 1,
            ]);

            $receita->save();
        }

        // Atualiza os valores principais da Ordem de Serviço
        // $request['valorOrdemdeServico'] = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorOrdemdeServico'));
        // $request['valorOrcamento']      = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorOrcamento'));


        $this->logVisualizaOS($ordemdeservico);
        return response()->json($ordemdeservico, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ordemdeservico = new OrdemdeServico();
        $temReceita = $request->get('idformapagamentoreceita');
        $fatorR = $request->get('fatorR');


        $tamanhoArrayReceita = count($temReceita);
        $request->validate([
            'idClienteOrdemdeServico'        => 'required',
            'valorOrdemdeServico'            => 'required|min:3',
            'eventoOrdemdeServico'           => 'required|min:2',
            'dataCriacaoOrdemdeServico'      => 'required|min:3',

        ]);

        $ordemdeservico->idClienteOrdemdeServico          = $request->get('idClienteOrdemdeServico');
        $ordemdeservico->vendedor                         = $request->get('vendedor');
        $ordemdeservico->valorProjetoOrdemdeServico       = $request->get('valorProjetoOrdemdeServico');
        $ordemdeservico->valorOrdemdeServico              = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorOrdemdeServico'));
        $ordemdeservico->dataOrdemdeServico               = $request->get('dataOrdemdeServico');
        // $ordemdeservico->clienteOrdemdeServico            = $request->get('clienteOrdemdeServico');
        $ordemdeservico->eventoOrdemdeServico             = $request->get('eventoOrdemdeServico');

        $ordemdeservico->percentualPermitido              = $request->get('percentualPermitido');
        $ordemdeservico->valorOrcamento                   = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorOrcamento'));

        $ordemdeservico->servicoOrdemdeServico            = $request->get('servicoOrdemdeServico');
        $ordemdeservico->obsOrdemdeServico                = $request->get('obsOrdemdeServico');
        $ordemdeservico->dataCriacaoOrdemdeServico        = $request->get('dataCriacaoOrdemdeServico');
        $ordemdeservico->fatorR                           = $fatorR;
        $ordemdeservico->ativoOrdemdeServico              = $request->get('ativoOrdemdeServico');
        $ordemdeservico->excluidoOrdemdeServico           = $request->get('excluidoOrdemdeServico');

        $salvaOS = $ordemdeservico->save();
        $idDaOS = $ordemdeservico->id;

        if ($temReceita != '0') {

            for ($i = 0; $i < $tamanhoArrayReceita; $i++) {
                $receita = new Receita();
                $request->validate([
                    'idformapagamentoreceita'       => 'required',
                    'datapagamentoreceita'          => 'required',
                    'valorreceita'                  => 'required',
                    'pagoreceita'                   => 'required',
                    'contareceita'                  => 'required',
                    'nfreceita'                     => 'required',

                ]);
                $receita->descricaoreceita              = $ordemdeservico->eventoOrdemdeServico;
                $receita->idclientereceita              = $ordemdeservico->idClienteOrdemdeServico;
                $receita->idformapagamentoreceita       = $request->get('idformapagamentoreceita')[$i];
                $receita->datapagamentoreceita          = $request->get('datapagamentoreceita')[$i];
                $receita->dataemissaoreceita            = $request->get('dataemissaoreceita')[$i];
                $receita->valorreceita                  = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorreceita')[$i]);
                $receita->pagoreceita                   = $request->get('pagoreceita')[$i];
                $receita->contareceita                  = $request->get('contareceita')[$i];
                $receita->registroreceita               = $request->get('registroreceita');
                $receita->nfreceita                     = $request->get('nfreceita')[$i];

                $receita['idosreceita']                 = "$idDaOS";

                // $receita->save();

                $novaReceitaOS = Receita::create([
                    'idformapagamentoreceita'   => $receita->idformapagamentoreceita,
                    'descricaoreceita'          => $request->eventoOrdemdeServico,
                    'idclientereceita'          => $request->idClienteOrdemdeServico,
                    'datapagamentoreceita'      => $receita->datapagamentoreceita,
                    'dataemissaoreceita'        => $receita->dataemissaoreceita,
                    'valorreceita'              => $receita->valorreceita,
                    'pagoreceita'               => $receita->pagoreceita,
                    'contareceita'              => $receita->contareceita,
                    'registroreceita'           => $receita->registroreceita,
                    'nfreceita'                 => $receita->nfreceita,
                    'idosreceita'               => "$idDaOS"
                ]);


                $idReceita = $receita->id;
                $this->logCadastraReceitaOS($idReceita);
            }
        }

        $this->logCadastraOS($ordemdeservico);
        if ($temReceita != '0') {
            return redirect()->route('ordemdeservicos.index')
                ->with('success', 'Ordem de Serviço n°' . $idDaOS  . ' com ' . $tamanhoArrayReceita . ' parcela(s) cadastrada(s) com êxito.');
        } else if ($temReceita === '0') {
            return redirect()->route('ordemdeservicos.index')
                ->with('success', 'Ordem de Serviço n°' . $idDaOS . ' cadastrada com êxito. Não foram cadastradas receitas.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OrdemdeServico  $ordemdeservico
     * @return \Illuminate\Http\Response
     */
    public  function show($id)
    {

        $ordemdeservico = OrdemdeServico::find($id);
        $dataInicio = $ordemdeservico->dataCriacaoOrdemdeServico;

        $listaContas = DB::select('select id,apelidoConta, nomeConta from conta where ativoConta = 1');
        $listaForncedores = DB::select('select id,nomeFornecedor, razaosocialFornecedor, contatoFornecedor from fornecedores where ativoFornecedor = 1');
        $vendedores     = $listaForncedores;
        $formapagamento = DB::select('select id,nomeFormaPagamento from formapagamento where ativoFormaPagamento = 1 and id = :idFormaPagamento', ['idFormaPagamento' => $ordemdeservico->nomeFormaPagamento]);
        $codigoDespesa = DB::select('select id, idGrupoCodigoDespesa, despesaCodigoDespesa from codigodespesas where ativoCodigoDespesa = 1');

        $receitasPorOS = DB::select('select distinct id as idReceita, idosreceita, idclientereceita,idformapagamentoreceita,datapagamentoreceita,dataemissaoreceita,valorreceita,pagoreceita,contareceita,descricaoreceita,registroreceita,nfreceita from receita  where  ativoreceita = 1 and excluidoreceita = 0 and  idosreceita = :idOrdemServico', ['idOrdemServico' => $ordemdeservico->id]);

        $cliente = DB::select('select distinct id, nomeCliente, razaosocialCliente from clientes  where  ativoCliente = 1 and id = :clienteOrdemServico', ['clienteOrdemServico' => $ordemdeservico->idClienteOrdemdeServico]);
        $despesaPorOS = DB::select('select distinct * from despesas  where  ativoDespesa = 1 and excluidoDespesa = 0 and idOS = :idOrdemServico', ['idOrdemServico' => $ordemdeservico->id]);
        $percentualPorOS = DB::select('select distinct * from tabelapercentual  where  idostabelapercentual = :idOrdemServico', ['idOrdemServico' => $ordemdeservico->id]);

        $totaldespesas = DB::select('select sum(despesas.precoReal) as totaldespesa, ordemdeservico.id from despesas, ordemdeservico where despesas.idOS = ordemdeservico.id  and despesas.excluidoDespesa = 0  and ordemdeservico.id = :idOrdemServico GROUP BY id', ['idOrdemServico' => $ordemdeservico->id]);
        $totaldespesasAPagar = DB::select('select sum(despesas.precoReal) as totaldespesa, ordemdeservico.id from despesas, ordemdeservico where despesas.idOS = ordemdeservico.id and despesas.excluidoDespesa = 0  and despesas.pago = "N" and ordemdeservico.id = :idOrdemServico GROUP BY id', ['idOrdemServico' => $ordemdeservico->id]);
        $totaldespesasPagas = DB::select('select sum(despesas.precoReal) as totaldespesa, ordemdeservico.id from despesas, ordemdeservico where despesas.idOS = ordemdeservico.id and despesas.excluidoDespesa = 0  and despesas.pago = "S" and ordemdeservico.id = :idOrdemServico GROUP BY id', ['idOrdemServico' => $ordemdeservico->id]);

        $contadorDespesas = count($totaldespesas);
        $contadorDespesasAPagar = count($totaldespesasAPagar);
        $contadorDespesasPagas = count($totaldespesasPagas);

        if ($contadorDespesas == 0) {
            $getArrayTotalDespesas = 0.00;
        } else {
            $getArrayTotalDespesas = $totaldespesas[0]->totaldespesa;
        }
        //verifica o contador de todas as despesas a pagar
        if ($contadorDespesasAPagar == 0) {
            $getArrayTotalDespesasAPagar = 0.00;
        } else {
            $getArrayTotalDespesasAPagar = $totaldespesasAPagar[0]->totaldespesa;
        }

        if ($contadorDespesasPagas == 0) {
            $getArrayTotalDespesasPagas = 0.00;
        } else {
            $getArrayTotalDespesasPagas = $totaldespesasPagas[0]->totaldespesa;
        }

        $totalreceitas = DB::select('select  sum(r.valorreceita) as totalreceita, o.id from  receita r, ordemdeservico o where excluidoreceita = 0 and ativoreceita = 1 and r.idosreceita = o.id and o.id = :idOrdemServico GROUP BY id', ['idOrdemServico' => $ordemdeservico->id]);
        $totalreceitasAPagar = DB::select('select  sum(r.valorreceita) as totalreceita, o.id from  receita r, ordemdeservico o where excluidoreceita = 0 and ativoreceita = 1 and   r.idosreceita = o.id and r.pagoreceita = "N" and o.id = :idOrdemServico GROUP BY id', ['idOrdemServico' => $ordemdeservico->id]);

        $tamanhoArrayReceita = count($totalreceitas);
        if ($tamanhoArrayReceita == 0) {
            $getArrayTotalReceitas = 0.00;
        } else {
            $getArrayTotalReceitas = $totalreceitas[0]->totalreceita;
        }

        $tamanhoArrayReceitaAPagar = count($totalreceitasAPagar);
        if ($tamanhoArrayReceitaAPagar == 0) {
            $getArrayTotalReceitasAPagar = 0.00;
        } else {
            $getArrayTotalReceitasAPagar = $totalreceitasAPagar[0]->totalreceita;
        }

        $totalOS = $ordemdeservico->valorOrdemdeServico;

        bcscale(2);
        $lucro = bcsub($getArrayTotalReceitas, $getArrayTotalDespesas);

        $porcentagemDespesa = bcmul($getArrayTotalDespesas, 100); //cem por cento da regra de tres
        if (($porcentagemDespesa > 0) && ($totalOS > 0)) {
            $porcentagemDespesa = bcdiv($porcentagemDespesa, $totalOS); // divido pelo total da OS
        }

        $porcentagemReceita = bcmul($getArrayTotalReceitas, 100); //cem por cento da regra de tres
        if (($porcentagemReceita > 0) && ($totalOS > 0)) {
            $porcentagemReceita = bcdiv($porcentagemReceita, $totalOS); // divido pelo total da OS
        }

        $porcentagemDespesaAPagar = bcmul($getArrayTotalDespesasAPagar, 100); //cem por cento da regra de tres
        if (($porcentagemDespesaAPagar > 0) && ($totalOS > 0)) {
            $porcentagemDespesaAPagar = bcdiv($porcentagemDespesaAPagar, $totalOS); // divido pelo total da OS
        }

        $porcentagemDespesaPagas = bcmul($getArrayTotalDespesasPagas, 100); //cem por cento da regra de tres
        if (($porcentagemDespesaPagas > 0) && ($totalOS > 0)) {
            $porcentagemDespesaPagas = bcdiv($porcentagemDespesaPagas, $totalOS); // divido pelo total da OS
        }

        $porcentagemReceitaAPagar = bcmul($getArrayTotalReceitasAPagar, 100); //cem por cento da regra de tres
        if (($porcentagemReceita > 0) && ($totalOS > 0)) {
            $porcentagemReceitaAPagar = bcdiv($porcentagemReceitaAPagar, $totalOS); // divido pelo total da OS
        }

        $porcentagemLucro = bcmul($lucro, 100); //cem por cento da regra de tres

        if (($porcentagemLucro <> 0) && ($totalOS <> 0)) {
            $porcentagemLucro = bcdiv($porcentagemLucro, $totalOS); // divido pelo total da OS
        }

        $totalreceitas = number_format($getArrayTotalReceitas, 2, ',', '.');
        $totalreceitasAPagar = number_format($getArrayTotalReceitasAPagar, 2, ',', '.');
        $totaldespesas = number_format($getArrayTotalDespesas, 2, ',', '.');
        $totaldespesasAPagar = number_format($getArrayTotalDespesasAPagar, 2, ',', '.');
        $totaldespesasPagas = number_format($getArrayTotalDespesasPagas, 2, ',', '.');
        $totalOS = number_format($ordemdeservico->valorOrdemdeServico, 2, ',', '.');
        $lucro = number_format($lucro, 2, ',', '.');



        $valorInput = $this->valorInput;
        $valorSemCadastro = $this->valorSemCadastro;
        $variavelReadOnlyNaView = $this->variavelReadOnlyNaView;
        $variavelDisabledNaView = $this->variavelDisabledNaView;


        $qtdDespesas = DB::select('select COUNT(precoReal) as numerodespesas FROM despesas where  excluidoDespesa = 0 and  idOS =:idOrdemServico', ['idOrdemServico' => $ordemdeservico->id]);
        $qtdReceitas = DB::select('select COUNT(valorreceita) as numeroreceitas FROM receita where idosreceita =:idOrdemServico', ['idOrdemServico' => $ordemdeservico->id]);

        $this->logVisualizaOS($ordemdeservico);
        return view('ordemdeservicos.show', compact('ordemdeservico', 'dataInicio', 'cliente', 'formapagamento', 'listaContas', 'listaForncedores', 'vendedores', 'codigoDespesa', 'despesaPorOS', 'receitasPorOS', 'percentualPorOS', 'totaldespesas', 'totaldespesasAPagar', 'totaldespesasPagas', 'totalreceitas', 'totalreceitasAPagar', 'qtdDespesas', 'qtdReceitas', 'lucro', 'totalOS', 'porcentagemDespesa', 'porcentagemReceita', 'porcentagemLucro', 'porcentagemDespesaAPagar', 'porcentagemDespesaPagas', 'porcentagemReceitaAPagar', 'valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OrdemdeServico  $ordemdeservico
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ordemdeservico = OrdemdeServico::find($id);
        $dataInicio = $ordemdeservico->dataCriacaoOrdemdeServico;

        $listaContas = DB::select('select id,apelidoConta, nomeConta from conta where ativoConta = 1');
        $listaForncedores = DB::select('select id,nomeFornecedor, razaosocialFornecedor, contatoFornecedor from fornecedores where ativoFornecedor = 1');
        $vendedores = $listaForncedores;
        $cliente = DB::select('select id, nomeCliente, razaosocialCliente from clientes where ativoCliente = 1');
        $formapagamento = DB::select('select id,nomeFormaPagamento from formapagamento where ativoFormaPagamento = 1 order by id =' . $id);

        $codigoDespesa = DB::select('select id, idGrupoCodigoDespesa, despesaCodigoDespesa from codigodespesas where ativoCodigoDespesa = 1');

        $receitasPorOS = DB::select('select distinct id as idReceita, idosreceita, idclientereceita,idformapagamentoreceita,datapagamentoreceita,dataemissaoreceita,valorreceita,pagoreceita,contareceita,descricaoreceita,registroreceita,nfreceita,excluidoreceita from receita  where ativoreceita = 1 and excluidoreceita = 0 and idosreceita = :idOrdemServico', ['idOrdemServico' => $ordemdeservico->id]);

        $valorOS = $ordemdeservico->valorOrdemdeServico;
        if (isset($valorOS)) {
            $ordemdeservico->valorOrdemdeServico = number_format($valorOS, 2, ',', '.');
        }

        $valorOrcamento = $ordemdeservico->valorOrcamento;
        if (isset($valorOrcamento)) {
            $ordemdeservico->valorOrcamento = number_format($valorOrcamento, 2, ',', '.');
        }

        $contadorReceita = count($receitasPorOS);

        if ($contadorReceita == 0 || $contadorReceita == null) {
            $valorTratadoReceita[0] = 0.0;
        } else {
            for ($i = 0; $i < $contadorReceita; $i++) {
                $valorMonetarioReceita[$i] = $receitasPorOS[$i]->valorreceita;
                $receitasPorOS[$i]->valorreceita = FormatacoesServiceProvider::validaValoresParaView($valorMonetarioReceita[$i]);
            }
        }

        $valorInput = $this->valorInput;
        $valorSemCadastro = $this->valorSemCadastro;
        $variavelReadOnlyNaView = $this->variavelReadOnlyNaView;
        $variavelDisabledNaView = $this->variavelDisabledNaView;

        return view('ordemdeservicos.edit', compact('ordemdeservico', 'dataInicio', 'listaContas', 'listaForncedores', 'vendedores', 'cliente', 'formapagamento', 'codigoDespesa', 'receitasPorOS', 'valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OrdemdeServico  $ordemdeservico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrdemdeServico $ordemdeservico, Receita $receita)
    {
        $temReceita = $request->get('idformapagamentoreceita');

        $tamanhoArrayReceita = count($temReceita);
        $request->validate([

            'idClienteOrdemdeServico'         => 'required',
            'eventoOrdemdeServico'           => 'required|min:3',
            'servicoOrdemdeServico'          => 'required|min:3',

        ]);

        if ($temReceita != '0') {


            for ($i = 0; $i < $tamanhoArrayReceita; $i++) {

                $request->validate([
                    'idformapagamentoreceita'       => 'required',
                    'datapagamentoreceita'          => 'required',
                    'pagoreceita'                   => 'required',
                    'contareceita'                  => 'required',
                    'nfreceita'                     => 'required',
                ]);

                $valorMonetarioReceita[$i]                  = $request->get('valorreceita')[$i];
                $valorReceita[$i] = FormatacoesServiceProvider::validaValoresParaBackEnd($valorMonetarioReceita[$i]);

                $receita->idReceita                     = $request->get('idReceita')[$i];
                $receita->idformapagamentoreceita       = $request->get('idformapagamentoreceita')[$i];
                $receita->datapagamentoreceita          = $request->get('datapagamentoreceita')[$i];
                $receita->dataemissaoreceita            = $request->get('dataemissaoreceita')[$i];
                $receita->valorreceita                  = $valorReceita[$i];
                $receita->pagoreceita                   = $request->get('pagoreceita')[$i];
                $receita->contareceita                  = $request->get('contareceita')[$i];
                $receita->registroreceita               = $request->get('registroreceita');
                // $receita->emissaoreceita             = $request->get('emissaoreceita');
                $receita->nfreceita                     = $request->get('nfreceita')[$i];
                $receita->idosreceita                   = $request->get('idosreceita');
                $receita->descricaoreceita              = $request->get('eventoOrdemdeServico');
                $receita->idclientereceita              = $request->get('idClienteOrdemdeServico');
                $receita->excluidoreceita               = $request->get('excluidoreceita')[$i];

                if ($receita->excluidoreceita == '1' || $receita->excluidoreceita == 1) {
                    $receita->ativoreceita = 0;
                } else {
                    $receita->ativoreceita = 1;
                }

                $receita['idosreceita'] = $ordemdeservico->id;

                if ($receita->idReceita == 'novo') {

                    $novaReceitaOS = Receita::create([
                        'idformapagamentoreceita'   => $receita->idformapagamentoreceita,
                        'descricaoreceita'          => $request->eventoOrdemdeServico,
                        'idclientereceita'          => $request->idClienteOrdemdeServico,
                        'datapagamentoreceita'      => $receita->datapagamentoreceita,
                        'dataemissaoreceita'        => $receita->dataemissaoreceita,
                        'valorreceita'              => $receita->valorreceita,
                        'pagoreceita'               => $receita->pagoreceita,
                        'contareceita'              => $receita->contareceita,
                        'registroreceita'           => $receita->registroreceita,
                        'nfreceita'                 => $receita->nfreceita,
                        'idosreceita'               => $receita->idosreceita,
                        'excluidoreceita'           => $receita->excluidoreceita,
                        'ativoreceita'              => $receita->ativoreceita
                    ]);
                } else {

                    Receita::where('id', $receita->idReceita)
                        ->update([
                            'idformapagamentoreceita'     => $receita->idformapagamentoreceita,
                            'descricaoreceita'            => $request->eventoOrdemdeServico,
                            'idclientereceita'            => $request->idClienteOrdemdeServico,
                            'datapagamentoreceita'        => $receita->datapagamentoreceita,
                            'dataemissaoreceita'          => $receita->dataemissaoreceita,
                            'valorreceita'                => $receita->valorreceita,
                            'pagoreceita'                 => $receita->pagoreceita,
                            'contareceita'                => $receita->contareceita,
                            'registroreceita'             => $receita->registroreceita,
                            'nfreceita'                   => $receita->nfreceita,
                            'idosreceita'                 => $receita->idosreceita,
                            'excluidoreceita'             => $receita->excluidoreceita,
                            'ativoreceita'                => $receita->ativoreceita
                        ]);
                }
            }
        }


        $request['valorOrdemdeServico']      = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorOrdemdeServico'));
        $ordemdeservico->vendedor            = $request->get('vendedor');
        $ordemdeservico->percentualPermitido = $request->get('percentualPermitido');
        $request['valorOrcamento']           = FormatacoesServiceProvider::validaValoresParaBackEnd($request->get('valorOrcamento'));


        $ordemdeservico->update($request->all());

        $this->logVisualizaOS($ordemdeservico);
        return redirect()->route('ordemdeservicos.index')
            ->with('success', 'Ordem de Serviço atualizada com êxito');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OrdemdeServico  $ordemdeservico
     * @return \Illuminate\Http\Response
     */
    public function updateOSApi(Request $request, OrdemdeServico $ordemdeservico)
    {
        $ordemdeservico = OrdemdeServico::findOrFail($request->id);
    
        $request->validate([
            'idClienteOrdemdeServico'   => 'required',
            'eventoOrdemdeServico'      => 'required|min:3',
            'servicoOrdemdeServico'     => 'required|min:3',
        ]);
    
        // Verifica se existem receitas a serem processadas
        $receitas = $request->get('receitas', []);
        $receitaIdsInRequest = [];  // Initialize an empty array to collect receita IDs
    
        foreach ($receitas as $index => $receitaData) {
            // Validação específica de cada receita
            $request->validate([
                "receitas.$index.idformapagamentoreceita" => 'required',
                "receitas.$index.datapagamentoreceita"    => 'required',
                "receitas.$index.pagoreceita"             => 'required',
                "receitas.$index.contareceita"            => 'required',
                "receitas.$index.nfreceita"               => 'required',
            ]);
    
            
            $receita = isset($receitaData['id']) && $receitaData['id'] != 0
            ? Receita::find($receitaData['id'])
            : new Receita();
            
            // Atribui os valores para a receita
            $receita->fill([
                'idformapagamentoreceita' => $receitaData['idformapagamentoreceita'],
                'datapagamentoreceita'    => $receitaData['datapagamentoreceita'],
                'dataemissaoreceita'      => $receitaData['dataemissaoreceita'],
                'valorreceita'            => $receitaData['valorreceita'],
                'pagoreceita'             => $receitaData['pagoreceita'],
                'contareceita'            => $receitaData['contareceita'],
                'nfreceita'               => $receitaData['nfreceita'],
                'descricaoreceita'        => $request->get('eventoOrdemdeServico'),
                'idclientereceita'        => $request->get('idClienteOrdemdeServico'),
                'excluidoreceita'         => $receitaData['excluidoreceita'],
                'idosreceita'             => $ordemdeservico->id,
                'ativoreceita'            => $receitaData['excluidoreceita'] == '1' ? 0 : 1,
            ]);
            
            $receita->save();
            $idReceita = $receita->id;
            // Collect the id if it exists
            if ($idReceita) {
                $receitaIdsInRequest[] = $idReceita;
            }
        }
        // return $receitaIdsInRequest;
        // Now that the foreach loop is done, $receitaIdsInRequest contains all the IDs from the request
        // Fetch all receitas that belong to the ordemdeservico and are not in the request
        $receitasToExclude = Receita::where('idosreceita', $ordemdeservico->id)
            ->whereNotIn('id', $receitaIdsInRequest)
            ->get();
    
        // Update excluidoreceita and ativoreceita for these receitas
        foreach ($receitasToExclude as $receita) {
            $receita->update([
                'excluidoreceita' => 1,
                'ativoreceita' => 0
            ]);
        }
    
        // Atualiza os valores principais da Ordem de Serviço
        $ordemdeservico->update($request->all());
    
        $this->logVisualizaOS($ordemdeservico);
        return response()->json($ordemdeservico, 200);
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OrdemdeServico  $ordemdeservico
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        OrdemdeServico::find($id)->delete();
        return redirect()->route('ordemdeservicos.index')
            ->with('success', 'Ordem de Serviço excluída com êxito!');
    }


    public function logCadastraOS($ordemdeservico)
    {
        $this->Logger->log('info', 'Cadastrou a OS ' . $ordemdeservico->id);
    }

    public function logCadastraReceitaOS($idReceita)
    {
        $this->Logger->log('info', 'Cadastrou a Receita' . $idReceita);
    }

    public function logVisualizaOS($ordemdeservico)
    {
        $this->Logger->log('info', 'Visualizou a OS ' . $ordemdeservico->id);
    }

    public function logAtualizaOS($ordemdeservico, $alteracoes, $original)
    {
        $trechoMensagem =  $ordemdeservico->id . " Dados Alterados: " . PHP_EOL;
        if (isset($alteracoes['despesaCodigoDespesas']) != NULL) {
            $trechoMensagem .= 'Id Cod Desp Atual: ' .       $alteracoes['despesaCodigoDespesas'] .    '. Antigo: ' .  $original['despesaCodigoDespesas'] . PHP_EOL;
        }
        if (isset($alteracoes['idOS']) != NULL) {
            $trechoMensagem .= 'Id OS Atual: ' .             $alteracoes['idOS'] .                     '. Antigo: ' .  $original['idOS'] . PHP_EOL;
        }
        if (isset($alteracoes['idDespesaPai']) != NULL) {
            $trechoMensagem .= 'Id Desp Pai Atual: ' .       $alteracoes['idDespesaPai'] .             '. Antigo: ' .  $original['idDespesaPai'] . PHP_EOL;
        }
        if (isset($alteracoes['descricaoDespesa']) != NULL) {
            $trechoMensagem .= 'Desc Desp Atual: ' .         $alteracoes['descricaoDespesa'] .         '. Antigo: ' .  $original['descricaoDespesa'] . PHP_EOL;
        }
        if (isset($alteracoes['idFornecedor']) != NULL) {
            $trechoMensagem .= 'Id Fornecedor Atual: ' .     $alteracoes['idFornecedor'] .             '. Antigo: ' .  $original['idFornecedor'] . PHP_EOL;
        }
        if (isset($alteracoes['precoReal']) != NULL) {
            $trechoMensagem .= 'Valor Atual: ' .             $alteracoes['precoReal'] .                '. Antigo: ' .  $original['precoReal'] . PHP_EOL;
        }
        if (isset($alteracoes['ehcompra']) != NULL) {
            $trechoMensagem .= 'Foi uma compra Atual: ' .    $alteracoes['ehcompra'] .                 '. Antigo: ' .  $original['ehcompra'] . PHP_EOL;
        }
        if (isset($alteracoes['pago']) != NULL) {
            $trechoMensagem .= 'Pago Atual: ' .              $alteracoes['pago'] .                     '. Antigo: ' .  $original['pago'] . PHP_EOL;
        }
        if (isset($alteracoes['quempagou']) != NULL) {
            $trechoMensagem .= 'Quem Pagou Atual: ' .        $alteracoes['quempagou'] .                '. Antigo: ' .  $original['quempagou'] . PHP_EOL;
        }
        if (isset($alteracoes['idFormaPagamento']) != NULL) {
            $trechoMensagem .= 'Forma PG Atual: ' .          $alteracoes['idFormaPagamento'] .         '. Antigo: ' .  $original['idFormaPagamento'] . PHP_EOL;
        }
        if (isset($alteracoes['conta']) != NULL) {
            $trechoMensagem .= 'Id Conta Atual: ' .          $alteracoes['conta'] .                    '. Antigo: ' .  $original['conta'] . PHP_EOL;
        }
        if (isset($alteracoes['nRegistro']) != NULL) {
            $trechoMensagem .= 'Num Reg Atual: ' .           $alteracoes['nRegistro'] .                '. Antigo: ' .  $original['nRegistro'] . PHP_EOL;
        }
        if (isset($alteracoes['valorEstornado']) != NULL) {
            $trechoMensagem .= 'Estornado Atual: ' .         $alteracoes['valorEstornado'] .           '. Antigo: ' .  $original['valorEstornado'] . PHP_EOL;
        }
        if (isset($alteracoes['vencimento']) != NULL) {
            $trechoMensagem .= 'Vencimento Atual: ' .        $alteracoes['vencimento'] .               '. Antigo: ' .  $original['vencimento'] . PHP_EOL;
        }
        if (isset($alteracoes['despesaFixa']) != NULL) {
            $trechoMensagem .= 'Desp Fixa Atual: ' .         $alteracoes['despesaFixa'] .              '. Antigo: ' .  $original['despesaFixa'] . PHP_EOL;
        }
        if (isset($alteracoes['notaFiscal']) != NULL) {
            $trechoMensagem .= 'Nota Fiscal Atual: ' .       $alteracoes['notaFiscal'] .               '. Antigo: ' .  $original['notaFiscal'] . PHP_EOL;
        }
        if (isset($alteracoes['idBanco']) != NULL) {
            $trechoMensagem .= 'Id Banco Atual: ' .          $alteracoes['idBanco'] .                  '. Antigo: ' .  $original['idBanco'] . PHP_EOL;
        }
        if (isset($alteracoes['reembolsado']) != NULL) {
            $trechoMensagem .= 'Id Reembolsado Atual: ' .    $alteracoes['reembolsado'] .              '. Antigo: ' .  $original['reembolsado'] . PHP_EOL;
        }
        if (isset($alteracoes['cheque']) != NULL) {
            $trechoMensagem .= 'Cheque Atual: ' .            $alteracoes['cheque'] .                   '. Antigo: ' .  $original['cheque'] . PHP_EOL;
        }
        if (isset($alteracoes['dataDoTrabalho']) != NULL) {
            $trechoMensagem .= 'Data do Trabalho Atual: ' .  $alteracoes['dataDoTrabalho'] .           '. Antigo: ' .  $original['dataDoTrabalho'] . PHP_EOL;
        }
        if (isset($alteracoes['dataDaCompra']) != NULL) {
            $trechoMensagem .= 'Data da Compra Atual: ' .    $alteracoes['dataDaCompra'] .             '. Antigo: ' .  $original['dataDaCompra'] . PHP_EOL;
        }
        if (isset($alteracoes['ativoDespesa']) != NULL) {
            $trechoMensagem .= 'Despesa Ativa Atual: ' .     $alteracoes['ativoDespesa'] .             '. Antigo: ' .  $original['ativoDespesa'] . PHP_EOL;
        }
        if (isset($alteracoes['excluidoDespesa']) != NULL) {
            $trechoMensagem .= 'Despesa Excluída Atual: ' .  $alteracoes['excluidoDespesa'] .          '. Antigo: ' .  $original['excluidoDespesa'] . PHP_EOL;
        }
        if (isset($alteracoes['idAlteracaoUsuario']) != NULL) {
            $trechoMensagem .= 'Id Alter. Usuario Atual: ' . $alteracoes['idAlteracaoUsuario'] .       '. Antigo: ' .  $original['idAlteracaoUsuario'] . PHP_EOL;
        }
        if (isset($alteracoes['idAutor']) != NULL) {
            $trechoMensagem .= 'Id Usuario Autor Atual: ' .  $alteracoes['idAutor'] .                  '. Antigo: ' .  $original['idAutor'] . PHP_EOL;
        }

        $this->Logger->log('info', 'Atualizou a OS ' .  $trechoMensagem);
    }

    public function logExcluiOS($ordemdeservico)
    {
        $this->Logger->log('info', 'Excluiu a OS ' . $ordemdeservico->id);
    }
}
