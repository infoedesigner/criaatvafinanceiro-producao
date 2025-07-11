<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



//Rotas de modal
Route::get('/cadastragrupodespesa', function () { return view('grupodespesas.campos'); });
Route::get('/modalmateriais', function () { return view('benspatrimoniais.camposmodal'); });
Route::get('/modaltipomateriais', function () { return view('products.camposmodal'); });
Route::get('/modalcodigodespesa', function () { return view('codigodespesas.camposmodal'); });
Route::get('/modalfornecedor', function () { return view('fornecedores.camposmodal'); });

Route::get('/modaledicaodespesas/{id}', 'DespesaController@editamodal')->name('modaledicaodespesas');



//Rotas de relatório
// Route::get('/relatorio', function () { return view('relatorio.index'); })->name('relatorio');
Route::get('/fatporcliente', function () { return view('relatorio.fatporcliente.index'); });
Route::get('/entradaporcontabancaria', function () { return view('relatorio.entradaporcontabancaria.index'); });
Route::get('/despesaspagasporcontabancaria', function () { return view('relatorio.despesaspagasporcontabancaria.index'); });
Route::get('/despesasporos', function () { return view('relatorio.despesasporos.index'); });
Route::get('/despesassinteticaporos', function () { return view('relatorio.despesassinteticaporos.index'); });
Route::get('/controleconsumomaterial', function () { return view('relatorio.controleconsumomaterial.index'); });
Route::get('/despesasporclienteanalitico', function () { return view('relatorio.despesasporclienteanalitico.index'); });
Route::get('/oscadastradas', function () { return view('relatorio.oscadastradas.index'); });
Route::get('/contaspagasporgrupo', function () { return view('relatorio.contaspagasporgrupo.index'); });
Route::get('/contasapagarporgrupo', function () { return view('relatorio.contasapagarporgrupo.index'); });
Route::get('/contasaidentificar', function () { return view('relatorio.contasaidentificar.index'); });
Route::get('/fornecedor', function () { return view('relatorio.fornecedor.index'); });
Route::get('/despesasporosplanilha', function () { return view('relatorio.despesasporosplanilha.index'); });
Route::get('/ordemdeservicorecebidas', function () { return view('relatorio.ordemdeservicorecebidas.index'); });
Route::get('/osrecebidasporcliente', function () { return view('relatorio.osrecebidasporcliente.index'); });
Route::get('/entradasdereceitasrecebidas', function () { return view('relatorio.entradasdereceitasrecebidas.index'); });
Route::get('/areceberporcliente', function () { return view('relatorio.areceberporcliente.index'); });
Route::get('/areceberporos', function () { return view('relatorio.areceberporos.index'); });
Route::get('/reembolso', function () { return view('relatorio.reembolso.index'); });
Route::get('/prolabore', function () { return view('relatorio.prolabore.index'); });
Route::get('/despesasfixavariavel', function () { return view('relatorio.despesasfixavariavel.index'); });
Route::get('/notafiscalfornecedor', function () { return view('relatorio.notafiscalfornecedor.index'); });
Route::get('/notasemitidas', function () { return view('relatorio.notasemitidas.index'); });
Route::get('/notasficaisemitidascriaatva', function () { return view('relatorio.notasficaisemitidascriaatva.index'); });
Route::get('/fechamentofinal', function () { 
    if(Gate::allows('rel-fechamentofinal')){
        return view('relatorio.fechamentofinal.index'); 
    }
});



Route::get('/controledeorcamento', function () { return view('relatorio.controledeorcamento.index'); });
Route::get('/contasareceberporos', function () { return view('contacorrente.contasareceberporos'); });


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/resumofinanceiro', 'ContaController@resumofinanceiro')->name('resumofinanceiro');

Route::get('/contasAReceber', 'ContaController@contasAReceber')->name('contasAReceber');
Route::get('/contasAPagar', 'ContaController@contasAPagar')->name('contasAPagar');
Route::get('/relatorioFornecedores', 'FornecedorController@relatorioFornecedores')->name('relatorioFornecedores');
Route::get('/tabelaContasAReceber', 'ContaController@tabelaContasAReceber')->name('tabelaContasAReceber');
Route::get('/tabelaContasAPagar', 'ContaController@tabelaContasAPagar')->name('tabelaContasAPagar');
Route::get('/extratoConta', 'ContaController@extratoConta')->name('extratoConta');
Route::get('/fluxodecaixa', 'RelatorioController@displayFluxoDeCaixa')->name('fluxodecaixa');
Route::get('/tabelaExtratoConta', 'ContaController@tabelaExtratoConta')->name('tabelaExtratoConta');
Route::get('/tabelaRelatorioFornecedores', 'FornecedorController@tabelaRelatorioFornecedores')->name('tabelaRelatorioFornecedores');
Route::get('/tabelaDespesas', 'DespesaController@tabelaDespesas')->name('tabelaDespesas');
Route::get('/displaydespesas', 'DespesaController@index')->name('displaydespesas');
Route::get('/displaydespesascompleto', 'DespesaController@despesascompleto')->name('displaydespesascompleto');
Route::get('/displayreceita', 'ReceitaController@index')->name('displayreceita');
// Route::get('/displayentrada', 'EntradasController@displayentrada')->name('displayentrada');
Route::get('/displaysaida', 'SaidasController@displaysaidas')->name('displaysaidas');
Route::get('/tabelaReceitas', 'ReceitaController@tabelaReceitas')->name('tabelaReceitas');
Route::get('/tabelaOrdemServicos', 'OrdemdeServicoController@tabelaOrdemServicos')->name('tabelaOrdemServicos');

Route::get('/listaCodigoDespesa', 'DespesaController@listaCodigoDespesa')->name('listaCodigoDespesa');
Route::get('/listaMateriais', 'DespesaController@listaMateriais')->name('listaMateriais');
Route::get('/listaClientes', 'ClientesController@listaClientes')->name('listaClientes');
Route::get('/listaFornecedores', 'DespesaController@listaFornecedores')->name('listaFornecedores');
Route::get('/listaTipoMateriais', 'DespesaController@listaTipoMateriais')->name('listaTipoMateriais');

// Route::post('/retornoanalisepedido', 'PedidoCompraController@updateAprovacao')->name('retornoanalisepedido');
// Route::post('/revisaoanalisepedido', 'PedidoCompraController@updateRevisao')->name('revisaoanalisepedido');
// Route::post('/marcacomolido', 'PedidoCompraController@marcaComoLido')->name('marcacomolido');
// Route::delete('/remover-documento-pedido-compra/{pedidoId}/{documentoId}', 'PedidoCompraController@removerDocumento')->name('remover-documento-pedido-compra');


Route::post('/salvarmodalgrupodespesa', 'GrupoDespesaController@salvarmodalgrupodespesa')->name('salvarmodalgrupodespesa');
Route::post('/cadastromateriais', 'BensPatrimoniaisController@salvarmodal')->name('cadastromateriais');
Route::post('/cadastrotipomateriais', 'ProductController@salvarmodal')->name('cadastrotipomateriais');
Route::post('/cadastrocodigodespesa', 'CodigoDespesaController@salvarmodal')->name('cadastrocodigodespesa');
Route::post('/cadastrofornecedor', 'FornecedorController@salvarmodal')->name('cadastrofornecedor');

Route::post('/apiupdatedespesas', 'DespesaController@apiUpdateDespesas')->name('apiupdatedespesas');

//View
Route::get('/replacegrupodespesa', function () { 
    if(Gate::allows('grupodespesa-edit')){
        return view('grupodespesas.replace'); 
    }
});
//Submit
Route::post('/replacegrupodespesa', 'GrupoDespesaController@replaceGrupoDespesa')->name('replacegrupodespesa');

//View
Route::get('/replacecodigodespesa', function () { 
    if(Gate::allows('codigodespesa-edit')){
        return view('codigodespesas.replace'); 
    }
});
//Submit
Route::post('/replacecodigodespesa', 'CodigoDespesaController@replaceCodigoDespesa')->name('replacecodigodespesa');


Route::post('/marcacomolido', 'PedidoCompraController@marcaComoLido')->name('marcacomolido');
Route::delete('/remover-documento-pedido-compra/{pedidoId}/{documentoId}', 'PedidoCompraController@removerDocumento')->name('remover-documento-pedido-compra');

Route::middleware(['auth', 'auditlog', 'sandbox'])->group(function () {
    Route::post('/retornoanalisepedido', 'PedidoCompraController@updateAprovacao')->name('retornoanalisepedido');
    Route::post('/revisaoanalisepedido', 'PedidoCompraController@updateRevisao')->name('revisaoanalisepedido');
    Route::resource('pedidocompra','PedidoCompraController');
});

Route::middleware(['auth', 'sandbox'])->group(function () {
    Route::resource('roles','RoleController');
    Route::resource('users','UserController');
    Route::resource('products','ProductController');
    Route::resource('funcionarios','FuncionarioController');
    Route::resource('benspatrimoniais','BensPatrimoniaisController');
    Route::resource('bancos','BancoController');
    Route::resource('contas','ContaController');
    Route::resource('orgaosrg','OrgaosRGController');
    Route::resource('fornecedores','FornecedorController');
    Route::resource('estoque','EstoqueController');
    Route::resource('entradas','EntradasController');
    Route::resource('saidas','SaidasController');
    Route::resource('clientes','ClientesController');
    Route::resource('formapagamentos','FormaPagamentoController');
    Route::resource('ordemdeservicos','OrdemdeServicoController');
    Route::resource('codigodespesas','CodigoDespesaController');
    Route::resource('despesas', 'DespesaController');
    Route::resource('verbas','VerbasController');
    Route::resource('tabelapercentual','TabelaPercentualController');
    Route::resource('receita','ReceitaController');
    Route::resource('grupodespesas','GrupoDespesaController');
    Route::resource('notasrecibos','NotasRecibosController');
    Route::resource('aliquotamensal','AliquotaMensalController');
    Route::resource('relatorio','RelatorioController');
    Route::resource('sandbox','SandboxController');
    
    Route::get('/renovaSessao', function () {
        // Obtenha o ID do usuário autenticado
        $userId = Auth::id();
    
        // Use o método session() para acessar os dados da sessão
        $sessionData = session()->all(); // Isso irá recuperar todos os dados da sessão
    
        // Verifique se o usuário está autenticado e se a sessão contém os dados necessários
        if (Auth::check() && isset($sessionData['lastActivityTime'])) {
            // Calcule o tempo restante com base no último horário de atividade
            $lastActivityTime = $sessionData['lastActivityTime'];
            $sessionLifetime = config('session.lifetime'); // Obtém o tempo de vida da sessão das configurações
            $currentTime = now()->timestamp;
            $timeRemaining = $lastActivityTime + $sessionLifetime - $currentTime;
    
            // Certifique-se de que o tempo restante seja positivo
            $timeRemaining = max(0, $timeRemaining);
    
            // Retorne os dados em JSON
            return response()->json(['timeRemaining' => $timeRemaining]);
        } else {
            // Se o usuário não estiver autenticado ou se os dados da sessão estiverem ausentes, retorne um erro
            return response()->json(['error' => 'Usuário não autenticado ou dados de sessão ausentes'], 401);
        }
    })->middleware('auth');
     // Middleware de autenticação
    
    Route::get('/despesas/{despesa}', 'DespesaController@show')->name('despesas.show');


    Route::get('/get-time-remaining', 'HomeController@getTimeRemaining');
    
    Route::get('/inventarioCompras', 'EstoqueController@inventarioCompras');

    Route::get('/analiseMaterial', 'EstoqueController@analiseMaterial');
});


