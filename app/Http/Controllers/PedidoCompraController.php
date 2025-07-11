<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Providers\FormatacoesServiceProvider;
use App\Classes\Logger;
use App\PedidoCompra;
// use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Enums\StatusEnumPedidoCompra;
use Illuminate\Support\Facades\Storage;
use App\DocumentoAnexado;
use App\Enums\EnumEntidades;
use App\AuditLog;
use Illuminate\Support\Facades\Auth;

class PedidoCompraController extends Controller
{
    private $Enc;
    private $Logger;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:pedidocompra-list|pedidocompra-create|pedidocompra-edit|pedidocompra-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:pedidocompra-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:pedidocompra-edit|pedidocompra-expedicao', ['only' => ['edit', 'update', 'updateAprovacao']]);
        $this->middleware('permission:pedidocompra-delete', ['only' => ['destroy']]);
        $this->middleware('permission:pedidocompra-analise', ['only' => ['analise']]);

        //Importação do Logger
        $this->Logger = new Logger();

        $this->acao =  request()->segment(count(request()->segments()));
        $this->valorInput = null;
        $this->valorSemCadastro = null;
        $this->infoSelectVazio  = '<option value="" selected> -- NENHUM -- </option>';

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
    public function index(Request $request)
    {

        $idusuariologado = Auth::id();
        $listarTodos = json_decode($request->post('listarTodos'));

        if($request->post('aprovado') != null){
            $aprovado = json_decode($request->post('aprovado'));
            $notificado = json_decode($request->post('notificado'));

            return view('pedidocompra.index', compact('idusuariologado', 'aprovado', 'notificado', 'listarTodos'));
        }

        return view('pedidocompra.index', compact('idusuariologado','listarTodos'));

    }

    public function create()
    {

        $valorInput = $this->valorInput;
        $valorSemCadastro = $this->valorSemCadastro;
        $variavelReadOnlyNaView = $this->variavelReadOnlyNaView;
        $variavelDisabledNaView = $this->variavelDisabledNaView;
        $infoSelectVazio        = $this->infoSelectVazio;

        return view('pedidocompra.create', compact('valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView', 'infoSelectVazio'));
    }

    public function store(Request $request)
    {
        $pedido = new PedidoCompra();

        $oscriaatva = $request->get('oscriaatva');
        if(isset($oscriaatva)){
            if($oscriaatva == 'EMPRESA GERAL'){
                $pedido->ped_os                     = $oscriaatva;
            }else{
                $pedido->ped_os                     = $request->get('ped_os');
            }
        }else{
            $pedido->ped_os                     = $request->get('ped_os');
        }
        
        $request->validate([
            'ped_tipopedido'     => 'required',
            'ped_os'             => 'required',
            'nf_exigencia'       => 'required',
            'ped_descprod'       => 'required',
        ],
        [
            'ped_tipopedido.required'   => 'Informe se a compra já foi efetuada ou não', 
            'ped_os.required'           => 'Informe se a compra é para a CRIAATVA ou para uma OS', 
            'nf_exigencia.required'     => 'Informe o campo: "Há Nota Fiscal?"', 
            'ped_descprod.required'     => 'Informe a descrição', 
        ]);

        if($request->get('nf_exigencia') == 'S'){
            if(($request->get('ped_notafiscal') == '') || ($request->get('ped_notafiscal') === null)){
               $request->validate([ 
                   'ped_notafiscal'   => 'required',
               ], 
               [ 
                   'ped_notafiscal.required'  => 'Informe a Nota Fiscal',
               ]);
           }
        }
        
        $pedido->info_financeira            = 'N';
        if(!$request->info_financeira){
            $pedido->info_financeira            = 'S';
            $request->validate([
                'ped_nomecomprador'  => 'required',
                'ped_fornecedor'     => 'required',
                'ped_precounit'      => 'required',
                'ped_qtd'            => 'required',
                'ped_valortotal'     => 'required',
            ],
            [
                'ped_nomecomprador.required'=> 'Nome do Comprador é obrigatório', 
                'ped_fornecedor.required'   => 'Nome do Fornecedor é obrigatório', 
                'ped_precounit.required'    => 'Informe o valor unitário', 
                'ped_qtd.required'          => 'Informe a quantidade', 
                'ped_valortotal.required'   => 'Informe o valor total', 
    
             ]); 
             
             $this->pegaDados($request, $pedido);
             $this->validaDados($request);
        }
         
        // $pedido->ped_os                     = $request->get('ped_os');
        $pedido->ped_tipopedido             = $request->get('ped_tipopedido');
        $pedido->ped_data                   = $request->get('ped_data');
        $pedido->ped_nomecomprador          = $request->get('ped_nomecomprador');
        $pedido->ped_usrsolicitante         = $request->get('ped_usrsolicitante');
        $pedido->ped_fornecedor             = ($request->get('ped_fornecedor') == '') ? null : $request->get('ped_fornecedor') ;
        $pedido->ped_descprod               = $request->get('ped_descprod');
        $pedido->ped_valortotal             = $request->get('ped_valortotal');
        $pedido->ped_formapag               = $request->get('ped_formapag');
        $pedido->ped_precounit              = $request->get('ped_precounit');
        $pedido->ped_qtd                    = $request->get('ped_qtd');
        $pedido->ped_observacao             = $request->get('ped_observacao');
        $pedido->ped_notafiscal             = $request->get('ped_notafiscal');
        $pedido->observacoes_solicitante    = $request->get('observacoes_solicitante');
        $pedido->ped_aprovado               = StatusEnumPedidoCompra::AGUARNDANDO_COMPRA;
        $pedido->ped_contaaprovada          = '';
        $pedido->ped_exigaprov              = '';
        $pedido->ped_excluidopedido         = '0';
        $pedido->ped_novanotificacao        = '1';
        $pedido->nf_exigencia               = $request->get('nf_exigencia');
        $pedido->local_entrega              = $request->get('local_entrega');
        $pedido->prazo_entrega_limite       = $request->get('prazo_entrega_limite');
        $pedido->save();
        
        // Lógica para armazenar os arquivos
        if ($request->hasFile('documentoAnexadoPedido')) {
            $this->uploadAnexos($request, $pedido);
        }

        $this->logCadastraPedidoCompra($pedido);

        return redirect()->route('pedidocompra.index')
            ->with('success', 'Pedido de compra n° ' . $pedido->id . ' realizado.');
    }

    public function uploadAnexos(Request $request, &$pedido){
        foreach ($request->file('documentoAnexadoPedido') as $documento) {
            try {
                $name = uniqid(date('HisYmd'));
                $extension = $documento->extension();
                $nameFile = "{$name}.{$extension}";
                $upload = $documento->storeAs("pedidoCompra/p_$pedido->id", $nameFile, 'public');

                if ($upload) {               
                    $documentoAnexado = $pedido->documentosAnexados()->create(['documento_anexado' => $nameFile]);
                    // Defina os valores adicionais após a criação do modelo
                    $documentoAnexado->update([
                        'enum_entidade' => EnumEntidades::PEDIDO_COMPRA,
                        'id_entidade' => $pedido->id
                    ]);
                } else {
                    return redirect()->back()
                        ->with('error', 'Falha ao fazer upload')
                        ->withInput();
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Falha ao fazer upload: ' . $e->getMessage())
                    ->withInput();
            }
        }
    }

    // Lógica para remover arquivo específico
    public function removerDocumento($pedidoId, $documentoId) {
        $documento = DocumentoAnexado::where('id_entidade', $pedidoId)
                                    ->where('enum_entidade', EnumEntidades::PEDIDO_COMPRA)
                                    ->where('id', $documentoId)
                                    ->firstOrFail(); // Obtém o documento

        // Remove o arquivo do armazenamento
        Storage::disk('public')->delete("pedidoCompra/p_$pedidoId/" . $documento->documento_anexado);

        // Remove o registro do documento do banco de dados
        $documento->delete();

        // Retorne uma resposta JSON bem-sucedida
        return response()->json(['message' => 'Documento removido com sucesso'], 200);
    }

    public function update(Request $request)
    {
        $pedido =  PedidoCompra::find($request->id);

        $oscriaatva = $request->get('oscriaatva');
        if(isset($oscriaatva)){
            if($oscriaatva == 'EMPRESA GERAL'){
                $pedido->ped_os                     = $oscriaatva;
            }else{
                $pedido->ped_os                     = $request->get('ped_os');
            }
        }else{
            $pedido->ped_os                     = $request->get('ped_os');
        }



        if($request->get('nf_exigencia') == 'S' ){
            if(($request->get('ped_notafiscal') == '') || ($request->get('ped_notafiscal') === null)){
                $request->validate([ 
                    'ped_notafiscal'   => 'required',
                ], 
                [ 
                    'ped_notafiscal.required'  => 'Informe a Nota Fiscal',
                ]);
            }
        }

        $pedido->id                         = $request->get('id');


        $pedido->ped_tipopedido             = $request->get('ped_tipopedido');
        $pedido->ped_data                   = $request->get('ped_data');
        $pedido->ped_nomecomprador          = $request->get('ped_nomecomprador');
        // $pedido->ped_usrsolicitante         = Auth::id();

        // $pedido->ped_fornecedor             = $request->get('ped_fornecedor');
        $pedido->ped_fornecedor             = ($request->get('ped_fornecedor') == '') ? null : $request->get('ped_fornecedor') ;
        $pedido->ped_descprod               = $request->get('ped_descprod');
        $pedido->ped_valortotal             = $request->get('ped_valortotal');
        // $pedido->ped_cartaodecredito        = $request->get('ped_cartaodecredito');
        $pedido->ped_formapag               = $request->get('ped_formapag');
        $pedido->ped_periodofaturado        = $request->get('ped_periodofaturado');
        $pedido->ped_pix                    = $request->get('ped_pix');
        $pedido->ped_favorecido             = $request->get('ped_favorecido');
        $pedido->ped_boleto                 = $request->get('ped_boleto');
        $pedido->ped_banco                  = $request->get('ped_banco');
        $pedido->ped_conta                  = $request->get('ped_conta');
        $pedido->ped_agenciaconta           = $request->get('ped_agenciaconta');
        $pedido->ped_cpfcnpj                = $request->get('ped_cpfcnpj');
        $pedido->ped_numcartao              = $request->get('ped_numcartao');
        $pedido->ped_reembolsado            = $request->get('ped_reembolsado');
        $pedido->ped_vzscartao              = $request->get('ped_vzscartao');
        $pedido->ped_precounit              = $request->get('ped_precounit');
        $pedido->ped_qtd                    = $request->get('ped_qtd');
        $pedido->observacoes_solicitante    = $request->get('observacoes_solicitante');
        $pedido->ped_observacao             = $request->get('ped_observacao');
        $pedido->ped_notafiscal             = $request->get('ped_notafiscal');
        $pedido->nf_exigencia               = $request->get('nf_exigencia');
        $pedido->local_entrega              = $request->get('local_entrega');
        $pedido->prazo_entrega_limite       = $request->get('prazo_entrega_limite');


        $pedido->ped_aprovado           =  StatusEnumPedidoCompra::AGUARNDANDO_COMPRA;
        $pedido->ped_contaaprovada      = '';
        $pedido->ped_exigaprov          = '';
        $pedido->ped_excluidopedido     = '0';
        $pedido->ped_novanotificacao    = '1';
        $pedido->data = FormatacoesServiceProvider::getHorarioParaBackend();

        $pedido->info_financeira            = 'N';
        if(!$request->info_financeira){
            //Situação do validador preenchendo todos os dados, situação do solicitante editando e preenchendo os dados

            $pedido->info_financeira            = 'S';
            $request->validate([
                'ped_nomecomprador'  => 'required',
                'ped_fornecedor'     => 'required',
                'ped_precounit'      => 'required',
                'ped_qtd'            => 'required',
                'ped_valortotal'     => 'required',
            ],
            [
                'ped_nomecomprador.required'=> 'Nome do Comprador é obrigatório', 
                'ped_fornecedor.required'   => 'Nome do Fornecedor é obrigatório', 
                'ped_precounit.required'    => 'Informe o valor unitário', 
                'ped_qtd.required'          => 'Informe a quantidade', 
                'ped_valortotal.required'   => 'Informe o valor total', 
    
             ]); 
             
             $this->pegaDados($request, $pedido);
             $this->validaDados($request);
        }        

        $this->logAlteraPedidoCompra($pedido);
        $stringConsulta = $pedido->atualizaPedidos($pedido);
        $dadosAtualizacao = DB::update($stringConsulta);

        // Lógica para armazenar os arquivos
        if ($request->hasFile('documentoAnexadoPedido')) {
            $this->uploadAnexos($request, $pedido);
        }



        return redirect()->route('pedidocompra.index')
            ->with('success', 'Pedido de compra n° ' . $pedido->id . ' atualizado.');
    }

    public function updateAprovacao(Request $request)
    {
        $pedido = PedidoCompra::find($request->id);
        $msgSuccess = '';
        try{
            
            $idUsuarioAprovador = auth()->id();
            
            DB::table('pedidocompra')
            ->where('id', $request->id)
            ->update([
                    'ped_usr_aprovador'     => $idUsuarioAprovador,
                    'ped_dt_aprovacao'      => date('Y-m-d H:i:s'),
                    'ped_aprovado'          => $request->ped_aprovado,
                    'ped_novanotificacao'   => ($request->ped_aprovado != '') ? 1 : null,
                    'ped_exigaprov'         => $request->ped_exigaprov,
                    'ped_observacao'        => $request->ped_observacao,
                    'ped_excluidopedido'    => 0,
                ]);
                
            $this->logValidaPedidoCompra($pedido);
            
            if ($request->ped_aprovado == StatusEnumPedidoCompra::PEDIDO_AGUARDANDO_APROVACAO ) {
                $msgSuccess =  'Pedido de compra n° ' . $request->id . ' foi encaminhado para a área de aprovação, já notificamos ao setor responsável.';
            }
            
            if ($request->ped_aprovado == StatusEnumPedidoCompra::LIBERADO_PARA_EXPEDICAO ) {
                $msgSuccess =  'Pedido de compra n° ' . $request->id . ' foi encaminhado para a expedição, já notificamos ao setor.';
            }
            
            if ($request->ped_aprovado == StatusEnumPedidoCompra::PEDIDO_APROVADO ) {
                $msgSuccess =   'Pedido de compra n° ' . $request->id . ' foi aprovado, já notificamos ao solicitante.';
            }
            
            if ($request->ped_aprovado == StatusEnumPedidoCompra::PEDIDO_NAO_APROVADO ) {
                $msgSuccess =   'Pedido de compra n° ' . $request->id . ' foi reprovado, já notificamos ao solicitante.';
            }
        } 
        catch (\Throwable $th) {
            return redirect()->route('pedidocompra.index')
            ->with('alert', 'Não conseguimos processar sua solicitação. Favor, Tente novamente');
        }


        return redirect()->route('pedidocompra.index', ['listarTodos' => true])
        ->with('success', $msgSuccess);

    }

    public function updateRevisao(Request $request)
    { 
        
        $ped_aprovado       = (is_null($request->ped_aprovado) || $request->ped_aprovado != StatusEnumPedidoCompra::PEDIDO_CANCELADO) ? StatusEnumPedidoCompra::PEDIDO_REVISADO : $request->ped_aprovado;
        $idUsuarioAprovador = auth()->id();

        try {
            $pedido = PedidoCompra::find($request->id);
            $pedido->update([
                    'ped_aprovado'              => $ped_aprovado,
                    'ped_tipopedido'            => 1,
                    'ped_novanotificacao'       => 1,
                    'ped_pago'                  => $request->ped_pago,
                    'ped_contaaprovada'         => $request->ped_contaaprovada,
                    'ped_observacao_revisao'    => $request->ped_observacao_revisao,
                    'ped_usr_finalizador'       => $idUsuarioAprovador,
                    'ped_dt_finalizacao'        => date('Y-m-d H:i:s'),
                ]);
                
                $this->logRevisaPedidoCompra($pedido->id);
        
            return redirect()->route('pedidocompra.index', ['listarTodos' => true])
                ->with('success', 'Pedido de compra n° ' . $request->id . ' foi finalizado, já notificamos ao solicitante.');
        } catch (\Throwable $th) {
            return redirect()->route('pedidocompra.index')
                ->with('alert', 'Não conseguimos processar sua solicitação. Favor, tente novamente');
        }
    }

    public function marcaComoLido(Request $request)
    {

        // try{
        $query = "UPDATE pedidocompra SET ";

        if ($request->ped_novanotificacao != '') {
            $query .= " ped_novanotificacao= '$request->ped_novanotificacao'";
        }

        DB::update("$query WHERE id= '$request->id'");

        // return redirect()->route("pedidocompra". $request->id);
        return redirect()->back()->with('message','Notificação removida!');


        // } catch (\Throwable $th) {
        //     return redirect()->route('despesas.index')
        //         ->with('alert', 'Não conseguimos processar sua solicitação. Favor, Tente novamente');
        // }

    }

    public function apipedidocompra(Request $request)
    {

        // $user = auth()->user();
        // dd($user->id);
        $aprovado       = json_decode($request->post('aprovado'));
        $notificado     = json_decode($request->post('notificado'));
        $listarTodos    = json_decode($request->post('listarTodos'));

        // $idusuariologado = json_decode($request->post('id'));
        $idusuariologado    = $request->post('id');
        $permissao          = json_decode($request->post('permissao'));
        if($permissao == '199' && (!is_null($listarTodos))){
            $idusuariologado = null;
        }
        // if($permissao == '199'){
        //     $idusuariologado = null;
        // }

        $pedidocompra   = new PedidoCompra();
        $stringConsulta = $pedidocompra->listaPedidos($idusuariologado, $aprovado, $notificado);
        $dadosConsulta  = DB::select($stringConsulta);
        return $dadosConsulta;
    }

    public function show(Request $request, $id)
    {
        $pedido = PedidoCompra::with('solicitante','aprovador','finalizador', 'documentosAnexados')->find($id);

        $valorInput = $this->valorInput;
        $valorSemCadastro = $this->valorSemCadastro;
        $variavelReadOnlyNaView = $this->variavelReadOnlyNaView;
        $variavelDisabledNaView = $this->variavelDisabledNaView;
        $infoSelectVazio        = $this->infoSelectVazio;

        $auditLogs = PedidoCompra::getAuditLogs($id);


        return view('pedidocompra.show', compact('pedido', 'valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView', 'infoSelectVazio', 'auditLogs'));
    }

    public function edit($id)
    {
        $pedido = PedidoCompra::with('solicitante')->find($id);

        $valorInput = $this->valorInput;
        $valorSemCadastro       = $this->valorSemCadastro;
        $variavelReadOnlyNaView = $this->variavelReadOnlyNaView;
        $variavelDisabledNaView = $this->variavelDisabledNaView;
        $infoSelectVazio        = $this->infoSelectVazio;

        if($pedido->ped_aprovado == StatusEnumPedidoCompra::PEDIDO_APROVADO){
            return view('pedidocompra.show', compact('pedido', 'valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView', 'infoSelectVazio'));
        }

        return view('pedidocompra.edit', compact('pedido', 'valorInput', 'valorSemCadastro', 'variavelReadOnlyNaView', 'variavelDisabledNaView', 'infoSelectVazio'));
    }

    public function destroy($id)
    {
        $pedido = new PedidoCompra();

        $pedido->id = PedidoCompra::find($id);
        $pedido->ped_excluidopedido     = 1;

        $pedido->update();
        $this->logExcluiPedidoCompra($pedido);

        return redirect()->route('pedidocompra.index')
            ->with('success', 'Pedido de compra n° ' . $pedido->id . ' cancelado.');
    }

    public function logCadastraPedidoCompra($pedido = null)
    {
        $action = 'pedido_compra_criado';
        $this->logAlteraPedidoCompra($pedido, $action);
    }

    public function logAlteraPedidoCompra($pedido = null, $action = null )
    {
        $user   = Auth::user();
        $action = $action ?? 'pedido_compra_alterado';

        AuditLog::create([
            'id'      => $pedido->id,
            'user_id' => $user->id,
            'action'  => $action,
            'model'   => 'PedidoCompra',
            'dirty'   => $pedido->getDirty(),
            'before'  => $pedido->getOriginal(),
            'after'   => $pedido->getAttributes(),
            // adicione mais campos e detalhes conforme necessário
        ]);
    }

    public function logValidaPedidoCompra($pedido = null)
    {
        $action = 'pedido_compra_validado';
        $this->logAlteraPedidoCompra($pedido, $action);
    }

    public function logRevisaPedidoCompra($pedido = null)
    {
        $action = 'pedido_compra_revisado';
        $this->logAlteraPedidoCompra($pedido, $action);
    }

    public function logInvalidaPedidoCompra($pedido = null)
    {
        $action = 'pedido_compra_reprovado';
        $this->logAlteraPedidoCompra($pedido, $action);
    }

    public function logExcluiPedidoCompra($pedido = null)
    {
        $action = 'pedido_compra_excluido';
        $this->logAlteraPedidoCompra($pedido, $action);
    }

    public function pegaDados($request, $pedido)
    {

        if($request->get('nf_exigencia') == 'S'){
            if(($request->get('ped_notafiscal') == '') || ($request->get('ped_notafiscal') === null)){

                $request->validate([ 
                    'ped_notafiscal'   => 'required',
                ], 
                [ 
                    'ped_notafiscal.required'  => 'Informe a Nota Fiscal',
                ]);
            }
        }

        if($request->get('ped_formapag') == 'avista'){
            $pedido->ped_pix                    = $request->get('ped_pix');
            $pedido->ped_favorecido             = $request->get('ped_favorecido');
            if(!is_null($pedido->ped_pix)){
                if(!is_null($pedido->ped_favorecido)){
                    return $pedido;
                }else{
                    $request->validate([
                        'ped_favorecido'                 => 'required' ],
                    [   'ped_favorecido.required'        => 'Preencha o favorecido da chave pix informada']);
                }
            }
            $pedido->ped_banco                  = $request->get('ped_banco');
            $pedido->ped_conta                  = $request->get('ped_conta');
            $pedido->ped_agenciaconta           = $request->get('ped_agenciaconta');   
            $pedido->ped_cpfcnpj                = $request->get('ped_cpfcnpj');   


           
            if($pedido->ped_banco != '180'){
                $request->validate([
                    'ped_cpfcnpj'                 => 'required|cpf_cnpj' ],
                [   'ped_cpfcnpj.required'        => 'CPF/CNPJ obrigatório']);
            }

            return $pedido;

            $request->validate([
                'ped_banco'         => 'required',
                'ped_conta'         => 'required',
                'ped_agenciaconta'  => 'required',
            ],
            [
                'ped_banco.required'        => 'Nome do Banco é obrigatório', 
                'ped_conta.required'        => 'Informe a conta', 
                'ped_agenciaconta.required' => 'Informe a agência',     
             ]);
        }
        if($request->get('ped_formapag') == 'cred'){
            $pedido->ped_numcartao              = $request->get('ped_numcartao');
            $pedido->ped_vzscartao              = $request->get('ped_vzscartao'); 
            return $pedido;
            
            $request->validate([ 'ped_numcartao'  => 'required', 'ped_vzscartao'  => 'required'] ,
             [  'ped_numcartao.required'=> 'O número final do cartão é obrigatório', 
                'ped_vzscartao.required'=> 'O número de parcelas no cartão é obrigatório']);

        }
        if($request->get('ped_formapag') == 'faturado'){
            $pedido->ped_pix                    = $request->get('ped_pix');
            $pedido->ped_favorecido             = $request->get('ped_favorecido');
            $pedido->ped_periodofaturado        = $request->get('ped_periodofaturado');
            $pedido->ped_boleto                 = $request->get('ped_boleto');
            $pedido->ped_banco                  = $request->get('ped_banco');
            $pedido->ped_conta                  = $request->get('ped_conta');
            $pedido->ped_agenciaconta           = $request->get('ped_agenciaconta');   
            $pedido->ped_cpfcnpj                = $request->get('ped_cpfcnpj');   
            return $pedido;

            $request->validate([ 'ped_periodofaturado'  => 'required'] , [ 'ped_periodofaturado.required'=> 'O período faturado é obrigatório']);

        }
        if($request->get('ped_formapag') == 'reembolsado'){
            $pedido->ped_reembolsado            = $request->get('ped_reembolsado');
            $pedido->ped_pix                    = $request->get('ped_pix');
            $pedido->ped_banco                  = $request->get('ped_banco');
            $pedido->ped_conta                  = $request->get('ped_conta');
            $pedido->ped_agenciaconta           = $request->get('ped_agenciaconta');   
            $pedido->ped_cpfcnpj                = $request->get('ped_cpfcnpj');   

            // Se o Banco for diferente de Itau, validar o CPF/CNPJ
            if($pedido->ped_banco != '180' && is_null($pedido->ped_pix)){
                $request->validate([
                    'ped_cpfcnpj'          => 'required|cpf_cnpj' ],
                [   'ped_cpfcnpj.required' => 'CPF/CNPJ obrigatório']);

            }else{
                return $pedido;
            }

            $request->validate([ 
                'ped_reembolsado'   => 'required',
                'ped_banco'         => 'required',
                'ped_conta'         => 'required',
                'ped_agenciaconta'  => 'required',
            ], 
            [ 
                'ped_reembolsado.required'  => 'Nome do Reembolsado é obrigatório',
                'ped_banco.required'        => 'Nome do Banco é obrigatório', 
                'ped_conta.required'        => 'Informe a conta', 
                'ped_agenciaconta.required' => 'Informe a agência', 
            ]);
        }

    }

    
    public function validaDados($request)
    {
        $validator = null;
    

        if($request->get('nf_exigencia') == 'S'){
            if(($request->get('ped_notafiscal') == '') || ($request->get('ped_notafiscal') === null)){
                $validator = Validator::make($request->all(), [ 
                    'ped_notafiscal'   => 'required',
                ], 
                [ 
                    'ped_notafiscal.required'  => 'Informe a Nota Fiscal',
                ]);
            }
        }

        if($request->get('ped_formapag') == 'avista'){
            if(!is_null($request->ped_pix)){
                if(!is_null($request->ped_favorecido)){
                    return $request;
                }else{
                    $validator = Validator::make($request->all(), 
                    [   'ped_favorecido'            => 'required' ],
                    [   'ped_favorecido.required'   => 'Preencha o favorecido da chave pix informada'  ]);
                }
            }
            $validator = Validator::make($request->all(), [
                'ped_banco'         => 'required',
                'ped_conta'         => 'required',
                'ped_agenciaconta'  => 'required',
            ],
            [
                'ped_banco.required'        => 'Nome do Banco é obrigatório', 
                'ped_conta.required'        => 'Informe a conta', 
                'ped_agenciaconta.required' => 'Informe a agência',     
            ]);
        }
        elseif($request->get('ped_formapag') == 'cred'){
            $validator = Validator::make($request->all(), [
                'ped_numcartao'  => 'required',
                'ped_vzscartao'  => 'required'
            ],
            [
                'ped_numcartao.required'=> 'O número final do cartão é obrigatório', 
                'ped_vzscartao.required'=> 'O número de parcelas no cartão é obrigatório'
            ]);
        }
        elseif ($request->get('ped_formapag') == 'faturado') {
            $rules = [];
            $messages = [];
        
            if (is_null($request->ped_periodofaturado)) {
                $rules['ped_periodofaturado'] = 'required';
        
                $messages = array_merge($messages, [
                    'ped_periodofaturado.required' => 'O período faturado é obrigatório',
                ]);
            }else{

                
                if (is_null($request->ped_boleto) && is_null($request->ped_pix)) {
                    $rules['ped_boleto'] = 'required';
                    $rules['ped_pix'] = 'required';
                    
                    $messages = array_merge($messages, [
                        'ped_boleto.required' => 'Informe o boleto',
                        'ped_pix.required' => 'Informe o pix',
                    ]);
                }else{
                    return $request;
                }
                
                if (is_null($request->ped_banco) || is_null($request->ped_conta) || is_null($request->ped_agenciaconta)) {
                    $rules['ped_banco'] = 'required';
                    $rules['ped_conta'] = 'required';
                    $rules['ped_agenciaconta'] = 'required';
                    
                    $messages = array_merge($messages, [
                        'ped_banco.required' => 'Nome do Banco é obrigatório',
                        'ped_conta.required' => 'Informe a conta',
                        'ped_agenciaconta.required' => 'Informe a agência',
                    ]);
                }else{
                    return $request;
                }
            }
        
        
            $validator = Validator::make($request->all(), $rules, $messages);
        
            if ($validator->fails()) {
                throw new HttpResponseException(redirect()->back()->withErrors($validator)->withInput());
            }
                return $request;           
        }
        
        elseif($request->get('ped_formapag') == 'reembolsado'){
            if($request->ped_banco == '180' || !is_null($request->ped_pix)){
                return $request;
            }
            $validator = Validator::make($request->all(), [
                'ped_reembolsado'  => 'required',
                'ped_banco'         => 'required',
                'ped_conta'         => 'required',
                'ped_agenciaconta'  => 'required',
            ],
            [
                'ped_reembolsado.required'=> 'Nome do Reembolsado é obrigatório',
                'ped_banco.required'        => 'Nome do Banco é obrigatório', 
                'ped_conta.required'        => 'Informe a conta', 
                'ped_agenciaconta.required' => 'Informe a agência', 
            ]);
        }
        else{
            $validator = Validator::make($request->all(), [
                'ped_formapag'  => 'required',
            ],
            [
                'ped_formapag.required'=> 'Informe a Forma de Pagamento',
            ]);
        }

    
        if ($validator->fails()) {
            throw new HttpResponseException(redirect()->back()->withErrors($validator)->withInput());
        }
    
        return $request;
    }
    
}
