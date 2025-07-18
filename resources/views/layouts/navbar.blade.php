<nav class="navbar navbar-expand-md navbar-dark navbar-laravel" id="navbar"
@can('sandbox-modify')
    @if($modoSandbox->ativo == '1' || $modoSandbox->ativo == 1)
        style="background-color:darkorange !important;"
    @endif
@endcan    
>
<script>
    // Pega os dados da sessão do Laravel e os coloca no cookie
    const userData = @json(session('userData'));
    const token = @json(session('token')); // Se o token já for uma string, não precisa acessar ->token
    const userPermissions = @json(session('userPermissions'));

    // Verifica se a rota atual é "/login". Se for, não faz nada.
    if (window.location.pathname === '/login') {
        console.log('Bem-vindo a área de login.');
    } else {
        if (userData && token) {
            // Verifica se o cookie 'userData' já está definido
            if (!getCookie('userData')) {
                document.cookie = `userData=${JSON.stringify(userData)}; path=/; domain=.danieltecnologia.com; secure; samesite=strict`;
            }

            if (!getCookie('token')) {
                document.cookie = `token=${token}; path=/; domain=.danieltecnologia.com; secure; samesite=strict`;
            }

            if (!getCookie('userPermissions')) {
                const compactPermissions = userPermissions.map(permission => permission.name);
                document.cookie = `userPermissions=${JSON.stringify(compactPermissions)}; path=/; domain=.danieltecnologia.com; secure; samesite=strict`;
            }

            // console.log('Dados de sessão armazenados no cookie (se não já presentes):');
            // console.log('UserData:', userData);
            // console.log('Token:', token);
            // console.log('UserPermissions:', userPermissions);
        } else {
            console.error('Erro: Dados de sessão ausentes');
        }
    }

    // Função para pegar o valor do cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
</script>



    @yield('nav')
    <div class="container" style="max-width: fit-content !important;">
        <a href="{{ route('home') }}" class="mr-3"> <i class="fas fa-home" style="color: white;"></i></a>
        <a class="navbar-brand" href="{{ url('/') }}" style="color:white;">
            Criaatva
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto"></ul>

            <ul class="navbar-nav ml-auto">
                @guest
                    <li><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                @else
                    <li class="nav-item dropdown">
                        @can('ordemdeservico-list')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                OS <span class="caret"></span>
                            </a>
                        @endcan

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('ordemdeservico-list')
                                <a class="dropdown-item" href="https://v2{{$_SERVER['SERVER_NAME']}}/ordemdeservicos">Consulta (V2)</a>
                            @endcan
                            @can('ordemdeservico-create')
                                <a class="dropdown-item" href="https://v2{{$_SERVER['SERVER_NAME']}}/ordemdeservicos/create">Cadastrar (V2)</a>
                            @endcan
                            @can('ordemdeservico-list')
                                <a class="dropdown-item" href="{{ route('ordemdeservicos.index') }}">Consultar</a>
                            @endcan
                            @can('ordemdeservico-create')
                                <a class="dropdown-item" href="{{ route('ordemdeservicos.create') }}">Cadastrar</a>
                            @endcan

                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        @can('despesa-list')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Despesas <span class="caret"></span>
                            </a>
                        @elsecan('despesa-create')
                            {{-- <a class="dropdown-item" href="{{ route('ordemdeservicos.index') }}">Consultar</a> --}}
                            <a class="nav-link" href="{{ route('despesas.create') }}">Cadastrar Despesas</a>
                        @endcan

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('despesa-list')
                                {{-- <a class="dropdown-item" href="{{ route('despesas.index') }}">Listar todas</a> --}}
                                <a class="dropdown-item" data-toggle="modal" data-target=".modaldepesas"
                                    style="cursor:pointer;">Pesquisar por despesa</a>
                                <a onclick="abreModalDespesas(param = 'pesquisadespesascompleto');" class="dropdown-item"
                                    href="#" style="cursor:pointer; color:red;">Pesquisar Despesas (completo)</a>
                            @endcan
                            @can('despesa-create')
                                <a class="dropdown-item" href="{{ route('despesas.create') }}">Cadastrar</a>
                            @endcan
                            @can('codigodespesa-list')
                                <a class="dropdown-item" href="{{ route('codigodespesas.index') }}">Código Despesas</a>
                            @endcan
                            @can('grupodespesa-list')
                                <a class="dropdown-item" href="{{ route('grupodespesas.index') }}">Grupo Despesas</a>
                            @endcan
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        @can('receita-list')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Receitas <span class="caret"></span>
                            </a>
                        @endcan
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('receita-list')
                                <a class="dropdown-item" href="https://v2{{$_SERVER['SERVER_NAME']}}/receitas">Consulta (V2)</a>
                                @endcan
                                @can('receita-list')
                                <a class="dropdown-item" data-toggle="modal" data-target=".modalreceita"
                                style="cursor:pointer;">Pesquisar por receita</a>
                                @endcan
                                @can('receita-create')
                                <a class="dropdown-item" href="{{ route('receita.create') }}">Cadastrar</a>
                                @endcan
                            </div>
                    </li>
                    <li class="nav-item dropdown">
                        @can('receita-list')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Relatórios <span class="caret"></span>
                            </a>
                        @endcan
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('relatorio-list')
                                <a class="dropdown-item" href="https://v2{{$_SERVER['SERVER_NAME']}}/receitas">Relatórios V2</a>
                                <a class="dropdown-item" href="{{ route('relatorio.index') }}" role="button">Relatórios</a>
                                
                            @endcan
                            {{-- @can('entradas-list')
                            <a class="dropdown-item" href="{{ route('resumofinanceiro') }}">Resumo Financeiro</a>
                            @endcan --}}
                            
                        </div>
                    </li>
                                           
                    @include('layouts/helpersview/navpedidocompra')


                    <li class="nav-item dropdown">
                        @can('fornecedor-list')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Forn. <span class="caret"></span>
                            </a>
                        @endcan
                        
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('fornecedor-create')
                                <a class="dropdown-item" href="{{ route('fornecedores.create') }}">Cadastrar</a>
                                @endcan
                                @can('fornecedor-list')
                                <a class="dropdown-item" href="{{ route('fornecedores.index') }}">Consultar</a>
                                @endcan
                            </div>
                        </li>
                        
                        <li class="nav-item dropdown">
                        @can('cliente-list')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Clientes <span class="caret"></span>
                            </a>
                        @endcan

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('cliente-create')
                            <a class="dropdown-item" href="{{ route('clientes.create') }}">Cadastrar</a>
                            @endcan
                            @can('cliente-list')
                                <a class="dropdown-item" href="{{ route('clientes.index') }}">Consultar</a>
                            @endcan
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        @can('funcionario-list')
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Funcionários <span class="caret"></span>
                    </a>
                    @endcan

                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        @can('funcionario-create')
                        <a class="dropdown-item" href="{{ route('funcionarios.create') }}">Cadastrar</a>
                        @endcan
                        @can('funcionario-list')
                                <a class="dropdown-item" href="{{ route('funcionarios.index') }}">Consultar</a>
                                @endcan
                            </div>
                        </li>
                        
                        @can('notasrecibos-list')
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            Notas/Alíquotas <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @can('notasrecibos-list')
                            <a class="dropdown-item" href="{{ route('notasrecibos.index') }}">Consultar
                                        Notas/Recibos</a>
                                        @endcan
                                        
                                        @can('notasrecibos-list')
                                        <a class="dropdown-item" href="{{ route('aliquotamensal.index') }}">Alíquotas Mensais</a>
                                        @endcan

                                    </div>
                        </li>
                        @endcan


                    <li class="nav-item dropdown">
                        @can('configuracoes')
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Conf. <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="https://v2{{$_SERVER['SERVER_NAME']}}/settings">Configurações (V2)</a>
                            
                                @can('usuario-list')
                                <a class="dropdown-item" href="{{ route('users.index') }}">Usuários</a>
                                @endcan
                                
                                @can('banco-list')
                                <a class="dropdown-item" href="{{ route('bancos.index') }}">Bancos</a>
                                @endcan

                                @can('formapagamento-list')
                                <a class="dropdown-item" href="{{ route('formapagamentos.index') }}">Formas de Pagamento</a>
                                @endcan
                                
                                @can('conta-list')
                                <a class="dropdown-item" href="{{ route('contas.index') }}">Contas</a>
                            @endcan

                            @can('codigodespesa-list')
                            <a class="dropdown-item" href="{{ route('codigodespesas.index') }}">Código Despesas</a>
                            @endcan

                            @can('role-list')
                                <a class="dropdown-item" href="{{ route('roles.index') }}">Regras</a>
                                @endcan
                                
                                @can('orgaorg-list')
                                <a class="dropdown-item" href="{{ route('orgaosrg.index') }}">Órgãos RG</a>
                                @endcan
                                
                                @can('benspatrimoniais-list')
                                <a class="dropdown-item" href="{{ route('products.index') }}">Tipo de Bens Patrimoniais</a>
                                @endcan
                            </div>
                            @endcan
                    </li>

                    @can('benspatrimoniais-list')
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Bens <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                @can('benspatrimoniais-list')
                                    <a class="dropdown-item" href="{{ route('benspatrimoniais.index') }}">Cadastro de
                                        Materiais</a>
                                @endcan

                                @can('entradas-list')
                                    <a class="dropdown-item" href="{{ route('entradas.index') }}">Entradas de Materiais</a>
                                @endcan

                                @can('saidas-list')
                                    <a class="dropdown-item" href="{{ route('saidas.index') }}">Saídas (Baixa de Materiais)</a>
                                @endcan

                                @can('estoque-list')
                                    <a class="dropdown-item" href="{{ route('estoque.index') }}">Estoque (Inventário) </a>
                                    <a class="dropdown-item" href="{{ url('inventarioCompras') }}">Inventário x Compras </a>
                                    <a class="dropdown-item" href="{{ url('analiseMaterial') }}">Análise Material </a>
                                @endcan
                            </div>
                        </li>
                    @endcan

                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            v-pre>{{ Auth::user()->name }}<span class="caret"></span>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="users/{{ Auth::user()->id }}/edit">Editar Perfil <i
                                    class="far fa-id-card ml-1"></i></a>

                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                Sair
                                <i class="fas fa-power-off"></i>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>

</nav>



<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="" id="formFiltraPeriodoMonetario" onsubmit="return chamaPrevencaodeClique(event)"
                method="get">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle" style="color:black;">Selecione o período e
                        conta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body col-sm-12">
                    <div class="row ml-2">
                        <label>Conta</label>
                        <select name="conta" id="conta" class="selecionaComInput col-sm-12"
                            style="width:440px;" required>
                            <option disabled selected>Selecione...</option>
                            @foreach ($listaContas as $contas)
                                <option value="{{ $contas->id }}">{{ $contas->apelidoConta }} -
                                    {{ $contas->nomeConta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="ml-2 mt-2">Período</label>
                    <div class="row">

                        <input type="date" required class="form-control col-sm-5 ml-4 mr-1" name="datainicial"
                            id="datainicial">
                        <input type="date" required class="form-control col-sm-5 " name="datafinal"
                            id="datafinal">
                        <input type="hidden" value="" name="modorelatorio" id="modorelatorio">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" id="buscarCC" class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
    </div>
</div>


@include('layouts/modal/modalpesquisadespesas')
@include('layouts/modal/modalpesquisareceita')
{{-- @include('layouts/modal/modalpesquisaentrada') --}}


<script>
    function alteraRotaFormularioCC() {
        document.getElementById("formFiltraPeriodoMonetario").setAttribute("action", "{{ route('extratoConta') }}");
    }

    function alteraRotaFormularioFluxo(relatorio) {
        if (relatorio === 'sintetico') {
            document.getElementById("modorelatorio").value = "sintetico";
        }
        if (relatorio === 'analitico') {
            document.getElementById("modorelatorio").value = 'analitico';

        }
        document.getElementById("formFiltraPeriodoMonetario").setAttribute("action", "{{ route('fluxodecaixa') }}");
        document.getElementById("datafinal").style.visibility = "hidden";
        document.getElementById("datafinal").removeAttribute("required");
    }
</script>



<datalist id="datalistIdReceita">
    @foreach ($listaReceitas as $receitas)
        <option value="{{ $receitas->id }}">{{ $receitas->id }}
        </option>
    @endforeach
</datalist>

<datalist id="datalistDescricaoReceita">
    @foreach ($listaReceitas as $receitas)
        <option value="{{ $receitas->descricaoreceita }}">{{ $receitas->descricaoreceita }}
        </option>
    @endforeach
</datalist>

<datalist id="datalistIdDespesa">
    @can('despesa-list-all')
        @foreach ($listaDespesas as $despesas)
            <option value="{{ $despesas->id }}">{{ $despesas->id }}
            </option>
        @endforeach
    @endcan
    @can('despesa-list')
        @foreach ($listaDespesas as $despesas)
            @if ($despesas->idAutor == Auth::user()->id)
                <option value="{{ $despesas->id }}">{{ $despesas->id }}
                </option>
            @endif
        @endforeach
    @endcan
</datalist>

<datalist id="datalistDescricaoDespesa">
    @can('despesa-list-all')
        @foreach ($listaDespesas as $despesas)
            <option value="{{ $despesas->descricaoDespesa }}">{{ $despesas->descricaoDespesa }}
            </option>
        @endforeach
    @endcan
    @can('despesa-list')
        @foreach ($listaDespesas as $despesas)
            @if ($despesas->idAutor == Auth::user()->id)
                <option value="{{ $despesas->descricaoDespesa }}">{{ $despesas->descricaoDespesa }}
                </option>
            @endif
        @endforeach
    @endcan
</datalist>

<datalist id="datalistOrdemServico">
    @foreach ($pegaidOS as $ordemdeservico)
        <option value="{{ $ordemdeservico->id }}">{{ $ordemdeservico->id }}
        </option>
    @endforeach

</datalist>

<datalist id="datalistFornecedor">
    @foreach ($listaFornecedores as $fornecedores)
        <option value="{{ $fornecedores->razaosocialFornecedor }}">{{ $fornecedores->razaosocialFornecedor }}
        </option>
    @endforeach
</datalist>

<datalist id="datalistCodDespesa">
    @foreach ($listaCodigoDespesa as $coddespesa)
        <option value="{{ $coddespesa->despesaCodigoDespesa }}">{{ $coddespesa->despesaCodigoDespesa }}
        </option>
    @endforeach
</datalist>

<datalist id="datalistGrupoDespesa">
    @foreach ($listaGrupoDespesa as $grupodespesa)
        <option value="{{ $grupodespesa->grupoDespesa }}">{{ $grupodespesa->grupoDespesa }}
        </option>
    @endforeach
</datalist>

<datalist id="datalistContas">
    @foreach ($listaContas as $conta)
        <option value="{{ $conta->apelidoConta }}">{{ $conta->nomeConta }} - {{ $conta->apelidoConta }}
        </option>
    @endforeach
</datalist>

<datalist id="datalistCliente">
    @foreach ($listaClientes as $cliente)
        <option value="{{ $cliente->id }}">{{ $cliente->razaosocialCliente }}</option>
    @endforeach
</datalist>
