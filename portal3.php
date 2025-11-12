<?php
// Inicia a sessão para armazenar o token de segurança (CSRF)
session_start();

// --- 1. CONFIGURAÇÕES DO BANCO DE DADOS ---
$db_host = '127.0.0.1';    // Ou 'localhost'
$db_name = 'sus_portal';   // Nome do banco de dados
$db_user = 'root';         // Usuário padrão do XAMPP
$db_pass = '';           // Senha padrão do XAMPP (vazia)

// --- 2. INICIALIZAÇÃO DE VARIÁVEIS ---
$pdo = null;
$resultados = [];        // Array para armazenar os resultados da busca
$erro_busca = '';        // Mensagem de erro da busca
$sucesso_revisao = '';     // Mensagem de sucesso da revisão
$erro_revisao = '';      // Mensagem de erro da revisão

// --- 3. GERAÇÃO DE TOKEN CSRF ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- 4. CONEXÃO SEGURA COM O BANCO DE DADOS (PDO) ---
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Falha na conexão com o banco de dados. Verifique as configurações. Erro: " . $e->getMessage());
}

// --- 5. FUNÇÃO PARA MASCARAR O CPF (LGPD) ---
function mascarar_cpf($cpf) {
    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf_limpo) != 11) {
        return "CPF Inválido";
    }
    // Retorna no formato 123.***.***-**
    return substr($cpf_limpo, 0, 3) . '.***.***-' . substr($cpf_limpo, 9, 2);
}

// --- 6. PROCESSAMENTO DOS FORMULÁRIOS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && $pdo) {
    
    // Validação do Token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: index.php");
        exit;
    }

    // Simula um pequeno atraso para ver o loading spinner (REMOVA EM PRODUÇÃO)
    // sleep(1); 

    $action = $_POST['action'] ?? '';

    // --- AÇÃO: BUSCAR PACIENTE ---
    if ($action == 'buscar_paciente') {
        try {
            $nome = $_POST['nome'] ?? '';
            $cpf = $_POST['cpf'] ?? '';
            $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf); // Limpa máscara

            if (empty($nome) || empty($cpf_limpo)) {
                $erro_busca = "Por favor, preencha o Nome e o CPF para consultar.";
            } else {
                // ASSUMINDO que sua tabela se chama 'fila_espera'
                $stmt = $pdo->prepare("SELECT * FROM fila_espera WHERE nome LIKE ? AND cpf = ?");
                $stmt->execute(["%$nome%", $cpf_limpo]);
                $resultados = $stmt->fetchAll();

                if (empty($resultados)) {
                    $erro_busca = "Nenhum registro encontrado para este Nome e CPF. Se você já foi atendido ou acredita que isso é um erro, por favor, solicite uma revisão.";
                }
            }
        } catch (PDOException $e) {
            $erro_busca = "Ocorreu um erro técnico durante a consulta. Tente novamente mais tarde.";
        }
    }

    // --- AÇÃO: SOLICITAR REVISÃO ---
    if ($action == 'solicitar_revisao') {
        try {
            $rev_nome = $_POST['rev_nome'] ?? '';
            $rev_cpf = $_POST['rev_cpf'] ?? '';
            $rev_nascimento = $_POST['rev_nascimento'] ?? '';
            $rev_telefone = $_POST['rev_telefone'] ?? '';
            $rev_whatsapp = $_POST['rev_whatsapp'] ?? '';
            $rev_observacao = $_POST['rev_observacao'] ?? '';
            
            $rev_cpf_limpo = preg_replace('/[^0-9]/', '', $rev_cpf);

            if (empty($rev_nome) || empty($rev_cpf_limpo) || empty($rev_nascimento)) {
                $erro_revisao = "Os campos Nome, CPF e Data de Nascimento são obrigatórios.";
            } else {
                // ASSUMINDO que sua tabela de revisões se chama 'solicitacoes_revisao'
                $stmt = $pdo->prepare(
                    "INSERT INTO solicitacoes_revisao (nome, cpf, data_nascimento, telefone, whatsapp, observacao, data_solicitacao) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$rev_nome, $rev_cpf_limpo, $rev_nascimento, $rev_telefone, $rev_whatsapp, $rev_observacao]);
                
                $sucesso_revisao = "Sua solicitação de revisão foi enviada com sucesso! Nossa equipe analisará e entrará em contato se necessário.";
            }

        } catch (PDOException $e) {
            $erro_revisao = "Ocorreu um erro técnico ao enviar sua solicitação. Tente novamente mais tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal da Transparência - Fila de Espera - Hoftalon</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js CDN (para controlar os modais e loading) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- iMask.js CDN (para máscaras de input) -->
    <script src="https://unpkg.com/imask"></script>
    
    <!-- Adicionando Fonte "Inter" -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Aplicando a fonte "Inter" como padrão */
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Cores da Marca Hoftalon */
        :root {
            --hoftalon-blue: #003a70;
            --hoftalon-teal: #00a99d;
            --hoftalon-light-blue: #e6f0f7;
            --hoftalon-border-blue: #b3d1e6;
        }

        /* Oculta elementos controlados pelo Alpine antes da inicialização */
        [x-cloak] { display: none !important; }

        /* Estilos personalizados para o anel de foco usando as cores da marca */
        .focus\:ring-hoftalon-blue:focus {
            --tw-ring-color: var(--hoftalon-blue);
            box-shadow: 0 0 0 2px var(--tw-ring-color);
            border-color: var(--hoftalon-blue);
        }
        
        /* Spinner de Carregamento */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-left-color: #ffffff;
            border-radius: 50%;
            width: 1.25rem; /* 20px */
            height: 1.25rem; /* 20px */
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <!--
      Controle de estado principal com Alpine.js
      - isLoading: controla o spinner do botão de busca
      - show...Modal: controla a visibilidade de cada modal
    -->
    <div x-data="{ 
        isLoading: false,
        showResultadoModal: false, 
        showErroModal: false, 
        showRevisaoModal: false, 
        showSucessoModal: false 
    }" class="min-h-screen flex flex-col">

        <!-- Início do VLibras (Acessibilidade) -->
        <div vw class="enabled">
            <div vw-access-button class="active"></div>
            <div vw-plugin-wrapper>
                <div class="vw-plugin-top-wrapper"></div>
            </div>
        </div>
        <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
        <script>
            new window.VLibras.Widget('https://vlibras.gov.br/app');
        </script>
        <!-- Fim do VLibras -->

        <!-- 1. CABEÇALHO E AVISO LGPD -->
        <header class="bg-white shadow-md">
            <div class="container mx-auto max-w-5xl p-6 text-center">
                <!-- Logo do Hoftalon -->
                <img src="https://hoftalon.com.br/wp-content/uploads/2022/10/hoftalon-logo@2x-300x147.png" style="width: 250px;" alt="Hospital Hoftalon" class="header-logo img-fluid mx-auto mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Portal da Transparência</h1>
                <h2 class="text-2xl" style="color: var(--hoftalon-blue);">Consulta de Posição na Fila de Espera (SUS)</h2>
                
                <!-- Aviso LGPD com cores da marca -->
                <div class="text-left bg-[var(--hoftalon-light-blue)] border border-[var(--hoftalon-border-blue)] text-gray-700 p-4 rounded-lg text-sm mt-8">
                    <p class="font-bold mb-2 text-[var(--hoftalon-blue)]">INFORMATIVO (Lei nº 13.709 - LGPD)</p>
                    <ol class="list-decimal list-inside pl-4 space-y-1 text-gray-600">
                        <li>Os pacientes em fila de espera devem autorizar a coleta de dados pessoais.</li>
                        <li>As informações coletadas serão utilizados exclusivamente para o cadastro e identificação do usuário.</li>
                        <li>Os dados podem incluir: (Nome, Nome da Mãe, Data de Nascimento, CNS e CPF).</li>
                        <li>Dúvidas ou questionamentos: <span class="font-semibold text-[var(--hoftalon-teal)]">dpo@hoftalon.com.br</span></li>
                    </ol>
                </div>
            </div>
        </header>

        <!-- 2. FORMULÁRIO DE BUSCA PRINCIPAL -->
        <main class="container mx-auto max-w-xl p-6 mt-10 flex-grow">
            <div class="bg-white p-8 sm:p-10 rounded-xl shadow-2xl">
                <h3 class="text-2xl font-semibold text-gray-800 mb-8 text-center">Consulte sua Posição</h3>
                
                <!-- 
                  Formulário com @submit.prevent 
                  - @submit="isLoading = true": Ativa o estado de loading no Alpine
                -->
                <form action="index.php" method="POST" @submit="isLoading = true">
                    <input type="hidden" name="action" value="buscar_paciente">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="mb-5">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo do Paciente</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite o nome completo" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                    </div>
                    
                    <div class="mb-8">
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">CPF do Paciente</label>
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                    </div>
                    
                    <button type="submit"
                            :disabled="isLoading"
                            class="w-full flex items-center justify-center bg-[var(--hoftalon-blue)] text-white py-3 px-6 rounded-lg font-semibold text-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-hoftalon-blue transition-all duration-200 ease-in-out shadow-lg hover:shadow-blue-900/20 disabled:opacity-60 disabled:cursor-not-allowed">
                        
                        <!-- Estado de Loading (Spinner) -->
                        <div x-show="isLoading" class="spinner mr-3"></div>
                        
                        <!-- Texto do Botão -->
                        <span x-show="isLoading">Buscando...</span>
                        <span x-show="!isLoading">Buscar</span>
                    </button>
                </form>
            </div>
        </main>

        <!-- 3. MODAL DE RESULTADOS (Sucesso) -->
        <div x-cloak x-show="showResultadoModal" @keydown.escape.window="showResultadoModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-resultados" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Fundo escuro (Backdrop) -->
                <div x-show="showResultadoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" @click="showResultadoModal = false" aria-hidden="true"></div>

                <!-- Conteúdo do Modal -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div x-show="showResultadoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block w-full max-w-6xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    
                    <div class="sm:flex sm:items-start">
                        <!-- Ícone do Modal -->
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[var(--hoftalon-light-blue)] sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-[var(--hoftalon-blue)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title-resultados">Resultados da Consulta</h3>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Entrada</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Olho</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procedimento</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Atualização</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posição</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($resultados)): ?>
                                    <?php foreach ($resultados as $row): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['id_fila'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['data_entrada'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nome'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars(mascarar_cpf($row['cpf'] ?? '')); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['olho'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['procedimento'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['data_atualizacao'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-[var(--hoftalon-teal)]"><?php echo htmlspecialchars($row['posicao_fila'] ?? ''); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['observacoes'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                        <button @click="showResultadoModal = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-gray-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Voltar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. MODAL DE ERRO (Não encontrado) -->
        <div x-cloak x-show="showErroModal" @keydown.escape.window="showErroModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-erro" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showErroModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" @click="showErroModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div x-show="showErroModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title-erro">Atenção</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($erro_busca); // Exibe a mensagem de erro vinda do PHP ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button @click="showErroModal = false; showRevisaoModal = true" type="button" class="inline-flex w-full justify-center rounded-md bg-[var(--hoftalon-teal)] px-4 py-2 text-base font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--hoftalon-teal)] focus:ring-offset-2 sm:col-start-2 sm:text-sm transition-colors">
                            Solicitar Revisão
                        </button>
                        <button @click="showErroModal = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-gray-200 px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:mt-0 sm:col-start-1 sm:text-sm transition-colors">
                            Tentar Novamente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. MODAL DE SOLICITAÇÃO DE REVISÃO -->
        <div x-cloak x-show="showRevisaoModal" @keydown.escape.window="showRevisaoModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-revisao" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRevisaoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" @click="showRevisaoModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div x-show="showRevisaoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[var(--hoftalon-light-blue)]">
                        <svg class="h-6 w-6 text-[var(--hoftalon-blue)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title-revisao">Solicitação de Revisão de Cadastro</h3>
                        <p class="text-sm text-gray-600 mt-2">Preencha o formulário abaixo para que nossa equipe possa analisar seu cadastro.</p>
                    </div>

                    <?php if ($erro_revisao): // Mostra erro se houver falha no envio da revisão ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded relative my-4" role="alert">
                            <span class="block sm:inline"><?php echo htmlspecialchars($erro_revisao); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="index.php" method="POST" class="mt-6">
                        <input type="hidden" name="action" value="solicitar_revisao">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="md:col-span-2">
                                <label for="rev_nome" class="block text-sm font-medium text-gray-700">Nome Completo <span class="text-red-600">*</span></label>
                                <input type="text" id="rev_nome" name="rev_nome" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                            </div>
                            <div>
                                <label for="rev_cpf" class="block text-sm font-medium text-gray-700">CPF <span class="text-red-600">*</span></label>
                                <input type="text" id="rev_cpf" name="rev_cpf" placeholder="000.000.000-00" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                            </div>
                            <div>
                                <label for="rev_nascimento" class="block text-sm font-medium text-gray-700">Data de Nascimento <span class="text-red-600">*</span></label>
                                <input type="date" id="rev_nascimento" name="rev_nascimento" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                            </div>
                            <div>
                                <label for="rev_telefone" class="block text-sm font-medium text-gray-700">Telefone (Fixo ou Celular)</label>
                                <input type="tel" id="rev_telefone" name="rev_telefone" placeholder="(00) 00000-0000" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                            </div>
                            <div>
                                <label for="rev_whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                <input type="tel" id="rev_whatsapp" name="rev_whatsapp" placeholder="(00) 00000-0000" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue">
                            </div>
                            <div class="md:col-span-2">
                                <label for="rev_observacao" class="block text-sm font-medium text-gray-700">Observação (Opcional)</label>
                                <textarea id="rev_observacao" name="rev_observacao" rows="3" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-transparent focus:ring-2 focus:ring-hoftalon-blue" placeholder="Descreva brevemente o seu caso, se necessário."></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-[var(--hoftalon-blue)] px-4 py-2 text-base font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--hoftalon-blue)] focus:ring-offset-2 sm:col-start-2 sm:text-sm transition-colors">
                                Enviar Solicitação
                            </button>
                            <button type="button" @click="showRevisaoModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-gray-200 px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:mt-0 sm:col-start-1 sm:text-sm transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 6. MODAL DE SUCESSO DA REVISÃO -->
        <div x-cloak x-show="showSucessoModal" @keydown.escape.window="showSucessoModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-sucesso" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showSucessoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" @click="showSucessoModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div x-show="showSucessoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title-sucesso">Sucesso!</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($sucesso_revisao); // Exibe a mensagem de sucesso vinda do PHP ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6">
                        <button @click="showSucessoModal = false" type="button" class="inline-flex w-full justify-center rounded-md bg-[var(--hoftalon-blue)] px-4 py-2 text-base font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--hoftalon-blue)] focus:ring-offset-2 sm:text-sm transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- Fim do x-data -->

    <!-- 7. RODAPÉ -->
    <footer class="bg-gray-800 text-gray-400 text-sm text-center p-6 mt-12">
        <p>&copy; <?php echo date('Y'); ?> Hoftalon. Todos os direitos reservados.</p>
    </footer>

    <!--
    |--------------------------------------------------------------------------
    | NOTAS DE SEGURANÇA PARA O ADMINISTRADOR (Mantidas como comentário)
    |--------------------------------------------------------------------------
    |
    | [SOLUCIONADO] SQL Injection: Prevenido com PDO + Prepared Statements.
    | [SOLUCIONADO] Cross-Site Scripting (XSS): Prevenido com htmlspecialchars().
    | [SOLUCIONADO] Cross-Site Request Forgery (CSRF): Prevenido com tokens de sessão.
    | [AÇÃO NECESSÁRIA] Portas de Banco de Dados: Firewall deve bloquear acesso externo à porta 3306.
    | [AÇÃO NECESSÁRIA] Man-in-the-Middle (MitM): Configurar SSL (HTTPS) no Apache.
    | [AÇÃO NECESSÁRIA] Credenciais: Alterar usuário e senha padrão ('root'/'') do MySQL.
    |
    -->

    <!-- 
      8. SCRIPTS DE INICIALIZAÇÃO
       - Ativa as máscaras de input (iMask.js)
       - Ativa os modais corretos com base na resposta do PHP (Alpine.js)
    -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // --- 1. ATIVAR MÁSCARAS DE INPUT (UX PREMIUM) ---
            
            // Máscara de CPF no formulário principal
            const cpfMaskEl = document.getElementById('cpf');
            if (cpfMaskEl) {
                IMask(cpfMaskEl, { mask: '000.000.000-00' });
            }
            
            // Máscaras no formulário de revisão
            const revCpfMaskEl = document.getElementById('rev_cpf');
            if (revCpfMaskEl) {
                IMask(revCpfMaskEl, { mask: '000.000.000-00' });
            }

            const phoneMaskOptions = {
                mask: [
                    { mask: '(00) 0000-0000' },
                    { mask: '(00) 00000-0000' }
                ]
            };
            
            const revTelefoneMaskEl = document.getElementById('rev_telefone');
            if (revTelefoneMaskEl) {
                IMask(revTelefoneMaskEl, phoneMaskOptions);
            }
            
            const revWhatsappMaskEl = document.getElementById('rev_whatsapp');
            if (revWhatsappMaskEl) {
                IMask(revWhatsappMaskEl, phoneMaskOptions);
            }

            
            // --- 2. TRIGGER DOS MODAIS (PÓS-SUBMISSÃO PHP) ---
            
            // Pega o elemento principal do Alpine
            let alpineRoot = document.querySelector('[x-data]');
            if (!alpineRoot) return;

            // Pega o objeto de dados do Alpine
            let alpineData = alpineRoot.__x.data;

            <?php if (!empty($resultados)): // Se $resultados NÃO estiver vazio ?>
                alpineData.showResultadoModal = true;
            
            <?php elseif (!empty($erro_busca)): // Se $erro_busca NÃO estiver vazio ?>
                alpineData.showErroModal = true;
            
            <?php elseif (!empty($sucesso_revisao)): // Se $sucesso_revisao NÃO estiver vazio ?>
                alpineData.showSucessoModal = true;

            <?php elseif (!empty($erro_revisao)): // Se $erro_revisao NÃO estiver vazio ?>
                // Se deu erro na revisão, abre o modal de revisão de novo para mostrar o erro
                alpineData.showRevisaoModal = true;
            <?php endif; ?>
        });
    </script>

</body>
</html>