<?php
// --- CONFIGURAÇÃO DO BANCO DE DADOS (ATUALIZADO PARA XAMPP LOCAL) ---
$dbConfig = array(
    'host'   => 'localhost',        // MUDADO DE 'hoftalon.org.br'
    'port'   => '3306',             // Porta padrão do XAMPP/MySQL
    'dbname' => 'ptdb_sus',         // MUDADO DE 'hoftalon_dbptsus' (Nome do seu DB local)
    'user'   => 'root',             // MUDADO DE 'hoftalon_suporte' (Usuário padrão XAMPP)
    'pass'   => ''                  // MUDADO DE 'Hoftalon@2025#' (Senha padrão XAMPP)
);


// --- ROTEADOR DE REQUISIÇÕES AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Pega os dados JSON crus enviados pelo 'fetch' do JavaScript
    $input = json_decode(file_get_contents('php://input'), true);

    // Garante que o output será JSON
    header('Content-Type: application/json');

    try {
        // Conecta ao banco de dados de produção
        // ATUALIZADO: Adicionado 'port' ao DSN
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Roteamento baseado na 'action' enviada pelo JS
        if (isset($input['action'])) {

            // --- AÇÃO: BUSCAR PACIENTE (ATUALIZADO) ---
            if ($input['action'] === 'search') {
                $nome = $input['nome'] ?? '';
                $cpf = $input['cpf'] ?? '';
               // echo $cpf ."---".$nome;
                // SEGURANÇA: Limpa o CPF para conter apenas números
                $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

                // ATUALIZADO: Query agora usa a VIEW 'vw_consulta_fila_sus'
                // e mapeia os nomes das colunas (ex: 'nr_cpf')
                $sql = "
                    SELECT 
                        cd_fila AS id, 
                        dh_data_entrada AS data_entrada, 
                        nome_paciente AS nome, 
                        nr_cpf AS cpf,  -- Pega o CPF real para a máscara (será removido antes de enviar)
                        tp_olho AS olho, 
                        procedimento, 
                        dh_atualizacao AS data_atualizacao, 
                        nr_posicao AS posicao_fila, 
                        ds_obs AS observacoes 
                    FROM 
                        vw_consulta_fila_sus 
                    WHERE 
                        nr_cpf = ? AND nome_paciente LIKE ?
                ";

                $stmt = $pdo->prepare($sql);
                
                // O bindValue é mais seguro para o LIKE
                $stmt->bindValue(1, $cpf_limpo);
                $stmt->bindValue(2, '%' . $nome . '%');
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // SEGURANÇA (LGPD): Mascara o CPF (lógica inalterada, ainda funciona)
                $masked_results = array();
                foreach ($results as $row) {
                    if (!empty($row['cpf']) && strlen($row['cpf']) >= 11) { // Lógica de máscara
                        $row['cpf_mask'] = substr($row['cpf'], 0, 3) . '******' . substr($row['cpf'], -2);
                    } else {
                        $row['cpf_mask'] = '***.***.***-**';
                    }
                    // Remove o CPF original para não trafegar de volta
                    unset($row['cpf']); 
                    $masked_results[] = $row;
                }

                echo json_encode(array('success' => true, 'data' => $masked_results));
                exit; // Termina o script PHP aqui
            }

            // --- AÇÃO: SOLICITAR REVISÃO (ATUALIZADO) ---
            if ($input['action'] === 'review') {
                // SEGURANÇA: Limpa o CPF e telefones
                $cpf_limpo = preg_replace('/[^0-9]/', '', $input['cpf_revisao']);
                $telefone_limpo = preg_replace('/[^0-9]/', '', $input['telefone']);
                $whatsapp_limpo = preg_replace('/[^0-9]/', '', $input['whatsapp']);

                // Validação
                if (empty($input['nome_revisao']) || strlen($cpf_limpo) != 11 || empty($input['data_nasc']) || strlen($whatsapp_limpo) < 10) {
                    echo json_encode(array('success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios corretamente.'));
                    exit;
                }

                // ATUALIZADO: Query agora usa a TABELA 'solicitacao_revisao'
                // e mapeia os nomes das colunas (ex: 'nm_paciente')
                $sql = "
                    INSERT INTO solicitacao_revisao 
                        (nm_paciente, nr_cpf, dt_nascimento, ds_telefone, ds_whatsapp, ds_observacao, dh_solicitacao, tp_status) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?, NOW(), 'Pendente')
                ";
                
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute(array(
                    $input['nome_revisao'], // nm_paciente
                    $cpf_limpo,              // nr_cpf
                    $input['data_nasc'],    // dt_nascimento
                    $telefone_limpo,         // ds_telefone
                    $whatsapp_limpo,         // ds_whatsapp
                    $input['observacao']     // ds_observacao
                ));

                echo json_encode(array('success' => true, 'message' => 'Solicitação de revisão enviada com sucesso. Nossa equipe entrará em contato em breve.'));
                exit; // Termina o script
            }
        }

        // Se nenhuma ação válida foi encontrada
        echo json_encode(array('success' => false, 'message' => 'Ação inválida.'));
        exit;

    } catch (PDOException $e) {
        // Captura qualquer erro de banco de dados
        
        // --- MODO DE DEBUG ---
        // Mostra o erro real para o usuário.
        // ATENÇÃO: Isso deve ser removido ou alterado antes de ir para produção.
        echo json_encode(array('success' => false, 'message' => 'ERRO DE DEBUG (PHP): ' . $e->getMessage()));
        // ---------------------

        // Linha original comentada (para produção):
        // error_log("Erro de PDO: " . $e->getMessage()); // Loga o erro real no servidor
        // echo json_encode(array('success' => false, 'message' => 'Erro ao conectar com o servidor. Tente novamente mais tarde.'));
        exit;
    }
}

// --- EXECUÇÃO NORMAL (CARREGAMENTO DA PÁGINA) ---
// Se não for um POST, o PHP simplesmente "passa direto" e renderiza o HTML.
// A função setupDatabase() foi removida.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Transparência - Fila de Cirurgias</title>
    
    <!-- 
      CSS EMBUTIDO (INLINE)
      (Nenhuma alteração necessária no CSS)
    -->
    <style type="text/css">
        /* Fonte personalizada */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        /* Reset básico e Configurações Globais */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6; /* Cor de fundo suave */
            background-image: linear-gradient(to bottom right, #eff6ff, #f3f4f6);
            line-height: 1.6;
            color: #374151;
            min-height: 100vh;
        }

        /* --- Layout Principal --- */
        .header {
            background-image: linear-gradient(to right, #1d4ed8, #1e3a8a);
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 0 1rem;
        }
        .header-nav {
            max-width: 1120px; /* 70rem */
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 96px; /* 6rem */
        }
        .logo-container {
            display: flex;
            align-items: center;
        }
        .logo-svg {
            height: 40px; /* 2.5rem */
            width: auto;
            fill: white;
        }
        .logo-text {
            font-size: 1.875rem; /* 3xl */
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-left: 0.75rem;
        }
        .header-title {
            font-size: 1.5rem; /* 2xl */
            font-weight: 600;
            color: #dbeafe; /* blue-100 */
            opacity: 0.9;
        }

        .container {
            max-width: 896px; /* 56rem */
            margin: 0 auto;
            padding: 1rem;
            position: relative;
            z-index: 10;
            margin-top: -4.64rem; /* Efeito de sobreposição */
        }
        .logo_hoftalon{
            /* ATUALIZADO: Centraliza o logo acima do card */
            display: block;
            margin: 0 auto 1.12rem auto; /* Centraliza horizontalmente e adiciona margem inferior */
        }

        .main-card {
            background-color: white;
            padding: 2.5rem; /* 40px */
            border-radius: 0.75rem; /* 12px */
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            border: 1px solid #e5e7eb;
            transition: all 0.5s;
        }

        .footer {
            max-width: 896px;
            margin: 0 auto;
            padding: 2rem;
            margin-top: 3rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .footer p {
            margin-bottom: 0.25rem;
        }
        .footer a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        



        /* --- Formulários --- */
        .form-title {
            font-size: 1.875rem; /* 3xl */
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2rem;
            text-align: center;
            
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
            margin-left: 0.25rem;
        }

        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #000000ff;
            width: 20px;
            height: 20px;
        }
        
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem; /* 12px 16px 12px 48px */
            border: 1px solid #d1d5db;
            border-radius: 0.5rem; /* 8px */
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4);
        }

        textarea.input-field {
            padding: 0.75rem 1rem;
            height: 120px;
            resize: vertical;
        }





        /* --- Botões --- */
        .btn {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem; /* 8px */
            border: none;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background-image: linear-gradient(to right, #2563eb, #1d4ed8);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transform: translateY(0);
        }
        .btn-primary:hover {
            background-image: linear-gradient(to right, #1d4ed8, #1e3a8a);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }
        .btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.4);
        }
        .btn-primary:disabled {
            background: #9ca3af;
            opacity: 0.7;
            cursor: not-allowed;
            transform: translateY(0);
            box-shadow: none;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #1f2937;
        }
        .btn-secondary:hover {
            background-color: #d1d5db;
        }
        .btn-secondary:focus {
             outline: none;
             box-shadow: 0 0 0 4px rgba(209, 213, 219, 0.6);
        }

        .text-center {
            text-align: center;
        }
        .pt-6 {
            padding-top: 1.5rem;
        }





        /* Margem customizada para botão voltar */
        .mb-8 {
            margin-bottom: 2rem;
        }
        .text-sm {
            font-size: 0.875rem;
        }
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }





        /* --- Alerta LGPD --- */
        .alert-lgpd {
            background-color: #eff6ff; /* blue-50 */
            border-left: 4px solid #3b82f6; /* blue-500 */
            color: #1e3a8a; /* blue-800 */
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .alert-header {
            display: flex;
            align-items: center;
            font-size: 1.125rem;
            font-weight: 700;
        }
        .alert-header svg {
            width: 24px;
            height: 24px;
            margin-right: 0.75rem;
            stroke: #3b82f6;
        }
        .alert-lgpd p {
            font-size: 0.875rem;
            margin-top: 0.75rem;
        }
        .alert-lgpd ul {
            font-size: 0.875rem;
            margin-top: 0.75rem;
            list-style-position: inside;
            list-style-type: disc;
            padding-left: 0.5rem;
        }
        .alert-lgpd li {
            margin-bottom: 0.25rem;
        }
        .alert-lgpd a {
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }
        .alert-lgpd a:hover {
            text-decoration: underline;
        }




        /* --- Seção de Resultados --- */
        /* ATUALIZAÇÃO: Tabela (Desktop) simplificada, sem scroll */
        .results-table-container {
            overflow-x: auto; /* Mantém 'auto' por segurança, mas não deve ser necessário */
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .results-table {
            width: 100%;
            /* min-width removido para evitar scroll */
            border-collapse: collapse;
        }
        .results-table th {
            background-color: #f9fafb; /* gray-100 */
            padding: 0.75rem 1rem; /* Padding reduzido */
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap; /* Títulos não quebram */
        }
        .results-table td {
            padding: 1rem 1rem; /* Padding reduzido */
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.875rem;
            color: #374151;
            white-space: nowrap;
        }
        .results-table tbody tr:hover {
            background-color: #f9fafb;
            transition: background-color 0.2s;
        }
        .results-table .col-nome {
            font-weight: 500;
            color: #111827;
            white-space: normal; /* Nomes podem quebrar */
        }
        .results-table .col-procedimento {
            white-space: normal; /* Procedimentos podem quebrar */
            min-width: 150px; /* Largura mínima para procedimento */
        }
        .results-table .col-posicao {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1d4ed8;
            text-align: center;
        }
        



        /* --- NOVA LISTA RESPONSIVA (CELULAR / TABLET) --- */
        .responsive-results-list {
            display: none; /* Escondido por padrão */
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .result-card {
            background-image: linear-gradient(to right, #ffffffff, #d7e1ffff );
            padding: 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            border:  0.82px solid #00000098;
        }
        .result-card:last-child {
            border-bottom: none;
        }
        .result-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        .result-card-header .procedimento {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.4;
        }
        .result-card-header .posicao-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.1;
            padding: 0.5rem 0.75rem;
            background-color: #eff6ff;
            border-radius: 0.5rem;
            border: 1px solid #dbeafe;
        }
        .result-card-header .posicao-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #1d4ed8;
        }
        .result-card-header .posicao {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1d4ed8;
        }
        .result-card-body {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Duas colunas */
            gap: 1rem;
        }
        .result-data-item {
            font-size: 0.875rem;
        }
        .result-data-item .label {
            font-weight: 500;
            color: #6b7280; /* gray-500 */
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }
        .result-data-item .value {
            color: #111827;
            font-weight: 600;
            word-break: break-word;
        }
        .result-data-item .value.cpf-mask {
            font-family: monospace;
        }
        /* Observações e Nome ocupam a largura toda */
        .result-data-item.full-width {
            grid-column: 1 / -1;
        }




        /* --- MEDIA QUERY PARA RESPONSIVIDADE DA TABELA --- */
        /* ATUALIZADO: Breakpoint maior (1024px) */
        /* Em telas de tablet ou menores, esconde a tabela e mostra os cards */
        @media (max-width: 1024px) {
            .results-table-container {
                display: none;
            }
            .responsive-results-list {
                display: block;
            }
        }
        



        /* --- Mensagem "Sem Resultados" --- */
        .no-results-message {
            text-align: center;
            padding: 2rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .no-results-message svg {
            width: 80px;
            height: 80px;
            color: #93c5fd; /* blue-300 */
            margin: 0 auto;
        }
        .no-results-message .title {
            margin-top: 1.25rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
        .no-results-message .subtitle {
            margin-top: 0.75rem;
            color: #6b7280;
            max-width: 448px;
            margin-left: auto;
            margin-right: auto;
        }
        .no-results-message .btn {
            margin-top: 2rem;
        }




        /* --- Modal (Dialog) --- */
        /* MELHORIA: Força o modal a ser centralizado e bonito */
        .modal {
            padding: 0;
            border-radius: 0.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.69);
            width: 100%;
            max-width: 672px; /* max-w-2xl */
            border: none;
            
            /* Correção de centralização */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-height: 95vh; /* Altura máxima */
            overflow-y: auto; /* Rolagem interna */
        }
        .modal::backdrop {
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            padding: 2.5rem;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        .modal-close-btn {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
        }
        .modal-close-btn:hover {
            color: #1f2937;
        }
        .modal-close-btn svg {
            width: 24px;
            height: 24px;
        }
        .modal-body p {
            color: #4b5563;
            margin-bottom: 1.5rem;
        }
        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        .modal-footer {
            display: flex;
            flex-direction: column-reverse;
            gap: 1rem;
            padding-top: 1rem;
        }
        @media (min-width: 768px) {
            .modal-footer {
                flex-direction: row;
                justify-content: flex-end;
            }
        }




        /* --- Toast (Notificação) --- */
        .toast-message {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 50;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.21);
            color: white;
            max-width: 448px;
            transition: all 0.3s ease-in-out;
        }
        .toast-success {
            background-color: #10b981; /* green-500 */
        }
        .toast-error {
            background-color: #ef4444; /* red-500 */
        }
        



        /* --- Loader (Spinner) --- */
        .loader {
            width: 24px;
            height: 24px;
            border: 4px solid #e5e7eb; /* gray-200 */
            border-top-color: #3b82f6; /* blue-500 */
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .loader-light {
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: #ffffff;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }




        /* --- Utilitários --- */
        .hidden {
            display: none;
        }
        



        /* Responsividade */
        @media (max-width: 768px) {
            .header-title {
                display: none;
            }
            .container {
                margin-top: -1rem;
                padding: 0.5rem;
            }
            .main-card {
                padding: 1.5rem;
            }
            .form-title {
                font-size: 1.5rem;
            }
            .modal {
                width: 95%; /* Modal ocupa 95% da tela no mobile */
            }
            .modal-content {
                padding: 1.5rem;
            }
            .modal-title {
                font-size: 1.25rem;
            }
            .btn {
                width: 100%;
            }
            .modal-footer {
                gap: 0.5rem;
            }
            .modal-footer .btn {
                width: 100%;
            }
        }
        
    </style>
</head>
<body>

    <!-- Header (Cabeçalho) -->
    <header class="header">
        <nav class="header-nav">
            <!-- Logo Placeholder -->
            <!--
            <div class="logo-container">
                <svg class="logo-svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
                <span class="logo-text">Hoftalon</span>
            </div>
            -->
            <!-- Título do Header -->
            <div class="header-title">
                
            </div>
        </nav>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">

        <!-- Notificação Flutuante (Toast) para mensagens -->
        <div id="toast-message" class="toast-message hidden" role="alert">
            <span id="toast-text"></span>
        </div>

        <div id="main-content" class="main-card">
            
            <div>
                <img class="logo_hoftalon" id="logo_hoftalon" src="https://hoftalon.com.br/wp-content/uploads/2022/10/hoftalon-logo@2x-300x147.png" style="width: 250px;" alt="Hospital Hoftalon">
            </div>
            <div id="search-section">
                <h2 class="form-title">Portal da Transparência</h2>

                <div class="alert-lgpd" role="alert">
                    <div class="alert-header">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>INFORMATIVO (LGPD)</span>
                    </div>
                    <p>Conforme Lei nº 13.709/2018 (Lei Geral de Proteção de Dados Pessoais), informamos:</p>
                    <ul>
                        <li>A coleta de dados é exclusiva para identificação do usuário na fila de espera.</li>
                        <li>Os dados podem incluir: Nome, Nome da Mãe, Data de Nascimento, CNS e CPF.</li>
                        <li>Dúvidas? Entre em contato: <a href="mailto:dpo@hoftalon.com.br">dpo@hoftalon.com.br</a></li>
                    </ul>
                </div>
                
                <form id="searchForm" action="/teste1.php" method="get">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome Completo do Paciente</label>
                        <div class="input-group">
                            <!-- Ícone SVG Usuário -->
                            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <input type="text" id="nome" name="nome" required 
                                   class="input-field" 
                                   placeholder="Digite o nome completo">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cpf" class="form-label">CPF do Paciente</label>
                         <div class="input-group">
                            <!-- Ícone SVG Documento -->
                                <svg class="input-icon" width="px" height="100px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path d="M2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H14C17.7712 4 19.6569 4 20.8284 5.17157C22 6.34315 22 8.22876 22 12C22 15.7712 22 17.6569 20.8284 18.8284C19.6569 20 17.7712 20 14 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M10 16.5H6" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M8 13.5H6" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M2 10L22 10" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> 
                                <path opacity="0.5" d="M14 15C14 14.0572 14 13.5858 14.2929 13.2929C14.5858 13 15.0572 13 16 13C16.9428 13 17.4142 13 17.7071 13.2929C18 13.5858 18 14.0572 18 15C18 15.9428 18 16.4142 17.7071 16.7071C17.4142 17 16.9428 17 16 17C15.0572 17 14.5858 17 14.2929 16.7071C14 16.4142 14 15.9428 14 15Z" stroke="#1C274C" stroke-width="1.5"></path> </g></svg>                            
                                <input type="text" id="cpf" name="cpf" required 
                                   class="input-field" 
                                   placeholder="000.000.000-00"
                                   maxlength="14">
                        </div>
                    </div>
                    <div class="text-center pt-6">
                        <button type="submit" id="searchButton" class="btn btn-primary">
                            <span id="button-text">Buscar</span>
                            <div id="button-loader" class="loader loader-light hidden"></div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. Área de Resultados (Oculta por padrão) -->
            <div id="results-section" class="hidden">
                <button onclick="showSearchSection()" class="btn btn-secondary mb-8 text-sm py-2 px-4">
                    &larr; Voltar para a Busca
                </button>
                <h2 class="form-title">Resultado do Paciente </h2>
                
                <!-- Container da Tabela (Desktop) -->
                <div id="resultsTableContainer" class="results-table-container">
                    <!-- A tabela será injetada aqui pelo JavaScript -->
                </div>
                
                <!-- Container dos Cards (Mobile / Tablet) -->
                <div id="resultsListContainer" class="responsive-results-list">
                    <!-- Os cards serão injetados aqui -->
                </div>
                
                <!-- 
                  Mensagem de "Nenhum Resultado" (Esta é a "segunda tela")
                  Ela só aparece se a busca não retornar NADA.
                -->
                <div id="noResultsMessage" class="no-results-message hidden">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="title">Nenhum paciente encontrado</p>
                    <p class="subtitle">Não encontramos registros para o nome e CPF informados. Se você acredita que isso é um erro, por favor, solicite uma revisão.</p>
                    <button onclick="openReviewModal()" class="btn btn-primary">
                        Solicitar Revisão de Cadastro
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer (Rodapé) -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Hoftalon - Hospital de Olhos. Todos os direitos reservados.</p>
        <p>Este é um portal de transparência em conformidade com a LGPD.</p>
        <p>DPO: <a href="mailto:dpo@hoftalon.com.br">dpo@hoftalon.com.br</a></p>
    </footer>

    <!-- 
      MODAL DE SOLICITAÇÃO DE REVISÃO
      Usa o elemento <dialog> do HTML5 para fácil controle.
    -->
    <dialog id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Solicitação de Revisão de Cadastro</h3>
                <button onclick="closeReviewModal()" class="modal-close-btn">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="modal-body">
                <p>Se você já foi atendido mas não encontrou seu nome na fila, preencha o formulário abaixo para que nossa equipe possa analisar seu cadastro.</p>
            
                <form id="reviewForm" class="modal-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome_revisao" class="form-label">Nome Completo <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="nome_revisao" required class="input-field" style="padding-left: 1rem;">
                        </div>
                        <div class="form-group">
                            <label for="cpf_revisao" class="form-label">CPF <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="cpf_revisao" placeholder="000.000.000-00" required class="input-field" style="padding-left: 1rem;" maxlength="14">
                        </div>
                    </div>
                     <div class="form-grid">
                        <div class="form-group">
                            <label for="data_nasc" class="form-label">Data de Nascimento <span style="color: #ef4444;">*</span></label>
                            <input type="date" id="data_nasc" required class="input-field" style="padding-left: 1rem;" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="telefone" class="form-label">Telefone (Opcional)</label>
                            <input type="tel" id="telefone" placeholder="(00) 0000-0000" class="input-field" style="padding-left: 1rem;" maxlength="15">
                        </div>
                    </div>
                    <div class="form-group">
                         <label for="whatsapp" class="form-label">WhatsApp (com DDD) <span style="color: #ef4444;">*</span></label>
                         <input type="tel" id="whatsapp" placeholder="(00) 90000-0000" required class="input-field" style="padding-left: 1rem;" maxlength="15">
                    </div>
                    <div class="form-group">
                        <label for="observacao" class="form-label">Observação (Opcional)</label>
                        <textarea id="observacao" rows="4" placeholder="Descreva brevemente seu caso. Ex: Fui atendido em tal data, meu procedimento é..." class="input-field" style="padding-left: 1rem;"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="closeReviewModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" id="reviewSubmitButton" class="btn btn-primary">
                            <span id="review-button-text">Enviar Solicitação</span>
                            <div id="review-button-loader" class="loader loader-light hidden"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>


    <!-- ============================================== -->
    <!-- JAVASCRIPT (FRONTEND)                      -->
    <!-- (Nenhuma alteração necessária no JS)         -->
    <!-- ============================================== -->
    <script>
        // --- Seletores de Elementos ---
        const searchSection = document.getElementById('search-section');
        const resultsSection = document.getElementById('results-section');
        const searchForm = document.getElementById('searchForm');
        const searchButton = document.getElementById('searchButton');
        const buttonText = document.getElementById('button-text');
        const buttonLoader = document.getElementById('button-loader');
        
        const resultsTableContainer = document.getElementById('resultsTableContainer');
        const resultsListContainer = document.getElementById('resultsListContainer'); // NOVO
        const noResultsMessage = document.getElementById('noResultsMessage');
        
        const reviewModal = document.getElementById('reviewModal');
        const reviewForm = document.getElementById('reviewForm');
        const reviewSubmitButton = document.getElementById('reviewSubmitButton');
        const reviewButtonText = document.getElementById('review-button-text');
        const reviewButtonLoader = document.getElementById('review-button-loader');
        
        const toastMessage = document.getElementById('toast-message');
        const toastText = document.getElementById('toast-text');
        
        // Seletores dos campos com máscara
        const cpfInput = document.getElementById('cpf');
        const cpfRevisaoInput = document.getElementById('cpf_revisao');
        const telefoneInput = document.getElementById('telefone');
        const whatsappInput = document.getElementById('whatsapp');

        // --- Funções de UI (Mostrar/Esconder Seções) ---
        
        function showSearchSection() {
            resultsSection.classList.add('hidden');
            searchSection.classList.remove('hidden');
            // Limpa os resultados anteriores e o formulário
            resultsTableContainer.innerHTML = '';
            resultsListContainer.innerHTML = ''; // NOVO: Limpa os cards
            noResultsMessage.classList.add('hidden');
            searchForm.reset();
        }

        function showResultsSection() {
            searchSection.classList.add('hidden');
            resultsSection.classList.remove('hidden');
        }
        
        function showLoading(button, textEl, loaderEl) {
            button.disabled = true;
            textEl.classList.add('hidden');
            loaderEl.classList.remove('hidden');
        }
        
        function hideLoading(button, textEl, loaderEl) {
            button.disabled = false;
            textEl.classList.remove('hidden');
            loaderEl.classList.add('hidden');
        }

        /**
         * Mostra uma mensagem flutuante (toast)
         * @param {string} message - O texto a ser exibido
         * @param {string} type - 'success' (verde) or 'error' (vermelho)
         */
        function showToast(message, type = 'success') {
            toastText.textContent = message;
            
            // Remove classes antigas antes de adicionar novas
            toastMessage.classList.remove('toast-success', 'toast-error');
            
            if (type === 'success') {
                toastMessage.classList.add('toast-success');
            } else {
                toastMessage.classList.add('toast-error');
            }
            
            toastMessage.classList.remove('hidden');
            
            // Esconde a mensagem após 5 segundos
            setTimeout(() => {
                toastMessage.classList.add('hidden');
            }, 5000);
        }

        // --- Funções dos Modais ---
        
        function openReviewModal() {
            // Preenche o formulário de revisão com os dados da busca, se disponíveis
            document.getElementById('nome_revisao').value = document.getElementById('nome').value;
            // Aplica a máscara no CPF que veio da busca
            document.getElementById('cpf_revisao').value = maskCPF(document.getElementById('cpf').value);
            reviewModal.showModal();
        }
        
        function closeReviewModal() {
            reviewForm.reset(); // Limpa o formulário
            reviewModal.close();
        }

        // --- Lógica de Submissão (AJAX) ---

        /**
         * Event Listener para o formulário de BUSCA
         */
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Impede o recarregamento da página
            showLoading(searchButton, buttonText, buttonLoader);
            
            const nome = document.getElementById('nome').value;
            const cpf = document.getElementById('cpf').value;
            
            try {
                // Simula um pequeno atraso para o loader ser visível (melhora UX)
                await new Promise(resolve => setTimeout(resolve, 500));

                const response = await fetch('teste1.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'search',
                        nome: nome,
                        cpf: cpf // O PHP no backend vai limpar a máscara
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Falha na resposta do servidor.');
                }
                
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    displayResults(result.data);
                } else if (result.success) {
                    displayNoResults();
                } else {
                    throw new Error(result.message || 'Erro ao processar a busca.');
                }
                
            } catch (error) {
                console.error('Erro na busca:', error);
                




                // MUDANÇA CRÍTICA:
                // Mesmo se houver um erro de servidor (PHP, rede, etc.),
                // mostramos a tela de "não encontrado" para que o usuário
                // possa pelo menos solicitar a revisão manual.
                showToast(error.message || 'Erro no servidor. Verifique seus dados ou solicite uma revisão.', 'error');
                displayNoResults(); // <-- Garante que o usuário possa pedir revisão
                
            } finally {
                hideLoading(searchButton, buttonText, buttonLoader);
            }
        });

        /**
         * Renderiza a tabela de resultados (Desktop) E a lista de cards (Mobile)
         * @param {Array} data - Array de objetos de pacientes
         */
        function displayResults(data) {
            
            // ATUALIZAÇÃO: A tabela desktop agora é simplificada
            let tableHTML = `
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Nome</th>
                            <th>Procedimento</th>
                            <th>Olho</th>
                            <th>Data Entrada</th>
                            <th>Atualização</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            let listHTML = ''; // String para os cards
            
            data.forEach(row => {
                // 1. Constrói a linha da TABELA (Desktop Simplificada)
                tableHTML += `
                    <tr>
                        <td class="col-posicao">${escapeHTML(row.posicao_fila)}</td>
                        <td class="col-nome">${escapeHTML(row.nome)}</td>
                        <td class="col-procedimento">${escapeHTML(row.procedimento)}</td>
                        <td>${escapeHTML(row.olho)}</td>
                        <td>${formatarData(row.data_entrada)}</td>
                        <td>${formatarData(row.data_atualizacao)}</td>
                    </tr>
                `;
                



                // 2. Constrói o CARD (Mobile / Tablet)
                listHTML += `
                    <div class="result-card">
                        <div class="result-card-header">
                            <span class="procedimento">${escapeHTML(row.procedimento)}</span>
                            <div class="posicao-wrapper">
                                <span class="posicao-label">POSIÇÃO</span>
                                <span class="posicao">${escapeHTML(row.posicao_fila)}</span>
                            </div>
                        </div>
                        <div class="result-card-body">
                            <div class="result-data-item full-width">
                                <span class="label">Paciente</span>
                                <span class="value">${escapeHTML(row.nome)}</span>
                            </div>
                            <div class="result-data-item">
                                <span class="label">CPF</span>
                                <span class="value cpf-mask">${escapeHTML(row.cpf_mask)}</span>
                            </div>
                            <div class="result-data-item">
                                <span class="label">Olho</span>
                                <span class="value">${escapeHTML(row.olho)}</span>
                            </div>
                            <div class="result-data-item">
                                <span class="label">Data Entrada</span>
                                <span class="value">${formatarData(row.data_entrada)}</span>
                            </div>
                            <div class="result-data-item">
                                <span class="label">Últ. Atualização</span>
                                <span class="value">${formatarData(row.data_atualizacao)}</span>
                            </div>
                            <div class="result-data-item full-width">
                                <span class="label">Observações</span>
                                <span class="value">${escapeHTML(row.observacoes) || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            tableHTML += `</tbody></table>`;
          
            


            // Injeta o HTML em AMBOS os containers
            resultsTableContainer.innerHTML = tableHTML;
            resultsListContainer.innerHTML = listHTML;
            
            noResultsMessage.classList.add('hidden');
            showResultsSection();
        }




        /**
         * Função de segurança para escapar HTML e prevenir XSS
         * @param {string} str - A string para escapar
         */
        function escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }




        /**
         * Formata data (YYYY-MM-DD HH:MM:SS) para (DD/MM/YYYY HH:MM) ou (DD/MM/YYYY)
         */
        function formatarData(dataISO) {
            if (!dataISO) return 'N/A';
            try {
                // Tenta corrigir datas que podem vir sem 'T'
                const dataObj = new Date(dataISO.replace(' ', 'T') + 'Z'); // Trata como UTC
                
                if (isNaN(dataObj.getTime())) return dataISO; // Retorna original se inválida
                
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    timeZone: 'America/Sao_Paulo' // Fuso horário de Londrina/Brasil
                };
                
                // Verifica se a data tem parte de hora relevante (não é meia-noite)
                if (dataISO.includes(' ') && !dataISO.includes('00:00:00')) {
                    options.hour = '2-digit';
                    options.minute = '2-digit';
                }
                
                return dataObj.toLocaleString('pt-BR', options);
            } catch (e) {
                return dataISO; // Retorna original em caso de erro
            }
        }



        /**
         * Mostra a mensagem de "nenhum resultado"
         */
        function displayNoResults() {
            resultsTableContainer.innerHTML = '';
            resultsListContainer.innerHTML = ''; // NOVO: Limpa os cards
            noResultsMessage.classList.remove('hidden');
            showResultsSection();
        }
        



        /**
         * Event Listener para o formulário de REVISÃO
         */
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            showLoading(reviewSubmitButton, reviewButtonText, reviewButtonLoader);
            
            const formData = {
                action: 'review',
                nome_revisao: document.getElementById('nome_revisao').value,
                cpf_revisao: document.getElementById('cpf_revisao').value,
                data_nasc: document.getElementById('data_nasc').value,
                telefone: document.getElementById('telefone').value,
                whatsapp: document.getElementById('whatsapp').value,
                observacao: document.getElementById('observacao').value,
            };
            
            try {
                // Simula um pequeno atraso
                await new Promise(resolve => setTimeout(resolve, 500));

                const response = await fetch('teste1.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                if (!response.ok) {
                    throw new Error('Falha na resposta do servidor.');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    closeReviewModal();
                    // Volta para a tela de busca após sucesso
                    showSearchSection();
                    showToast(result.message || 'Solicitação enviada com sucesso!', 'success');
                } else {
                    // Mostra o erro DENTRO do modal
                    showToast(result.message || 'Erro ao enviar a solicitação.', 'error');
                }
                
            } catch (error) {
                console.error('Erro na revisão:', error);


                // Mostra o erro DENTRO do modal



                showToast(`Erro: ${error.message}`, 'error');
            } finally {
                hideLoading(reviewSubmitButton, reviewButtonText, reviewButtonLoader);
            }
        });
        



        // --- MÁSCARAS DE INPUT ---
        
        /**
         * Formata um valor de CPF (000.000.000-00)
         * @param {string} value - O valor a ser formatado
         * @returns {string} - O valor com a máscara
         */
        function maskCPF(value) {
            if (!value) return "";
            value = value.replace(/\D/g, ''); // Remove tudo que não é dígito
            value = value.replace(/(\d{3})(\d)/, '$1.$2'); // 000.
            value = value.replace(/(\d{3})(\d)/, '$1.$2'); // 000.000.
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // 000.000.000-00
            return value;
        }




        
        /**
         * Formata um valor de Telefone (00) 00000-0000
         * @param {string} value - O valor a ser formatado
         * @returns {string} - O valor com a máscara
         */
        function maskPhone(value) {
            if (!value) return "";
            value = value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2'); // (00)
            if (value.length > 13) { // Celular com 9 dígitos
                value = value.replace(/(\d{5})(\d)/, '$1-$2'); // (00) 90000-0000
            } else { // Fixo ou celular antigo
                value = value.replace(/(\d{4})(\d)/, '$1-$2'); // (00) 0000-0000
            }
            return value;
        }




        // Aplica os listeners de máscara
        cpfInput.addEventListener('input', (e) => {
            e.target.value = maskCPF(e.target.value);
        });
        
        cpfRevisaoInput.addEventListener('input', (e) => {
            e.target.value = maskCPF(e.target.value);
        });
        
        telefoneInput.addEventListener('input', (e) => {
            e.target.value = maskPhone(e.target.value);
        });
        
        whatsappInput.addEventListener('input', (e) => {
            e.target.value = maskPhone(e.target.value);
        });
        
    </script>

</body>
</html>