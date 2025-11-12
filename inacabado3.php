<?php
// SISTEMA ITAM/ITSM COMPLETO - VERSÃO 3.2
// Banco de dados: claude_db
// Desenvolvido para XAMPP
//
// === LOG DE ALTERAÇÕES v3.2 (Baseado na v3.1.1) ===
//
// --- NOVAS CONSTANTES (Erro 7) ---
// - Adicionadas constantes PHP para padronização de nomenclatura de ativos.
//   (LOC_CIDADES, LOC_PREDIOS, LOC_SETORES, LOC_TIPOS)
//
// --- MÓDULO: INVENTÁRIO (Erro 1) ---
// - CORREÇÃO: Modal 'Adicionar Item' agora funcional.
// - NOVO: Implementado CRUD completo para Inventário (Editar e Excluir).
// - MELHORIA: Tabela 'inventario_componentes' reestruturada.
//   - 'tipo_componente' alterado de ENUM para VARCHAR(50).
//   - Adicionada coluna 'categoria' (Infra, Peças, Licenças, Outro) para classificação.
//   - Adicionada coluna 'observacoes'.
// - MELHORIA: Página 'page=inventario' agora possui filtros por Categoria.
// - MELHORIA: Modal 'Adicionar' e nova página 'Editar' refletem a nova estrutura.
//
// --- MÓDULO: DASHBOARD (Erro 2 & 4) ---
// - CORREÇÃO (Erro 2): Gráfico 'Por Tipo' (DSK/NTB) agora carrega corretamente.
// - MELHORIA (Erro 4): 'Alertas Recentes' agora atualiza via AJAX a cada 30 segundos.
//   - 'carregarAlertas()' é chamado no load da página.
//
// --- MÓDULO: ATIVOS (Erro 6, 7 & 9) ---
// - MELHORIA (Erro 7): Nomenclatura e Filtros.
//   - Modal 'Adicionar Ativo' agora usa <select> (dropdowns) com as constantes
//     (Cidade, Prédio, Setor, Tipo/Posição) para padronizar a nomenclatura.
//   - Página 'page=ativos' agora possui filtros por 'Unidade' (Prédio) e 'Setor'.
// - MELHORIA (Erro 9): Observações.
//   - Adicionada coluna 'observacoes' (TEXT) na tabela 'ativos_hardware'.
//   - Campo 'Observações' adicionado ao 'Modal Adicionar' e 'Página Editar'.
//   - Observações agora são visíveis na 'Página Detalhes'.
// - CORREÇÃO (Erro 6): Trava de Manutenção.
//   - O botão 'Registrar Manutenção' na 'Página Detalhes' é desabilitado
//     automaticamente se houver uma manutenção 'Agendada' ou 'Em Andamento'.
//
// --- MÓDULO: AUDITORIA (Erro 5 & 8) ---
// - MELHORIA (Erro 5): Página 'page=auditoria' agora se chama 'Relatório de Auditoria'.
// - NOVO (Erro 5): Adicionados filtros por Ação, Tabela e Intervalo de Datas.
// - MELHORIA (Erro 8): A página de auditoria agora serve como o "Relatório de Alterações"
//   solicitado.
//
// --- GERAL (Erro 3) ---
// - MELHORIA (Erro 3): Adicionados mais ícones (Font Awesome) em cabeçalhos,
//   tabelas e botões para melhorar a UX.
// =======================================================

// ===== CONFIGURAÇÕES =====
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'claude_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações do Sistema
define('SISTEMA_NOME', 'ITAM System Pro');
define('SISTEMA_VERSAO', '3.2.0'); // Versão atualizada
define('UPLOAD_PATH', __DIR__ . '/uploads');
define('BACKUP_PATH', __DIR__ . '/backups');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// ===== NOVAS CONSTANTES (ERRO 7) =====
// Mapa de correlação para nomenclatura de ativos
define('LOC_CIDADES', ['LD', 'IB']);
define('LOC_PREDIOS', ['MTZ', 'ADN', 'FDN', 'AYS', 'CMB', 'MRG', 'AEP', 'IBP']);
define('LOC_SETORES', [
    'RECP' => 'Recepção',
    'ADMN' => 'Administração',
    'FNCR' => 'Financeiro',
    'OUVD' => 'Ouvidoria',
    'SAME' => 'Arquivo (SAME)',
    'TI' => 'TI',
    'CONS' => 'Consultório',
    'AMBL' => 'Ambulatório',
    'CTDG' => 'Centro de Diagnóstico',
    'AGEN' => 'Agendamento',
    'DO' => 'Dep. Ocupacional (RH)',
    'COMP' => 'Compras',
    'ESTQ' => 'Estoque',
    'FARM' => 'Farmácia',
    'SERV' => 'Serviços (Facilities)',
    'OUTR' => 'Outros/Temporário'
]);
define('LOC_TIPOS', [
    'ESQ' => 'Esquerda',
    'DRT' => 'Direita',
    'CTL' => 'Central',
    'FRT' => 'Frente',
    'FDS' => 'Fundos',
    'SALA' => 'Sala',
    'CTN' => 'Container',
    'LAB' => 'Laboratório',
    'OUTR' => 'Outro'
]);

// ===== CONEXÃO COM BANCO =====
$conn_temp = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn_temp->connect_error) {
    die("Erro de conexão: " . $conn_temp->connect_error);
}

// Criar banco de dados se não existir
$conn_temp->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn_temp->close();

// Conectar ao banco claude_db
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ===== FUNÇÕES AUXILIARES =====
function sanitize($input) {
    global $conn;
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    // Corrigido para tratar null
    if ($input === null) {
        return null;
    }
    return htmlspecialchars(trim($conn->real_escape_string((string)$input)), ENT_QUOTES, 'UTF-8');
}

function formatar_data($data, $formato = 'd/m/Y') {
    if (empty($data) || $data === '0000-00-00' || $data === null) {
        return '-';
    }
    return date($formato, strtotime($data));
}

function formatar_moeda($valor) {
    if ($valor === null || $valor === '') {
        return 'R$ 0,00';
    }
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function gerar_nome_maquina($cidade, $predio, $setor, $tipo, $id) {
    // Validação para garantir que os componentes não estão vazios
    if (empty($cidade) || empty($predio) || empty($setor) || empty($tipo) || empty($id)) {
        return false;
    }
    return strtoupper($cidade . '-' . $predio . '-' . $setor . '-' . $tipo . '-' . str_pad($id, 2, '0', STR_PAD_LEFT));
}

function calcular_idade_dias($data_aquisicao) {
    if (empty($data_aquisicao) || $data_aquisicao === '0000-00-00' || $data_aquisicao === null) {
        return 0;
    }
    $data = new DateTime($data_aquisicao);
    $hoje = new DateTime();
    return $data->diff($hoje)->days;
}

function get_status_badge_class($status) {
    switch (strtolower($status ?? '')) {
        case 'ativo': return 'bg-success';
        case 'manutenção':
        case 'manutencao': return 'bg-warning';
        case 'desativado': return 'bg-danger';
        case 'estoque': return 'bg-info';
        default: return 'bg-secondary';
    }
}

function get_risco_badge_class($risco) {
    switch (strtolower($risco ?? '')) {
        case 'crítico':
        case 'critico': return 'bg-danger';
        case 'alto': return 'bg-warning';
        case 'médio':
        case 'medio': return 'bg-info';
        case 'baixo': return 'bg-success';
        default: return 'bg-secondary';
    }
}

function get_chamado_badge_class($status) {
    switch (strtolower($status ?? '')) {
        case 'aberto': return 'bg-danger';
        case 'em andamento': return 'bg-warning';
        case 'fechado': return 'bg-success';
        default: return 'bg-secondary';
    }
}

// ===== FUNÇÕES DO SISTEMA =====
function criarTabelas() {
    global $conn;
    
    // Tabela de usuários
    $conn->query("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_completo VARCHAR(150) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        setor VARCHAR(50),
        status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo',
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tabela de ativos de hardware (ATUALIZADA V3.2 - Erro 9)
    $conn->query("CREATE TABLE IF NOT EXISTS ativos_hardware (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_maquina VARCHAR(100) UNIQUE NOT NULL,
        loc_cidade VARCHAR(10) NOT NULL,
        loc_predio VARCHAR(10) NOT NULL,
        loc_setor VARCHAR(20) NOT NULL,
        loc_tipo VARCHAR(10) NOT NULL,
        loc_id INT NOT NULL,
        status ENUM('Ativo', 'Manutenção', 'Estoque', 'Desativado') DEFAULT 'Ativo',
        tipo_ativo ENUM('DSK', 'NTB') DEFAULT 'DSK',
        modelo_cpu VARCHAR(100),
        memoria_gb INT,
        modelo_disco_1 VARCHAR(100),
        serial_disco_1 VARCHAR(100),
        data_aquisicao DATE,
        custo_aquisicao DECIMAL(10,2),
        data_ultima_manutencao DATE NULL,
        data_proxima_manutencao DATE NULL,
        ip_address VARCHAR(15),
        sistema_operacional VARCHAR(50),
        garantia_fim DATE NULL,
        usuario_id_atual INT NULL,
        observacoes TEXT NULL, /* NOVO (Erro 9) */
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_nome_maquina (nome_maquina),
        INDEX idx_status (status),
        INDEX idx_localizacao (loc_cidade, loc_predio, loc_setor),
        INDEX idx_tipo_ativo (tipo_ativo),
        FOREIGN KEY (usuario_id_atual) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Adicionar coluna 'observacoes' se ela não existir (Erro 9)
    $conn->query("ALTER TABLE ativos_hardware ADD COLUMN IF NOT EXISTS observacoes TEXT NULL AFTER usuario_id_atual");

    // Tabela de logs de saúde
    $conn->query("CREATE TABLE IF NOT EXISTS logs_saude (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_maquina VARCHAR(100) NOT NULL,
        metric_type VARCHAR(50) NOT NULL,
        metric_value DECIMAL(10,2),
        metric_unit VARCHAR(20),
        alerta TINYINT(1) DEFAULT 0,
        gravidade ENUM('Baixo', 'Médio', 'Alto', 'Crítico') DEFAULT 'Baixo',
        mensagem TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_nome_maquina (nome_maquina),
        FOREIGN KEY (nome_maquina) REFERENCES ativos_hardware(nome_maquina) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de manutenções
    $conn->query("CREATE TABLE IF NOT EXISTS manutencoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_maquina VARCHAR(100) NOT NULL,
        data_manutencao DATE NOT NULL,
        tipo_manutencao ENUM('Preventiva', 'Corretiva', 'Preditiva', 'Emergencial') DEFAULT 'Preventiva',
        descricao TEXT,
        tecnico_responsavel VARCHAR(100),
        custo DECIMAL(10,2) DEFAULT 0,
        duracao_horas INT DEFAULT 0,
        pecas_substituidas TEXT,
        status ENUM('Agendada', 'Em Andamento', 'Concluída', 'Cancelada') DEFAULT 'Agendada',
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_nome_maquina (nome_maquina),
        FOREIGN KEY (nome_maquina) REFERENCES ativos_hardware(nome_maquina) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de componentes
    $conn->query("CREATE TABLE IF NOT EXISTS componentes_ativos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_maquina VARCHAR(100) NOT NULL,
        componente_nome VARCHAR(100) NOT NULL,
        tipo_componente VARCHAR(50),
        fabricante VARCHAR(100),
        modelo VARCHAR(100),
        numero_serie VARCHAR(100),
        data_instalacao DATE,
        data_prevista_falha DATE,
        vida_util_estimada INT,
        custo_componente DECIMAL(10,2),
        observacoes TEXT,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_nome_maquina (nome_maquina),
        FOREIGN KEY (nome_maquina) REFERENCES ativos_hardware(nome_maquina) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de chamados
    $conn->query("CREATE TABLE IF NOT EXISTS chamados_ti (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_maquina VARCHAR(100) NOT NULL,
        usuario_id_relator INT NULL,
        data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        categoria ENUM('Hardware', 'Software', 'Rede', 'Impressora', 'Outro') DEFAULT 'Hardware',
        descricao TEXT NOT NULL,
        status ENUM('Aberto', 'Em Andamento', 'Fechado') DEFAULT 'Aberto',
        solucao TEXT NULL,
        data_fechamento DATETIME NULL,
        INDEX idx_nome_maquina (nome_maquina),
        INDEX idx_status (status),
        FOREIGN KEY (nome_maquina) REFERENCES ativos_hardware(nome_maquina) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id_relator) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tabela de configurações
    $conn->query("CREATE TABLE IF NOT EXISTS configuracoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        descricao TEXT,
        categoria VARCHAR(50) DEFAULT 'Geral',
        tipo ENUM('texto', 'numero', 'boolean', 'email', 'data') DEFAULT 'texto',
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de logs do sistema (Auditoria)
    $conn->query("CREATE TABLE IF NOT EXISTS logs_sistema (
        id INT AUTO_INCREMENT PRIMARY KEY,
        acao VARCHAR(100) NOT NULL,
        tabela VARCHAR(50),
        registro_id VARCHAR(100),
        dados_antigos TEXT,
        dados_novos TEXT,
        ip VARCHAR(45),
        user_agent TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tabela de Inventário/Armazém (ATUALIZADA V3.2 - Erro 1)
    $conn->query("CREATE TABLE IF NOT EXISTS inventario_componentes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_item VARCHAR(150) NOT NULL,
        categoria VARCHAR(50) DEFAULT 'Outro', /* NOVO: Infra, Peças, Licenças */
        tipo_componente VARCHAR(50), /* ALTERADO: de ENUM para VARCHAR */
        fabricante VARCHAR(100) NULL,
        modelo VARCHAR(100) NULL,
        numero_serie VARCHAR(100) NULL,
        status ENUM('Estoque', 'Em Uso', 'Defeito', 'Descartado') DEFAULT 'Estoque',
        localizacao VARCHAR(100) DEFAULT 'Armazem TI',
        quantidade INT DEFAULT 1,
        data_aquisicao DATE NULL,
        custo DECIMAL(10,2) DEFAULT 0,
        observacoes TEXT, /* NOVO */
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_categoria (categoria),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Aplicar alterações da V3.2 (Erro 1) se a tabela já existir
    $conn->query("ALTER TABLE inventario_componentes ADD COLUMN IF NOT EXISTS categoria VARCHAR(50) DEFAULT 'Outro' AFTER nome_item");
    $conn->query("ALTER TABLE inventario_componentes ADD COLUMN IF NOT EXISTS observacoes TEXT NULL AFTER custo");
    $conn->query("ALTER TABLE inventario_componentes MODIFY COLUMN tipo_componente VARCHAR(50)");


    // Inserir configurações padrão
    $configuracoes_padrao = [
        ['sistema_nome', 'ITAM System Pro', 'Nome do sistema', 'Sistema', 'texto'],
        ['paginacao', '10', 'Registros por página', 'Interface', 'numero']
    ];
    
    foreach ($configuracoes_padrao as $config) {
        $stmt = $conn->prepare("INSERT IGNORE INTO configuracoes (chave, valor, descricao, categoria, tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $config[0], $config[1], $config[2], $config[3], $config[4]);
        $stmt->execute();
        $stmt->close();
    }

    // Tentar adicionar a coluna 'usuario_id_atual' se a tabela 'ativos_hardware' já existir
    $conn->query("ALTER TABLE ativos_hardware ADD COLUMN IF NOT EXISTS usuario_id_atual INT NULL AFTER garantia_fim, ADD INDEX IF NOT EXISTS idx_usuario_id (usuario_id_atual)");
    // Tentar adicionar a FK se ela não existir
    $result = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'ativos_hardware' AND COLUMN_NAME = 'usuario_id_atual' AND REFERENCED_TABLE_NAME = 'usuarios'");
    if ($result->num_rows == 0) {
        // Ignora o erro se a FK já existir (para execuções repetidas)
        $conn->query("ALTER TABLE ativos_hardware ADD CONSTRAINT fk_usuario_atual 
                        FOREIGN KEY (usuario_id_atual) REFERENCES usuarios(id) ON DELETE SET NULL");
    }
}

/**
 * Insere dados de exemplo v3.2
 * Adiciona usuários, chamados e itens de inventário
 */
function inserirDadosExemplo() {
    global $conn;

    // Inserir usuários de exemplo
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    if ($result->fetch_assoc()['total'] == 0) {
        $usuarios_exemplo = [
            ['Maria Silva', 'maria.silva@empresa.com', 'Recepção'],
            ['João Pereira', 'joao.pereira@empresa.com', 'Administrativo'],
            ['Técnico TI', 'ti@empresa.com', 'TI']
        ];
        foreach ($usuarios_exemplo as $user) {
            $stmt = $conn->prepare("INSERT INTO usuarios (nome_completo, email, setor) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $user[0], $user[1], $user[2]);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Pega os IDs dos usuários de exemplo para atribuir
    $user1_result = $conn->query("SELECT id FROM usuarios WHERE email = 'maria.silva@empresa.com'");
    $user1 = $user1_result->num_rows > 0 ? $user1_result->fetch_assoc()['id'] : null;

    $user2_result = $conn->query("SELECT id FROM usuarios WHERE email = 'joao.pereira@empresa.com'");
    $user2 = $user2_result->num_rows > 0 ? $user2_result->fetch_assoc()['id'] : null;

    if ($conn->query("SELECT COUNT(*) as total FROM ativos_hardware")->fetch_assoc()['total'] == 0) {
        // Usando a nova nomenclatura (Erro 7)
        $ativos_exemplo = [
            [
                'LD-MTZ-RECP-ESQ-01', 'LD', 'MTZ', 'RECP', 'ESQ', 1, 'Ativo', 'DSK',
                'Intel i5-10400', 8, 'SSD Kingston 256GB', 'SSD123456789',
                '2023-01-15', 2500.00, null, '192.168.1.101', 'Windows 10 Pro', '2025-01-15', $user1, 'Máquina da recepção esquerda.'
            ],
            [
                'LD-MTZ-ADMN-SALA-01', 'LD', 'MTZ', 'ADMN', 'SALA', 1, 'Ativo', 'NTB',
                'Intel i7-1165G7', 16, 'SSD Samsung 512GB', 'SSD987654321',
                '2023-03-20', 4500.00, null, '192.168.1.102', 'Windows 11 Pro', '2026-03-20', $user2, null
            ],
            [
                'LD-MTZ-TI-LAB-01', 'LD', 'MTZ', 'TI', 'LAB', 1, 'Manutenção', 'DSK',
                'Intel i5-8400', 8, 'HDD WD 1TB', 'HDD456789123',
                '2022-06-10', 1800.00, '2024-01-10', '192.168.1.103', 'Ubuntu 22.04 LTS', '2024-06-10', null, 'Bancada de testes.'
            ]
        ];
        
        foreach ($ativos_exemplo as $ativo) {
            $stmt = $conn->prepare("INSERT INTO ativos_hardware (nome_maquina, loc_cidade, loc_predio, loc_setor, loc_tipo, loc_id, status, tipo_ativo, modelo_cpu, memoria_gb, modelo_disco_1, serial_disco_1, data_aquisicao, custo_aquisicao, data_ultima_manutencao, ip_address, sistema_operacional, garantia_fim, usuario_id_atual, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // 20 colunas (?), 20 tipos na string.
            $stmt->bind_param("sssssisssisssdssssis", 
                $ativo[0], $ativo[1], $ativo[2], $ativo[3], $ativo[4], $ativo[5],
                $ativo[6], $ativo[7], $ativo[8], $ativo[9], $ativo[10], $ativo[11],
                $ativo[12], $ativo[13], $ativo[14], $ativo[15], $ativo[16], $ativo[17],
                $ativo[18], $ativo[19]
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Inserir logs de saúde de exemplo
    if ($conn->query("SELECT COUNT(*) as total FROM logs_saude")->fetch_assoc()['total'] == 0) {
        $logs_exemplo = [
            ['LD-MTZ-RECP-ESQ-01', 'CPU_TEMP', 65.5, '°C', 0, 'Baixo', 'Temperatura CPU normal'],
            ['LD-MTZ-ADMN-SALA-01', 'DISK_FREE_SPACE', 15.2, '%', 1, 'Alto', 'Espaço em disco baixo'],
            ['LD-MTZ-TI-LAB-01', 'MEMORY_USAGE', 92.8, '%', 1, 'Crítico', 'Uso de memória crítico']
        ];
        
        foreach ($logs_exemplo as $log) {
            $stmt = $conn->prepare("INSERT INTO logs_saude (nome_maquina, metric_type, metric_value, metric_unit, alerta, gravidade, mensagem) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsiss", $log[0], $log[1], $log[2], $log[3], $log[4], $log[5], $log[6]);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Inserir chamados de exemplo
    if ($conn->query("SELECT COUNT(*) as total FROM chamados_ti")->fetch_assoc()['total'] == 0 && $user1 && $user2) {
        $chamados_exemplo = [
            ['LD-MTZ-RECP-ESQ-01', $user1, 'Software', 'Computador muito lento para abrir o sistema.', 'Aberto'],
            ['LD-MTZ-ADMN-SALA-01', $user2, 'Hardware', 'Tela do notebook está piscando.', 'Aberto']
        ];
        foreach ($chamados_exemplo as $chamado) {
            $stmt = $conn->prepare("INSERT INTO chamados_ti (nome_maquina, usuario_id_relator, categoria, descricao, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $chamado[0], $chamado[1], $chamado[2], $chamado[3], $chamado[4]);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Inserir itens de inventário de exemplo (v3.2 - Erro 1)
    if ($conn->query("SELECT COUNT(*) as total FROM inventario_componentes")->fetch_assoc()['total'] == 0) {
        $inventario_exemplo = [
            // Categoria 'Peças'
            ['Teclado ABNT2 USB', 'Peças', 'Teclado', 'Dell', 'KB216', null, 'Estoque', 'Armazem TI', 10, '2024-01-01', 80.00, 'Pedido 1234'],
            ['Mouse Óptico USB', 'Peças', 'Mouse', 'Logitech', 'M90', null, 'Estoque', 'Armazem TI', 15, '2024-01-01', 45.00, 'Pedido 1234'],
            // Categoria 'Infra'
            ['Cabo de Força 3P', 'Infra', 'Cabo', 'N/A', 'N/A', null, 'Estoque', 'Prateleira A1', 50, '2023-01-01', 15.00, null],
            ['Cabo de Rede CAT6 3m', 'Infra', 'Cabo', 'Furukawa', 'CAT6', null, 'Estoque', 'Prateleira A2', 30, '2023-01-01', 25.00, null],
            // Categoria 'Licenças'
            ['Licença Windows 11 Pro', 'Licenças', 'SO', 'Microsoft', 'Win 11 Pro OEM', 'XXXXX-XXXXX-XXXXX-XXXXX-12345', 'Em Uso', 'Atribuída ao LD-MTZ-ADMN-SALA-01', 1, '2023-03-20', 800.00, null]
        ];
        foreach ($inventario_exemplo as $item) {
            $stmt = $conn->prepare("INSERT INTO inventario_componentes (nome_item, categoria, tipo_componente, fabricante, modelo, numero_serie, status, localizacao, quantidade, data_aquisicao, custo, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssisdss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[8], $item[9], $item[10], $item[11]);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * ATUALIZADO V3.1: Adicionado Estoque e Desativados
 */
function getDashboardStats() {
    global $conn;
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM ativos_hardware");
    $stats['total_ativos'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT status, COUNT(*) as quantidade FROM ativos_hardware GROUP BY status");
    $stats['status_grafico'] = $result->fetch_all(MYSQLI_ASSOC);
    
    $result = $conn->query("SELECT CONCAT(loc_cidade, '-', loc_predio) as localizacao, COUNT(*) as quantidade FROM ativos_hardware GROUP BY localizacao ORDER BY quantidade DESC LIMIT 10");
    $stats['localizacao_grafico'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // NOVO: Chamados Abertos
    $result = $conn->query("SELECT COUNT(*) as total FROM chamados_ti WHERE status = 'Aberto'");
    $stats['chamados_abertos'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM ativos_hardware WHERE status = 'Manutenção'");
    $stats['em_manutencao'] = $result->fetch_assoc()['total'];

    // NOVO: Em Estoque
    $result = $conn->query("SELECT COUNT(*) as total FROM ativos_hardware WHERE status = 'Estoque'");
    $stats['em_estoque'] = $result->fetch_assoc()['total'];

    // NOVO: Desativados
    $result = $conn->query("SELECT COUNT(*) as total FROM ativos_hardware WHERE status = 'Desativado'");
    $stats['desativados'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE status = 'Ativo'");
    $stats['total_usuarios'] = $result->fetch_assoc()['total'];

    // CORREÇÃO (Erro 2): Query estava correta, o problema era o JS.
    $result = $conn->query("SELECT tipo_ativo, COUNT(*) as quantidade FROM ativos_hardware GROUP BY tipo_ativo");
    $stats['tipo_grafico'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $stats;
}

function getAlertasRecentes($limite = 10) {
    global $conn;
    $sql = "SELECT ls.*, ah.modelo_cpu, ah.tipo_ativo FROM logs_saude ls 
            LEFT JOIN ativos_hardware ah ON ls.nome_maquina = ah.nome_maquina 
            WHERE ls.alerta = 1 
            ORDER BY ls.timestamp DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $alertas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $alertas;
}

// ATUALIZADO V3.2 (Erro 9): Adicionado 'observacoes'
function adicionarAtivo($dados) {
    global $conn;
    
    // Gerar nome da máquina automaticamente
    $nome_maquina = gerar_nome_maquina(
        $dados['loc_cidade'],
        $dados['loc_predio'],
        $dados['loc_setor'],
        $dados['loc_tipo'],
        $dados['loc_id']
    );

    if ($nome_maquina === false) {
         return ['success' => false, 'error' => 'Campos de localização inválidos.'];
    }
    
    $sql = "INSERT INTO ativos_hardware (nome_maquina, loc_cidade, loc_predio, loc_setor, loc_tipo, loc_id, status, tipo_ativo, modelo_cpu, memoria_gb, modelo_disco_1, serial_disco_1, data_aquisicao, custo_aquisicao, ip_address, sistema_operacional, garantia_fim, usuario_id_atual, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => $conn->error];
    }
    
    $ip = !empty($dados['ip_address']) ? $dados['ip_address'] : null;
    $garantia = !empty($dados['garantia_fim']) ? $dados['garantia_fim'] : null;
    $aquisicao = !empty($dados['data_aquisicao']) ? $dados['data_aquisicao'] : null;
    $custo = !empty($dados['custo_aquisicao']) ? $dados['custo_aquisicao'] : 0.00;
    $usuario_id = !empty($dados['usuario_id_atual']) && $dados['usuario_id_atual'] > 0 ? $dados['usuario_id_atual'] : null;
    $observacoes = !empty($dados['observacoes']) ? $dados['observacoes'] : null;

    $stmt->bind_param("sssssisssisssdsssis", 
        $nome_maquina, $dados['loc_cidade'], $dados['loc_predio'], 
        $dados['loc_setor'], $dados['loc_tipo'], $dados['loc_id'], 
        $dados['status'], $dados['tipo_ativo'], $dados['modelo_cpu'], 
        $dados['memoria_gb'], $dados['modelo_disco_1'], $dados['serial_disco_1'], 
        $aquisicao, $custo, $ip, 
        $dados['sistema_operacional'], $garantia, $usuario_id, $observacoes
    );
    
    $resultado = $stmt->execute();
    
    if ($resultado) {
        $stmt->close();
        registrarLog('Inserir', 'ativos_hardware', $nome_maquina, null, json_encode($dados));
        return ['success' => true, 'nome_maquina' => $nome_maquina];
    }
    
    $error = $conn->error;
    $stmt->close();
    return ['success' => false, 'error' => $error];
}

// ATUALIZADO: Inclui LEFT JOIN para pegar nome do usuário
function getAtivoByNome($nome) {
    global $conn;
    $sql = "SELECT ah.*, u.nome_completo as nome_usuario_atual 
            FROM ativos_hardware ah 
            LEFT JOIN usuarios u ON ah.usuario_id_atual = u.id 
            WHERE ah.nome_maquina = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $ativo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $ativo;
}

// ATUALIZADO V3.2 (Erro 9): Inclui 'observacoes'
function atualizarAtivo($nome_maquina, $dados) {
    global $conn;
    
    $dados_antigos = getAtivoByNome($nome_maquina);
    
    $data_manutencao = !empty($dados['data_ultima_manutencao']) ? $dados['data_ultima_manutencao'] : null;
    $ip = !empty($dados['ip_address']) ? $dados['ip_address'] : null;
    $garantia = !empty($dados['garantia_fim']) ? $dados['garantia_fim'] : null;
    $usuario_id = !empty($dados['usuario_id_atual']) && $dados['usuario_id_atual'] > 0 ? $dados['usuario_id_atual'] : null;
    $observacoes = !empty($dados['observacoes']) ? $dados['observacoes'] : null;
    
    $sql = "UPDATE ativos_hardware SET status = ?, tipo_ativo = ?, modelo_cpu = ?, memoria_gb = ?, modelo_disco_1 = ?, serial_disco_1 = ?, data_ultima_manutencao = ?, ip_address = ?, sistema_operacional = ?, garantia_fim = ?, usuario_id_atual = ?, observacoes = ? WHERE nome_maquina = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissssssiss", 
        $dados['status'], $dados['tipo_ativo'], $dados['modelo_cpu'], 
        $dados['memoria_gb'], $dados['modelo_disco_1'], $dados['serial_disco_1'], 
        $data_manutencao, $ip, $dados['sistema_operacional'], 
        $garantia, $usuario_id, $observacoes, $nome_maquina
    );
    
    $resultado = $stmt->execute();
    $stmt->close();
    
    if ($resultado) {
        registrarLog('Atualizar', 'ativos_hardware', $nome_maquina, json_encode($dados_antigos), json_encode($dados));
    }
    
    return $resultado;
}

function excluirAtivo($nome_maquina) {
    global $conn;
    $dados_antigos = getAtivoByNome($nome_maquina);
    $stmt = $conn->prepare("DELETE FROM ativos_hardware WHERE nome_maquina = ?");
    $stmt->bind_param("s", $nome_maquina);
    $resultado = $stmt->execute();
    $stmt->close();
    if ($resultado) {
        registrarLog('Excluir', 'ativos_hardware', $nome_maquina, json_encode($dados_antigos), null);
    }
    return $resultado;
}

// ===== FUNÇÕES V3.0 (USUÁRIOS) ATUALIZADAS V3.1 =====
function getUsuarios() {
    global $conn;
    $result = $conn->query("SELECT * FROM usuarios ORDER BY nome_completo");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUsuarioById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function adicionarUsuario($dados) {
    global $conn;
    $sql = "INSERT INTO usuarios (nome_completo, email, setor, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $dados['nome_completo'], $dados['email'], $dados['setor'], $dados['status']);
    $resultado = $stmt->execute();
    $novo_id = $conn->insert_id;
    
    if ($resultado) {
        $stmt->close();
        registrarLog('Inserir', 'usuarios', $novo_id, null, json_encode($dados));
        return ['success' => true];
    }
    $error = $conn->error;
    $stmt->close();
    return ['success' => false, 'error' => $error];
}

function atualizarUsuario($id, $dados) {
    global $conn;
    $dados_antigos = getUsuarioById($id);
    $sql = "UPDATE usuarios SET nome_completo = ?, email = ?, setor = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $dados['nome_completo'], $dados['email'], $dados['setor'], $dados['status'], $id);
    $resultado = $stmt->execute();
    $stmt->close();
    
    if ($resultado) {
        registrarLog('Atualizar', 'usuarios', $id, json_encode($dados_antigos), json_encode($dados));
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

function excluirUsuario($id) {
    global $conn;
    $dados_antigos = getUsuarioById($id);
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $resultado = $stmt->execute();
    $stmt->close();
    if ($resultado) {
        registrarLog('Excluir', 'usuarios', $id, json_encode($dados_antigos), null);
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// ===== NOVAS FUNÇÕES V3.0 (CHAMADOS) =====
function getChamados($filtros = []) {
    global $conn;
    $where = "1=1";
    if (!empty($filtros['status'])) {
        $where .= " AND c.status = '" . $conn->real_escape_string($filtros['status']) . "'";
    }
    
    $sql = "SELECT c.*, u.nome_completo as nome_relator 
            FROM chamados_ti c 
            LEFT JOIN usuarios u ON c.usuario_id_relator = u.id 
            WHERE $where
            ORDER BY c.status ASC, c.data_abertura DESC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getChamadoById($id) {
    global $conn;
    $sql = "SELECT c.*, u.nome_completo as nome_relator, u.email, u.setor 
            FROM chamados_ti c 
            LEFT JOIN usuarios u ON c.usuario_id_relator = u.id 
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * ATUALIZADO V3.1: Removido LIMIT 10 (Erro 3)
 */
function getChamadosPorMaquina($nome_maquina) {
    global $conn;
    $sql = "SELECT c.*, u.nome_completo as nome_relator 
            FROM chamados_ti c 
            LEFT JOIN usuarios u ON c.usuario_id_relator = u.id 
            WHERE c.nome_maquina = ?
            ORDER BY c.data_abertura DESC"; // LIMIT 10 removido
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome_maquina);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * NOVO V3.1: (Erro 5)
 */
function getChamadosPorUsuario($usuario_id) {
    global $conn;
    $sql = "SELECT c.*, u.nome_completo as nome_relator 
            FROM chamados_ti c 
            LEFT JOIN usuarios u ON c.usuario_id_relator = u.id 
            WHERE c.usuario_id_relator = ?
            ORDER BY c.data_abertura DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function adicionarChamado($dados) {
    global $conn;
    $sql = "INSERT INTO chamados_ti (nome_maquina, usuario_id_relator, categoria, descricao, status) 
            VALUES (?, ?, ?, ?, 'Aberto')";
    $stmt = $conn->prepare($sql);
    $usuario_id = !empty($dados['usuario_id_relator']) ? $dados['usuario_id_relator'] : null;
    $stmt->bind_param("siss", $dados['nome_maquina'], $usuario_id, $dados['categoria'], $dados['descricao']);
    $resultado = $stmt->execute();
    $novo_id = $conn->insert_id;
    $stmt->close();
    
    if ($resultado) {
        registrarLog('Inserir', 'chamados_ti', $novo_id, null, json_encode($dados));
        return ['success' => true, 'id' => $novo_id];
    }
    return ['success' => false, 'error' => $conn->error];
}

function fecharChamado($id, $solucao, $status = 'Fechado') {
    global $conn;
    $sql = "UPDATE chamados_ti SET solucao = ?, status = ?, data_fechamento = NOW() WHERE id = ?";
    if ($status != 'Fechado') {
        // Se reabrir ou manter em andamento, não atualiza data de fechamento
        $sql = "UPDATE chamados_ti SET solucao = ?, status = ?, data_fechamento = NULL WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $solucao, $status, $id);
    $resultado = $stmt->execute();
    $stmt->close();
    
    if ($resultado) {
        registrarLog('Atualizar', 'chamados_ti', $id, null, json_encode(['status' => $status, 'solucao' => $solucao]));
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// ===== FUNÇÕES V3.1 (Manutenções) =====
function getManutencoesPorMaquina($nome_maquina) {
    global $conn;
    $sql = "SELECT * FROM manutencoes WHERE nome_maquina = ? ORDER BY data_manutencao DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome_maquina);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * NOVO V3.2: (Erro 6)
 * Verifica se existe manutenção 'Agendada' ou 'Em Andamento'
 */
function checkManutencaoAberta($nome_maquina) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM manutencoes WHERE nome_maquina = ? AND (status = 'Agendada' OR status = 'Em Andamento')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome_maquina);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $resultado['total'] > 0;
}


function adicionarManutencao($dados) {
    global $conn;

    // (Erro 6) Trava para não adicionar nova manutenção se já houver uma aberta
    if (checkManutencaoAberta($dados['nome_maquina'])) {
        return ['success' => false, 'error' => 'Já existe uma manutenção "Em Andamento" or "Agendada" para este ativo.'];
    }

    $sql = "INSERT INTO manutencoes (nome_maquina, data_manutencao, tipo_manutencao, descricao, tecnico_responsavel, custo, pecas_substituidas, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $custo = !empty($dados['custo']) ? floatval($dados['custo']) : 0.00;
    
    $stmt->bind_param("sssssdss", 
        $dados['nome_maquina'], $dados['data_manutencao'], $dados['tipo_manutencao'],
        $dados['descricao'], $dados['tecnico_responsavel'], $custo,
        $dados['pecas_substituidas'], $dados['status']
    );
    
    $resultado = $stmt->execute();
    $novo_id = $conn->insert_id;
    
    if ($resultado) {
        // Se concluiu, atualiza data_ultima_manutencao no ativo
        if ($dados['status2'] === 'Concluída') {
            $conn->query("UPDATE ativos_hardware SET data_ultima_manutencao = '" . $dados['data_manutencao'] . "' WHERE nome_maquina = '" . $dados['nome_maquina'] . "'");
        }
        $stmt->close();
        registrarLog('Inserir', 'manutencoes', $novo_id, null, json_encode($dados));
        return ['success' => true];
    }
    
    $error = $conn->error;
    $stmt->close();
    return ['success' => false, 'error' => $error];
}


// ===== FUNÇÕES V3.2 (Inventário - Erro 1) =====
function getInventario($filtros = []) {
    global $conn;
    $where = "1=1";
    if (!empty($filtros['categoria'])) {
        $where .= " AND categoria = '" . $conn->real_escape_string($filtros['categoria']) . "'";
    }
    $sql = "SELECT * FROM inventario_componentes WHERE $where ORDER BY categoria, nome_item";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getInventarioItemById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM inventario_componentes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function adicionarItemInventario($dados) {
    global $conn;
    $sql = "INSERT INTO inventario_componentes (nome_item, categoria, tipo_componente, fabricante, modelo, numero_serie, status, localizacao, quantidade, data_aquisicao, custo, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $data_aquisicao = !empty($dados['data_aquisicao']) ? $dados['data_aquisicao'] : null;
    $custo = !empty($dados['custo']) ? floatval($dados['custo']) : 0.00;
    $quantidade = !empty($dados['quantidade']) ? intval($dados['quantidade']) : 1;
    $obs = !empty($dados['observacoes']) ? $dados['observacoes'] : null;
    
    $stmt->bind_param("sssssssisdss",
        $dados['nome_item'], $dados['categoria'], $dados['tipo_componente'], $dados['fabricante'], $dados['modelo'], 
        $dados['numero_serie'], $dados['status'], $dados['localizacao'], $quantidade, 
        $data_aquisicao, $custo, $obs
    );
    
    $resultado = $stmt->execute();
    $novo_id = $conn->insert_id;
    
    if ($resultado) {
        $stmt->close();
        registrarLog('Inserir', 'inventario_componentes', $novo_id, null, json_encode($dados));
        return ['success' => true];
    }
    $error = $conn->error;
    $stmt->close();
    return ['success' => false, 'error' => $error];
}

function atualizarItemInventario($id, $dados) {
    global $conn;
    $dados_antigos = getInventarioItemById($id);
    $sql = "UPDATE inventario_componentes SET nome_item = ?, categoria = ?, tipo_componente = ?, fabricante = ?, modelo = ?, numero_serie = ?, status = ?, localizacao = ?, quantidade = ?, data_aquisicao = ?, custo = ?, observacoes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    $data_aquisicao = !empty($dados['data_aquisicao']) ? $dados['data_aquisicao'] : null;
    $custo = !empty($dados['custo']) ? floatval($dados['custo']) : 0.00;
    $quantidade = !empty($dados['quantidade']) ? intval($dados['quantidade']) : 1;
    $obs = !empty($dados['observacoes']) ? $dados['observacoes'] : null;

    $stmt->bind_param("sssssssisdssi",
        $dados['nome_item'], $dados['categoria'], $dados['tipo_componente'], $dados['fabricante'], $dados['modelo'], 
        $dados['numero_serie'], $dados['status'], $dados['localizacao'], $quantidade, 
        $data_aquisicao, $custo, $obs,
        $id
    );

    $resultado = $stmt->execute();
    $stmt->close();
    
    if ($resultado) {
        registrarLog('Atualizar', 'inventario_componentes', $id, json_encode($dados_antigos), json_encode($dados));
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

function excluirItemInventario($id) {
    global $conn;
    $dados_antigos = getInventarioItemById($id);
    $stmt = $conn->prepare("DELETE FROM inventario_componentes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $resultado = $stmt->execute();
    $stmt->close();
    if ($resultado) {
        registrarLog('Excluir', 'inventario_componentes', $id, json_encode($dados_antigos), null);
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}


// ===== FUNÇÕES V3.2 (Auditoria - Erro 5) =====
function getLogsSistema($filtros = []) {
    global $conn;
    $where = "1=1";
    $params = [];
    $types = "";

    if (!empty($filtros['acao'])) {
        $where .= " AND acao = ?";
        $params[] = $filtros['acao'];
        $types .= "s";
    }
    if (!empty($filtros['tabela'])) {
        $where .= " AND tabela = ?";
        $params[] = $filtros['tabela'];
        $types .= "s";
    }
    if (!empty($filtros['data_inicio'])) {
        $where .= " AND timestamp >= ?";
        $params[] = $filtros['data_inicio'] . ' 00:00:00';
        $types .= "s";
    }
    if (!empty($filtros['data_fim'])) {
        $where .= " AND timestamp <= ?";
        $params[] = $filtros['data_fim'] . ' 23:59:59';
        $types .= "s";
    }

    $limite = $filtros['limite'] ?? 100;
    $where .= " ORDER BY timestamp DESC LIMIT ?";
    $params[] = $limite;
    $types .= "i";

    $sql = "SELECT * FROM logs_sistema WHERE $where";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


/**
 * ATUALIZADO V3.1.1 (CORREÇÃO Erro 10)
 */
function preverFalha($nome_maquina) {
    global $conn;
    $ativo = getAtivoByNome($nome_maquina);
    if (!$ativo) {
        return ['success' => false, 'error' => 'Ativo não encontrado'];
    }
    
    // === INÍCIO DA CORREÇÃO (Erro 10) ===
    // Verificar se há manutenção concluída recente
    $stmt_manut = $conn->prepare("SELECT data_manutencao FROM manutencoes WHERE nome_maquina = ? AND status = 'Concluída' ORDER BY data_manutencao DESC LIMIT 1");
    $stmt_manut->bind_param("s", $nome_maquina);
    $stmt_manut->execute();
    $ultima_manut = $stmt_manut->get_result()->fetch_assoc();
    $stmt_manut->close();

    // Atualizar data_ultima_manutencao no ativo se a data for mais recente
    if ($ultima_manut && (!$ativo['data_ultima_manutencao'] || $ultima_manut['data_manutencao'] > $ativo['data_ultima_manutencao'])) {
        $stmt_update = $conn->prepare("UPDATE ativos_hardware SET data_ultima_manutencao = ? WHERE nome_maquina = ?");
        $stmt_update->bind_param("ss", $ultima_manut['data_manutencao'], $nome_maquina);
        $stmt_update->execute();
        $stmt_update->close();
        // Recarregar os dados do ativo para refletir a data atualizada
        $ativo = getAtivoByNome($nome_maquina);
    }
    
    // Usar a data da última manutenção (se houver) ou a data de aquisição para calcular a "idade"
    $data_referencia = $ativo['data_ultima_manutencao'] ?? $ativo['data_aquisicao'];
    $idade_dias = calcular_idade_dias($data_referencia);
    // === FIM DA CORREÇÃO ===
    
    // Contar alertas de hardware
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM logs_saude WHERE nome_maquina = ? AND alerta = 1 AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->bind_param("s", $nome_maquina);
    $stmt->execute();
    $alertas_recentes = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    // NOVO: Contar chamados de TI
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados_ti WHERE nome_maquina = ? AND data_abertura >= DATE_SUB(NOW(), INTERVAL 90 DAY)");
    $stmt->bind_param("s", $nome_maquina);
    $stmt->execute();
    $chamados_recentes = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $risco = 'Baixo';
    $confianca = 70;
    $recomendacao = 'Manutenção preventiva recomendada em 6 meses.';
    
    if ($idade_dias > 1095) { // 3 anos
        $risco = 'Alto';
        $confianca = 85;
        $recomendacao = 'Substituição recomendada. Equipamento próximo ao fim de vida útil.';
    } elseif ($idade_dias > 730) { // 2 anos
        $risco = 'Médio';
        $confianca = 75;
        $recomendacao = 'Monitoramento intensivo. Manutenção preventiva em 3 meses.';
    }
    
    // Lógica de "Mega Dados" v2
    if ($chamados_recentes > 5) {
        $risco = 'Crítico';
        $confianca = 98;
        $recomendacao = 'Ação imediata. Equipamento com histórico crítico de falhas de usuário/software. Considere formatação ou substituição.';
    } elseif ($chamados_recentes > 2) {
        $risco = 'Alto';
        $confianca = 90;
        $recomendacao = 'Investigar. Múltiplos chamados abertos recentemente.';
    }

    if ($alertas_recentes > 2) {
        $risco = 'Crítico';
        $confianca = 95;
        $recomendacao = 'Ação imediata necessária. Múltiplos alertas de hardware detectados.';
    }
    
    $alerta_value = ($risco === 'Crítico' || $risco === 'Alto') ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO logs_saude (nome_maquina, metric_type, metric_value, metric_unit, alerta, gravidade, mensagem) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $metric_type = 'PREDICAO_FALHA_V2';
    $metric_unit = '%';
    $stmt->bind_param("ssdsiss", $nome_maquina, $metric_type, $confianca, $metric_unit, $alerta_value, $risco, $recomendacao);
    $stmt->execute();
    $stmt->close();
    
    return [
        'success' => true,
        'previsao' => [
            'risco' => $risco,
            'confianca' => $confianca,
            'recomendacao' => $recomendacao,
            'idade_dias' => $idade_dias, // Agora é dias desde a última manutenção
            'alertas_recentes' => $alertas_recentes,
            'chamados_recentes' => $chamados_recentes
        ]
    ];
}


function registrarLog($acao, $tabela, $registro_id, $dados_antigos, $dados_novos) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO logs_sistema (acao, tabela, registro_id, dados_antigos, dados_novos, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $acao, $tabela, $registro_id, $dados_antigos, $dados_novos, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}


/**
 * Busca ativos com base em filtros.
 * ATUALIZADO V3.2: (Erro 7) Adicionados filtros de 'loc_predio' e 'loc_setor'.
 */
function getRelatorioAtivos($filtros = []) {
    global $conn;
    
    $where = "1=1";
    $params = [];
    $types = "";
    
    // Filtro de Busca (usado na pág. Ativos)
    if (!empty($filtros['busca'])) {
        $busca = "%" . $filtros['busca'] . "%";
        // Adicionamos 'ah.' aqui para evitar ambiguidade
        $where .= " AND (ah.nome_maquina LIKE ? OR ah.modelo_cpu LIKE ? OR ah.loc_setor LIKE ? OR ah.ip_address LIKE ? OR u.nome_completo LIKE ?)";
        array_push($params, $busca, $busca, $busca, $busca, $busca);
        $types .= "sssss";
    }

    if (!empty($filtros['status'])) {
        $where .= " AND ah.status = ?";
        $params[] = $filtros['status'];
        $types .= "s";
    }
    
    if (!empty($filtros['tipo_ativo'])) {
        $where .= " AND ah.tipo_ativo = ?";
        $params[] = $filtros['tipo_ativo'];
        $types .= "s";
    }
    
    // (Erro 7) Filtro de Localização (Prédio)
    if (!empty($filtros['loc_predio'])) {
        $where .= " AND ah.loc_predio = ?";
        $params[] = $filtros['loc_predio'];
        $types .= "s";
    }
    
    // (Erro 7) Filtro de Setor
    if (!empty($filtros['loc_setor'])) {
        $where .= " AND ah.loc_setor = ?";
        $params[] = $filtros['loc_setor'];
        $types .= "s";
    }
    
    if (!empty($filtros['data_inicio'])) {
        $where .= " AND ah.data_aquisicao >= ?";
        $params[] = $filtros['data_inicio'];
        $types .= "s";
    }
    
    if (!empty($filtros['data_fim'])) {
        $where .= " AND ah.data_aquisicao <= ?";
        $params[] = $filtros['data_fim'];
        $types .= "s";
    }
    
    $sql = "SELECT ah.*, u.nome_completo as nome_usuario 
            FROM ativos_hardware ah
            LEFT JOIN usuarios u ON ah.usuario_id_atual = u.id
            WHERE $where 
            ORDER BY ah.nome_maquina";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $ativos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $result = $conn->query($sql);
        if (!$result) {
            die("Erro na Query SQL: " . $conn->error . "<br><br>Query: " . $sql);
        }
        $ativos = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return $ativos;
}
// ===== CRIAR DIRETÓRIOS =====
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
if (!is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0777, true);
}

// ===== INICIALIZAR TABELAS =====
criarTabelas();
inserirDadosExemplo();

// ===== PROCESSAR REQUISIÇÕES POST =====
$mensagem = '';
$tipo_mensagem = '';

// Verificar mensagens da sessão
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'];
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar Ativo
    if (isset($_POST['adicionar_ativo'])) {
        $dados = [
            'loc_cidade' => strtoupper(sanitize($_POST['loc_cidade'])),
            'loc_predio' => strtoupper(sanitize($_POST['loc_predio'])),
            'loc_setor' => strtoupper(sanitize($_POST['loc_setor'])),
            'loc_tipo' => strtoupper(sanitize($_POST['loc_tipo'])),
            'loc_id' => intval($_POST['loc_id']),
            'status' => sanitize($_POST['status']),
            'tipo_ativo' => sanitize($_POST['tipo_ativo']),
            'modelo_cpu' => sanitize($_POST['modelo_cpu']),
            'memoria_gb' => intval($_POST['memoria_gb']),
            'modelo_disco_1' => sanitize($_POST['modelo_disco_1']),
            'serial_disco_1' => sanitize($_POST['serial_disco_1']),
            'data_aquisicao' => sanitize($_POST['data_aquisicao']),
            'custo_aquisicao' => floatval($_POST['custo_aquisicao']),
            'ip_address' => sanitize($_POST['ip_address']),
            'sistema_operacional' => sanitize($_POST['sistema_operacional']),
            'garantia_fim' => sanitize($_POST['garantia_fim']),
            'usuario_id_atual' => intval($_POST['usuario_id_atual']),
            'observacoes' => sanitize($_POST['observacoes']) // (Erro 9)
        ];
        
        $resultado = adicionarAtivo($dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Ativo adicionado com sucesso! Nome: " . $resultado['nome_maquina'];
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao adicionar ativo: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=ativos');
        exit;
    }
    
    // Editar Ativo
    if (isset($_POST['editar_ativo'])) {
        $nome_maquina = sanitize($_POST['nome_maquina']);
        $dados = [
            'status' => sanitize($_POST['status']),
            'tipo_ativo' => sanitize($_POST['tipo_ativo']),
            'modelo_cpu' => sanitize($_POST['modelo_cpu']),
            'memoria_gb' => intval($_POST['memoria_gb']),
            'modelo_disco_1' => sanitize($_POST['modelo_disco_1']),
            'serial_disco_1' => sanitize($_POST['serial_disco_1']),
            'data_ultima_manutencao' => sanitize($_POST['data_ultima_manutencao']),
            'ip_address' => sanitize($_POST['ip_address']),
            'sistema_operacional' => sanitize($_POST['sistema_operacional']),
            'garantia_fim' => sanitize($_POST['garantia_fim']),
            'usuario_id_atual' => intval($_POST['usuario_id_atual']),
            'observacoes' => sanitize($_POST['observacoes']) // (Erro 9)
        ];
        
        if (atualizarAtivo($nome_maquina, $dados)) {
            $_SESSION['mensagem'] = "Ativo atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar ativo!";
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=detalhes&maquina=' . urlencode($nome_maquina));
        exit;
    }

    // Adicionar Usuário
    if (isset($_POST['adicionar_usuario'])) {
        $dados = [
            'nome_completo' => sanitize($_POST['nome_completo']),
            'email' => sanitize($_POST['email']),
            'setor' => sanitize($_POST['setor']),
            'status' => sanitize($_POST['status'])
        ];
        $resultado = adicionarUsuario($dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Usuário adicionado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao adicionar usuário: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=usuarios');
        exit;
    }

    // Editar Usuário (NOVO V3.1)
    if (isset($_POST['editar_usuario'])) {
        $id = intval($_POST['id']);
        $dados = [
            'nome_completo' => sanitize($_POST['nome_completo']),
            'email' => sanitize($_POST['email']),
            'setor' => sanitize($_POST['setor']),
            'status' => sanitize($_POST['status'])
        ];
        $resultado = atualizarUsuario($id, $dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Usuário atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar usuário: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=usuarios');
        exit;
    }

    // Adicionar Chamado
    if (isset($_POST['adicionar_chamado'])) {
        $dados = [
            'nome_maquina' => sanitize($_POST['nome_maquina']),
            'usuario_id_relator' => intval($_POST['usuario_id_relator']),
            'categoria' => sanitize($_POST['categoria']),
            'descricao' => sanitize($_POST['descricao'])
        ];
        $resultado = adicionarChamado($dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Chamado Nº" . $resultado['id'] . " aberto com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao abrir chamado: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=chamados');
        exit;
    }

    // Fechar Chamado
    if (isset($_POST['fechar_chamado'])) {
        $id = intval($_POST['id_chamado']);
        $solucao = sanitize($_POST['solucao']);
        $status = sanitize($_POST['status']); // 'Fechado' ou 'Em Andamento'

        $resultado = fecharChamado($id, $solucao, $status);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Chamado Nº" . $id . " atualizado para '$status'!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar chamado: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=chamados');
        exit;
    }

    // Adicionar Manutenção (NOVO V3.1)
    if (isset($_POST['adicionar_manutencao'])) {
        $nome_maquina = sanitize($_POST['nome_maquina']);
        $dados = [
            'nome_maquina' => $nome_maquina,
            'data_manutencao' => sanitize($_POST['data_manutencao']),
            'tipo_manutencao' => sanitize($_POST['tipo_manutencao']),
            'descricao' => sanitize($_POST['descricao']),
            'tecnico_responsavel' => sanitize($_POST['tecnico_responsavel']),
            'custo' => floatval($_POST['custo']),
            'pecas_substituidas' => sanitize($_POST['pecas_substituidas']),
            'status' => sanitize($_POST['status'])
        ];
        
        $resultado = adicionarManutencao($dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Manutenção registrada com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            // (Erro 6) Exibe a mensagem de erro específica
            $_SESSION['mensagem'] = "Erro ao registrar manutenção: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=detalhes&maquina=' . urlencode($nome_maquina));
        exit;
    }

    // Adicionar Item de Inventário (ATUALIZADO V3.2 - Erro 1)
    if (isset($_POST['adicionar_item_inventario'])) {
        $dados = [
            'nome_item' => sanitize($_POST['nome_item']),
            'categoria' => sanitize($_POST['categoria']),
            'tipo_componente' => sanitize($_POST['tipo_componente']),
            'fabricante' => sanitize($_POST['fabricante']),
            'modelo' => sanitize($_POST['modelo']),
            'numero_serie' => sanitize($_POST['numero_serie']),
            'status' => sanitize($_POST['status']),
            'localizacao' => sanitize($_POST['localizacao']),
            'quantidade' => intval($_POST['quantidade']),
            'data_aquisicao' => sanitize($_POST['data_aquisicao']),
            'custo' => floatval($_POST['custo']),
            'observacoes' => sanitize($_POST['observacoes']),
        ];
        
        $resultado = adicionarItemInventario($dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Item adicionado ao inventário!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao adicionar item: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=inventario');
        exit;
    }

    // (NOVO V3.2 - Erro 1) Editar Item de Inventário
    if (isset($_POST['editar_item_inventario'])) {
        $id = intval($_POST['id']);
        $dados = [
            'nome_item' => sanitize($_POST['nome_item']),
            'categoria' => sanitize($_POST['categoria']),
            'tipo_componente' => sanitize($_POST['tipo_componente']),
            'fabricante' => sanitize($_POST['fabricante']),
            'modelo' => sanitize($_POST['modelo']),
            'numero_serie' => sanitize($_POST['numero_serie']),
            'status' => sanitize($_POST['status']),
            'localizacao' => sanitize($_POST['localizacao']),
            'quantidade' => intval($_POST['quantidade']),
            'data_aquisicao' => sanitize($_POST['data_aquisicao']),
            'custo' => floatval($_POST['custo']),
            'observacoes' => sanitize($_POST['observacoes']),
        ];
        
        $resultado = atualizarItemInventario($id, $dados);
        if ($resultado['success']) {
            $_SESSION['mensagem'] = "Item de inventário atualizado!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar item: " . $resultado['error'];
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header('Location: ?page=inventario');
        exit;
    }

    
    // APIs AJAX
    if (isset($_POST['api_prever_falha'])) {
        $nome_maquina = sanitize($_POST['maquina']);
        $resultado = preverFalha($nome_maquina);
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
    
    // (Erro 4) API de Alertas
    if (isset($_POST['api_get_alertas'])) {
        $alertas = getAlertasRecentes(10);
        // Formatar datas para exibição
        foreach ($alertas as $i => $alerta) {
            $alertas[$i]['timestamp_formatado'] = formatar_data($alerta['timestamp'], 'd/m H:i');
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'alertas' => $alertas]);
        exit;
    }
}

// ===== PROCESSAR REQUISIÇÕES GET =====
$pagina_atual = $_GET['page'] ?? 'dashboard';
$acao = $_GET['acao'] ?? '';

// Excluir Ativo
if ($acao === 'excluir' && isset($_GET['maquina'])) {
    $nome = sanitize($_GET['maquina']);
    if (excluirAtivo($nome)) {
        $_SESSION['mensagem'] = 'Ativo excluído com sucesso!';
        $_SESSION['tipo_mensagem'] = 'success';
    } else {
        $_SESSION['mensagem'] = 'Erro ao excluir ativo!';
        $_SESSION['tipo_mensagem'] = 'danger';
    }
    header('Location: ?page=ativos');
    exit;
}

// Excluir Usuário (NOVO V3.1)
if ($acao === 'excluir_usuario' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (excluirUsuario($id)['success']) {
        $_SESSION['mensagem'] = 'Usuário excluído com sucesso!';
        $_SESSION['tipo_mensagem'] = 'success';
    } else {
        $_SESSION['mensagem'] = 'Erro ao excluir usuário!';
        $_SESSION['tipo_mensagem'] = 'danger';
    }
    header('Location: ?page=usuarios');
    exit;
}

// (NOVO V3.2 - Erro 1) Excluir Item de Inventário
if ($acao === 'excluir_item_inventario' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (excluirItemInventario($id)['success']) {
        $_SESSION['mensagem'] = 'Item de inventário excluído com sucesso!';
        $_SESSION['tipo_mensagem'] = 'success';
    } else {
        $_SESSION['mensagem'] = 'Erro ao excluir item!';
        $_SESSION['tipo_mensagem'] = 'danger';
    }
    header('Location: ?page=inventario');
    exit;
}


// Exportar
if ($acao === 'exportar' && isset($_GET['formato'])) {
    $formato = sanitize($_GET['formato']);
    $filtros = [];
    if (!empty($_GET['busca'])) $filtros['busca'] = sanitize($_GET['busca']);
    if (!empty($_GET['status'])) $filtros['status'] = sanitize($_GET['status']);
    if (!empty($_GET['tipo_ativo'])) $filtros['tipo_ativo'] = sanitize($_GET['tipo_ativo']);
    if (!empty($_GET['loc_predio'])) $filtros['loc_predio'] = sanitize($_GET['loc_predio']); // (Erro 7)
    if (!empty($_GET['loc_setor'])) $filtros['loc_setor'] = sanitize($_GET['loc_setor']); // (Erro 7)
    
    $dados = getRelatorioAtivos($filtros);
    // ATUALIZADO: Exportação de CSV agora inclui o nome do usuário
    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_ativos_' . date('Y-m-d_H-i-s') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Nome Máquina', 'Cidade', 'Prédio', 'Setor', 'Tipo', 'ID', 'Status', 
            'Tipo Ativo', 'CPU', 'Memória GB', 'Disco', 'Serial Disco', 
            'Data Aquisição', 'Custo', 'IP', 'SO', 'Garantia', 'Usuário Atual', 'Observações'
        ]);
        foreach ($dados as $ativo) {
            fputcsv($output, [
                $ativo['nome_maquina'], $ativo['loc_cidade'], $ativo['loc_predio'],
                $ativo['loc_setor'], $ativo['loc_tipo'], $ativo['loc_id'], $ativo['status'],
                $ativo['tipo_ativo'], $ativo['modelo_cpu'], $ativo['memoria_gb'],
                $ativo['modelo_disco_1'], $ativo['serial_disco_1'],
                formatar_data($ativo['data_aquisicao']), $ativo['custo_aquisicao'],
                $ativo['ip_address'], $ativo['sistema_operacional'],
                formatar_data($ativo['garantia_fim']), $ativo['nome_usuario'], $ativo['observacoes']
            ]);
        }
        fclose($output);
        exit;
    }
    if ($formato === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_ativos_' . date('Y-m-d_H-i-s') . '.json');
        echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ===== CARREGAR DADOS GLOBAIS =====
$stats = getDashboardStats();
$lista_usuarios = getUsuarios(); // Para modais
$lista_ativos_all = getRelatorioAtivos(); // Para modais

// (Erro 7) Listas para filtros e modais
$lista_loc_predios_db = $conn->query("SELECT DISTINCT loc_predio FROM ativos_hardware ORDER BY loc_predio")->fetch_all(MYSQLI_ASSOC);
$lista_loc_setores_db = $conn->query("SELECT DISTINCT loc_setor FROM ativos_hardware ORDER BY loc_setor")->fetch_all(MYSQLI_ASSOC);


// ===== CARREGAR DADOS PÁGINA-ESPECÍFICA =====
$ativos = [];
$ativo_editar = null;
$ativo_detalhes = null;
$tem_manutencao_aberta = false; // (Erro 6)
$alertas_ativo = [];
$manutencoes_ativo = []; // NOVO V3.1
$chamados_ativo = []; 
$relatorios_dados = [];
$lista_usuarios_page = []; 
$usuario_editar = null; // NOVO V3.1
$usuario_detalhes = null; // NOVO V3.1
$chamados_usuario = []; // NOVO V3.1
$lista_chamados_page = []; 
$chamado_detalhes = null; 
$lista_inventario = []; // NOVO V3.1
$item_inventario_editar = null; // (Erro 1)
$lista_logs = []; // NOVO V3.1

if ($pagina_atual === 'ativos') {
    $filtros_get = [];
    if (isset($_GET['busca']) && !empty($_GET['busca'])) $filtros_get['busca'] = sanitize($_GET['busca']);
    if (isset($_GET['status']) && !empty($_GET['status'])) $filtros_get['status'] = sanitize($_GET['status']);
    if (isset($_GET['tipo_ativo']) && !empty($_GET['tipo_ativo'])) $filtros_get['tipo_ativo'] = sanitize($_GET['tipo_ativo']);
    if (isset($_GET['loc_predio']) && !empty($_GET['loc_predio'])) $filtros_get['loc_predio'] = sanitize($_GET['loc_predio']); // (Erro 7)
    if (isset($_GET['loc_setor']) && !empty($_GET['loc_setor'])) $filtros_get['loc_setor'] = sanitize($_GET['loc_setor']); // (Erro 7)
    
    $ativos_brutos = getRelatorioAtivos($filtros_get);
    $ativos = array_map(function($ativo) {
        $ativo['idade_dias'] = calcular_idade_dias($ativo['data_aquisicao']);
        return $ativo;
    }, $ativos_brutos);
}

if ($pagina_atual === 'editar' && isset($_GET['maquina'])) {
    $nome = sanitize($_GET['maquina']);
    $ativo_editar = getAtivoByNome($nome);
    if (!$ativo_editar) {
        $_SESSION['mensagem'] = 'Ativo não encontrado!';
        $_SESSION['tipo_mensagem'] = 'danger';
        header('Location: ?page=ativos');
        exit;
    }
}

if ($pagina_atual === 'detalhes' && isset($_GET['maquina'])) {
    $nome = sanitize($_GET['maquina']);
    $ativo_detalhes = getAtivoByNome($nome);
    
    if (!$ativo_detalhes) {
        $_SESSION['mensagem'] = 'Ativo não encontrado!';
        $_SESSION['tipo_mensagem'] = 'danger';
        header('Location: ?page=ativos');
        exit;
    }
    
    // (Erro 6) Verificar manutenção aberta
    $tem_manutencao_aberta = checkManutencaoAberta($nome);

    // Buscar alertas recentes
    $stmt = $conn->prepare("SELECT * FROM logs_saude WHERE nome_maquina = ? ORDER BY timestamp DESC LIMIT 10");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $alertas_ativo = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Buscar chamados (ATUALIZADO V3.1 - sem limite)
    $chamados_ativo = getChamadosPorMaquina($nome);

    // Buscar manutenções (NOVO V3.1)
    $manutencoes_ativo = getManutencoesPorMaquina($nome);
}

if ($pagina_atual === 'relatorios') {
    $relatorios_dados = getRelatorioAtivos($_GET);
}

// Página de Usuários
if ($pagina_atual === 'usuarios') {
    $lista_usuarios_page = $lista_usuarios; // Já carregado
}

// NOVO V3.1: Página Editar Usuário
if ($pagina_atual === 'editar_usuario' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $usuario_editar = getUsuarioById($id);
    if (!$usuario_editar) {
        $_SESSION['mensagem'] = 'Usuário não encontrado!';
        $_SESSION['tipo_mensagem'] = 'danger';
        header('Location: ?page=usuarios');
        exit;
    }
}

// NOVO V3.1: Página Detalhes Usuário
if ($pagina_atual === 'detalhes_usuario' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $usuario_detalhes = getUsuarioById($id);
    if (!$usuario_detalhes) {
        $_SESSION['mensagem'] = 'Usuário não encontrado!';
        $_SESSION['tipo_mensagem'] = 'danger';
        header('Location: ?page=usuarios');
        exit;
    }
    $chamados_usuario = getChamadosPorUsuario($id);
}

// Página de Chamados
if ($pagina_atual === 'chamados') {
    $filtros_get = [];
    if (isset($_GET['status']) && !empty($_GET['status'])) $filtros_get['status'] = sanitize($_GET['status']);
    $lista_chamados_page = getChamados($filtros_get);
}

// Página para Fechar Chamado
if ($pagina_atual === 'fechar_chamado' && isset($_GET['id'])) {
    $id_chamado = intval($_GET['id']);
    $chamado_detalhes = getChamadoById($id_chamado);
    if (!$chamado_detalhes) {
        $_SESSION['mensagem'] = 'Chamado não encontrado!';
        $_SESSION['tipo_mensagem'] = 'danger';
        header('Location: ?page=chamados');
        exit;
    }
}

// NOVO V3.2: Página Inventário (Erro 1)
if ($pagina_atual === 'inventario') {
    $filtros_get = [];
    if (isset($_GET['categoria']) && !empty($_GET['categoria'])) $filtros_get['categoria'] = sanitize($_GET['categoria']);
    $lista_inventario = getInventario($filtros_get);
}

// (Erro 1) NOVO V3.2: Página Editar Item Inventário
if ($pagina_atual === 'editar_item_inventario' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $item_inventario_editar = getInventarioItemById($id);
    if (!$item_inventario_editar) {
        $_SESSION['mensagem'] = 'Item de inventário não encontrado!';
        $_SESSION['tipo_mensagem'] = 'danger';
        header('Location: ?page=inventario');
        exit;
    }
}


// NOVO V3.2: Página Auditoria (Erro 5)
if ($pagina_atual === 'auditoria') {
    $filtros_get = [];
    if (isset($_GET['acao']) && !empty($_GET['acao'])) $filtros_get['acao'] = sanitize($_GET['acao']);
    if (isset($_GET['tabela']) && !empty($_GET['tabela'])) $filtros_get['tabela'] = sanitize($_GET['tabela']);
    if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) $filtros_get['data_inicio'] = sanitize($_GET['data_inicio']);
    if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) $filtros_get['data_fim'] = sanitize($_GET['data_fim']);
    $lista_logs = getLogsSistema($filtros_get);
}


// ===== HEADER HTML =====
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SISTEMA_NOME ?> - Sistema de Gestão de Ativos</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); transition: transform 0.2s ease; }
        .card:hover { transform: translateY(-2px); }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .table th { background-color: #f8f9fa; font-weight: 600; }
        .dashboard-card h2 { font-size: 2.5rem; font-weight: bold; }
        .dashboard-card-sm h2 { font-size: 1.8rem; font-weight: bold; }
        .chart-container { position: relative; height: 300px; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #0d6efd; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .info-card { border: 1px solid #eee; border-radius: 8px; padding: 16px; margin-bottom: 16px; background-color: #fff; }
        /* (Erro 1) Estilo para travar a tela */
        .modal-backdrop.show { opacity: 0.5; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="?">
                <i class="fas fa-server"></i> <?= SISTEMA_NOME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'dashboard' ? 'active' : '' ?>" href="?">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'ativos' ? 'active' : '' ?>" href="?page=ativos">
                            <i class="fas fa-desktop"></i> Ativos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'chamados' ? 'active' : '' ?>" href="?page=chamados">
                            <i class="fas fa-life-ring"></i> Chamados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'usuarios' ? 'active' : '' ?>" href="?page=usuarios">
                            <i class="fas fa-users"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'inventario' ? 'active' : '' ?>" href="?page=inventario">
                            <i class="fas fa-warehouse"></i> Inventário
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'relatorios' ? 'active' : '' ?>" href="?page=relatorios">
                            <i class="fas fa-chart-bar"></i> Relatório Ativos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'auditoria' ? 'active' : '' ?>" href="?page=auditoria">
                            <i class="fas fa-history"></i> Auditoria
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($pagina_atual === 'dashboard'): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success dashboard-card">
                        <div class="card-body">
                            <h6 class="card-title">Total de Ativos</h6>
                            <h2 class="mb-0"><?= $stats['total_ativos'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger dashboard-card">
                        <div class="card-body">
                            <h6 class="card-title">Chamados Abertos</h6>
                            <h2 class="mb-0"><?= $stats['chamados_abertos'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info dashboard-card">
                        <div class="card-body">
                            <h6 class="card-title">Usuários Ativos</h6>
                            <h2 class="mb-0"><?= $stats['total_usuarios'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning dashboard-card">
                        <div class="card-body">
                            <h6 class="card-title">Em Manutenção</h6>
                            <h2 class="mb-0"><?= $stats['em_manutencao'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Novos Cards v3.1 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-white bg-primary dashboard-card-sm">
                        <div class="card-body">
                            <h6 class="card-title">Ativos em Estoque</h6>
                            <h2 class="mb-0"><?= $stats['em_estoque'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white bg-dark dashboard-card-sm">
                        <div class="card-body">
                            <h6 class="card-title">Dispositivos Desativados</h6>
                            <h2 class="mb-0"><?= $stats['desativados'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-pie"></i> Status dos Dispositivos</h5></div>
                        <div class="card-body"><div class="chart-container"><canvas id="statusChart"></canvas></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Localização dos Dispositivos</h5></div>
                        <div class="card-body"><div class="chart-container"><canvas id="localizacaoChart"></canvas></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-laptop"></i> Por Tipo (DSK/NTB)</h5></div>
                        <div class="card-body"><div class="chart-container"><canvas id="tipoChart"></canvas></div></div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mb-0"><i class="fas fa-bell"></i> Alertas Recentes de Hardware</h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="carregarAlertas()">
                                <i class="fas fa-sync"></i> Atualizar
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Máquina</th> <th>Tipo</th> <th>Valor</th> <th>Gravidade</th> <th>Mensagem</th> <th>Data</th> <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="alertasTable">
                                        <tr><td colspan="7" class="text-center"><div class="spinner"></div> Carregando...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'ativos'): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-desktop"></i> Gerenciar Ativos</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionar">
                        <i class="fas fa-plus"></i> Adicionar Ativo
                    </button>
                    <div class="btn-group ms-2">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" id="export-csv"><i class="fas fa-file-csv"></i> CSV</a></li>
                            <li><a class="dropdown-item" href="#" id="export-json"><i class="fas fa-file-code"></i> JSON</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5></div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="page" value="ativos">
                        <div class="col-md-3">
                            <label class="form-label">Busca Rápida</label>
                            <input type="text" class="form-control" name="busca" 
                                   value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" 
                                   placeholder="Buscar por nome, IP, usuário...">
                        </div>
                        <!-- (Erro 7) Novos Filtros -->
                        <div class="col-md-2">
                            <label class="form-label">Unidade (Prédio)</label>
                            <select class="form-select" name="loc_predio">
                                <option value="">Todas Unidades</option>
                                <?php foreach ($lista_loc_predios_db as $predio): $p = $predio['loc_predio']; ?>
                                    <option value="<?= $p ?>" <?= (($_GET['loc_predio'] ?? '') == $p) ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Setor</label>
                            <select class="form-select" name="loc_setor">
                                <option value="">Todos Setores</option>
                                <?php foreach ($lista_loc_setores_db as $setor): $s = $setor['loc_setor']; ?>
                                    <option value="<?= $s ?>" <?= (($_GET['loc_setor'] ?? '') == $s) ? 'selected' : '' ?>>
                                        <?= $s ?> (<?= LOC_SETORES[$s] ?? 'N/A' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Todos Status</option>
                                <option value="Ativo" <?= (($_GET['status'] ?? '') == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                                <option value="Manutenção" <?= (($_GET['status'] ?? '') == 'Manutenção') ? 'selected' : '' ?>>Manutenção</option>
                                <option value="Estoque" <?= (($_GET['status'] ?? '') == 'Estoque') ? 'selected' : '' ?>>Estoque</option>
                                <option value="Desativado" <?= (($_GET['status'] ?? '') == 'Desativado') ? 'selected' : '' ?>>Desativado</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo_ativo">
                                <option value="">Todos</option>
                                <option value="NTB" <?= (($_GET['tipo_ativo'] ?? '') == 'NTB') ? 'selected' : '' ?>>NTB</option>
                                <option value="DSK" <?= (($_GET['tipo_ativo'] ?? '') == 'DSK') ? 'selected' : '' ?>>DSK</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="?page=ativos" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Ativos (<?= count($ativos) ?> registros)</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome Máquina</th>
                                    <th>Usuário Atual</th>
                                    <th>Localização</th>
                                    <th>Status</th>
                                    <th>Tipo</th>
                                    <th>IP</th>
                                    <th>Garantia</th>
                                    <th>Últ. Manut.</th>
                                    <th>Idade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ativos)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-3x my-3"></i><br>
                                            Nenhum ativo encontrado
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($ativos as $ativo): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($ativo['nome_maquina'] ?? '') ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($ativo['modelo_cpu'] ?? '-') ?></small>
                                            </td>
                                            <td><small><?= $ativo['nome_usuario'] ? htmlspecialchars($ativo['nome_usuario']) : '<span class="text-muted">Nenhum</span>' ?></small>
                                            </td>
                                            <td>
                                                <small><?= $ativo['loc_cidade'] ?>-<?= $ativo['loc_predio'] ?><br><?= $ativo['loc_setor'] ?></small>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= get_status_badge_class($ativo['status']) ?>">
                                                    <?= $ativo['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $ativo['tipo_ativo'] === 'NTB' ? 'primary' : 'secondary' ?>">
                                                    <?= $ativo['tipo_ativo'] ?>
                                                </span>
                                            </td>
                                            <td><small><?= htmlspecialchars($ativo['ip_address'] ?? '-') ?></small></td>
                                            <td><small><?= formatar_data($ativo['garantia_fim']) ?></small></td>
                                            <td><small><?= formatar_data($ativo['data_ultima_manutencao']) ?></small></td>
                                            <td><small><?= $ativo['idade_dias'] ?> dias</small></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?page=editar&maquina=<?= urlencode($ativo['nome_maquina']) ?>" 
                                                       class="btn btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?page=detalhes&maquina=<?= urlencode($ativo['nome_maquina']) ?>" 
                                                       class="btn btn-outline-info" title="Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-warning" 
                                                            onclick="preverFalha('<?= htmlspecialchars($ativo['nome_maquina'] ?? '') ?>')"
                                                            title="Prever Falha (v2)">
                                                        <i class="fas fa-chart-line"></i>
                                                    </button>
                                                    <a href="?page=ativos&acao=excluir&maquina=<?= urlencode($ativo['nome_maquina']) ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('Tem certeza que deseja excluir este ativo? (Todos os logs e manutenções associados serão perdidos!)')"
                                                       title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'editar' && $ativo_editar): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h3><i class="fas fa-edit"></i> Editar Ativo</h3></div>
                <div class="col-md-6 text-end">
                    <a href="?page=detalhes&maquina=<?= urlencode($ativo_editar['nome_maquina'])?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar aos Detalhes
                    </a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-tag"></i> Identificação</h6>
                                <div class="mb-3">
                                    <label class="form-label">Nome da Máquina</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($ativo_editar['nome_maquina'] ?? '') ?>" disabled>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="Ativo" <?= ($ativo_editar['status']=='Ativo')?'selected':'' ?>>Ativo</option>
                                            <option value="Manutenção" <?= ($ativo_editar['status']=='Manutenção')?'selected':'' ?>>Manutenção</option>
                                            <option value="Estoque" <?= ($ativo_editar['status']=='Estoque')?'selected':'' ?>>Estoque</option>
                                            <option value="Desativado" <?= ($ativo_editar['status']=='Desativado')?'selected':'' ?>>Desativado</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-select" name="tipo_ativo">
                                            <option value="DSK" <?= ($ativo_editar['tipo_ativo']=='DSK')?'selected':'' ?>>Desktop</option>
                                            <option value="NTB" <?= ($ativo_editar['tipo_ativo']=='NTB')?'selected':'' ?>>Notebook</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuário Atual</label>
                                    <select class="form-select" name="usuario_id_atual">
                                        <option value="0">Nenhum (Em Estoque)</option>
                                        <?php foreach ($lista_usuarios as $usuario): ?>
                                            <option value="<?= $usuario['id'] ?>" <?= ($ativo_editar['usuario_id_atual'] == $usuario['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($usuario['nome_completo'] ?? '') ?> (<?= htmlspecialchars($usuario['setor'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">IP</label>
                                    <input type="text" name="ip_address" class="form-control" value="<?= htmlspecialchars($ativo_editar['ip_address'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sistema Operacional</label>
                                    <input type="text" name="sistema_operacional" class="form-control" value="<?= htmlspecialchars($ativo_editar['sistema_operacional'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-microchip"></i> Especificações</h6>
                                <div class="mb-3">
                                    <label class="form-label">CPU</label>
                                    <input type="text" name="modelo_cpu" class="form-control" value="<?= htmlspecialchars($ativo_editar['modelo_cpu'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Memória (GB)</label>
                                    <input type="number" name="memoria_gb" class="form-control" value="<?= intval($ativo_editar['memoria_gb']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Modelo Disco</label>
                                    <input type="text" name="modelo_disco_1" class="form-control" value="<?= htmlspecialchars($ativo_editar['modelo_disco_1'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Serial Disco</label>
                                    <input type="text" name="serial_disco_1" class="form-control" value="<?= htmlspecialchars($ativo_editar['serial_disco_1'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Última Manutenção</label>
                                    <input type="date" name="data_ultima_manutencao" class="form-control" value="<?= htmlspecialchars($ativo_editar['data_ultima_manutencao'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Garantia</label>
                                    <input type="date" name="garantia_fim" class="form-control" value="<?= htmlspecialchars($ativo_editar['garantia_fim'] ?? '') ?>">
                                </div>
                            </div>
                            <!-- (Erro 9) Novo Campo Observações -->
                            <div class="col-12">
                                <h6 class="text-primary mb-3"><i class="fas fa-sticky-note"></i> Observações</h6>
                                <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações sobre o estado, motivo da troca, etc."><?= htmlspecialchars($ativo_editar['observacoes'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <input type="hidden" name="nome_maquina" value="<?= htmlspecialchars($ativo_editar['nome_maquina'] ?? '') ?>">
                                <button type="submit" name="editar_ativo" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'detalhes' && $ativo_detalhes): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h3><i class="fas fa-eye"></i> Detalhes do Ativo</h3></div>
                <div class="col-md-6 text-end">
                    <a href="?page=ativos" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                    <a href="?page=editar&maquina=<?= urlencode($ativo_detalhes['nome_maquina']) ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-7">
                    <div class="info-card">
                        <h5><i class="fas fa-id-card"></i> Informações Básicas</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nome:</strong> <?= htmlspecialchars($ativo_detalhes['nome_maquina'] ?? '') ?></p>
                                <p><strong>Local:</strong> <?= $ativo_detalhes['loc_cidade'] ?>-<?= $ativo_detalhes['loc_predio'] ?> / <?= $ativo_detalhes['loc_setor'] ?></p>
                                <p><strong>Status:</strong> <span class="status-badge <?= get_status_badge_class($ativo_detalhes['status']) ?>"><?= $ativo_detalhes['status'] ?></span></p>
                                <p><strong>Usuário Atual:</strong> 
                                    <?= $ativo_detalhes['nome_usuario_atual'] ? htmlspecialchars($ativo_detalhes['nome_usuario_atual']) : '<span class="text-muted">Nenhum (Estoque)</span>' ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> <?= $ativo_detalhes['tipo_ativo']=='NTB'?'Notebook':'Desktop' ?></p>
                                <p><strong>IP:</strong> <?= htmlspecialchars($ativo_detalhes['ip_address'] ?? 'Não atribuído') ?></p>
                                <p><strong>SO:</strong> <?= htmlspecialchars($ativo_detalhes['sistema_operacional'] ?? '-') ?></p>
                                <p><strong>Aquisição:</strong> <?= formatar_data($ativo_detalhes['data_aquisicao']) ?> (<?= calcular_idade_dias($ativo_detalhes['data_aquisicao']) ?> dias)</p>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <h5><i class="fas fa-microchip"></i> Especificações</h5>
                        <p><strong>CPU:</strong> <?= htmlspecialchars($ativo_detalhes['modelo_cpu'] ?? '-') ?></p>
                        <p><strong>Memória:</strong> <?= intval($ativo_detalhes['memoria_gb']) ?> GB</p>
                        <p><strong>Disco:</strong> <?= htmlspecialchars($ativo_detalhes['modelo_disco_1'] ?? '-') ?> (Serial: <?= htmlspecialchars($ativo_detalhes['serial_disco_1'] ?? '-') ?>)</p>
                        <p><strong>Última Manutenção:</strong> <?= formatar_data($ativo_detalhes['data_ultima_manutencao']) ?></p>
                        <p><strong>Garantia:</strong> <?= formatar_data($ativo_detalhes['garantia_fim']) ?></p>
                    </div>
                    <!-- (Erro 9) Exibir Observações -->
                    <?php if (!empty($ativo_detalhes['observacoes'])): ?>
                    <div class="info-card bg-light">
                        <h5><i class="fas fa-sticky-note"></i> Observações</h5>
                        <p><?= nl2br(htmlspecialchars($ativo_detalhes['observacoes'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($alertas_ativo)): ?>
                    <div class="info-card">
                        <h5><i class="fas fa-exclamation-triangle"></i> Alertas de Hardware</h5>
                        <table class="table table-sm">
                            <thead><tr><th>Data</th><th>Tipo</th><th>Gravidade</th></tr></thead>
                            <tbody>
                                <?php foreach($alertas_ativo as $al): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($al['timestamp'])) ?></td>
                                        <td><?= htmlspecialchars($al['metric_type'] ?? '') ?></td>
                                        <td><span class="badge <?= get_risco_badge_class($al['gravidade']) ?>"><?= $al['gravidade'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="chamados-tab" data-bs-toggle="tab" data-bs-target="#chamados-content" type="button" role="tab"><i class="fas fa-life-ring"></i> Chamados</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="manutencoes-tab" data-bs-toggle="tab" data-bs-target="#manutencoes-content" type="button" role="tab"><i class="fas fa-tools"></i> Manutenções</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="myTabContent">
                                <!-- Aba Chamados -->
                                <div class="tab-pane fade show active" id="chamados-content" role="tabpanel">
                                    <?php if (empty($chamados_ativo)): ?>
                                        <p class="text-muted text-center">Nenhum chamado registrado para este ativo.</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach($chamados_ativo as $chamado): ?>
                                            <a href="?page=fechar_chamado&id=<?= $chamado['id'] ?>" class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?= htmlspecialchars($chamado['categoria'] ?? '') ?></h6>
                                                    <small><span class="badge <?= get_chamado_badge_class($chamado['status']) ?>"><?= $chamado['status'] ?></span></small>
                                                </div>
                                                <p class="mb-1 small"><?= htmlspecialchars($chamado['descricao'] ?? '') ?></p>
                                                <small class="text-muted"><?= formatar_data($chamado['data_abertura'], 'd/m/Y H:i') ?> por <?= htmlspecialchars($chamado['nome_relator'] ?? 'N/A') ?></small>
                                            </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Aba Manutenções -->
                                <div class="tab-pane fade" id="manutencoes-content" role="tabpanel">
                                    <!-- (Erro 6) Botão de Manutenção com trava -->
                                    <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalAdicionarManutencao" <?= $tem_manutencao_aberta ? 'disabled' : '' ?>>
                                        <i class="fas fa-plus"></i> Registrar Manutenção
                                    </button>
                                    <?php if ($tem_manutencao_aberta): ?>
                                        <div class="alert alert-warning small p-2"><i class="fas fa-exclamation-triangle"></i> Já existe uma manutenção em aberto. Conclua a anterior para registrar uma nova.</div>
                                    <?php endif; ?>

                                    <?php if (empty($manutencoes_ativo)): ?>
                                        <p class="text-muted text-center">Nenhum registro de manutenção.</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach($manutencoes_ativo as $manut): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?= htmlspecialchars($manut['tipo_manutencao'] ?? '') ?></h6>
                                                    <small><span class="badge bg-secondary"><?= $manut['status'] ?></span></small>
                                                </div>
                                                <p class="mb-1 small"><strong>Descrição:</strong> <?= htmlspecialchars($manut['descricao'] ?? '-') ?></p>
                                                <p class="mb-1 small"><strong>Peças:</strong> <?= htmlspecialchars($manut['pecas_substituidas'] ?? 'N/A') ?></p>
                                                <small class="text-muted"><?= formatar_data($manut['data_manutencao']) ?> por <?= htmlspecialchars($manut['tecnico_responsavel'] ?? 'N/A') ?> (Custo: <?= formatar_moeda($manut['custo']) ?>)</small>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'chamados'): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h2><i class="fas fa-life-ring"></i> Gerenciar Chamados</h2></div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAbrirChamado">
                        <i class="fas fa-plus"></i> Abrir Chamado
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Chamados (<?= count($lista_chamados_page) ?> registros)</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Status</th>
                                    <th>Ativo (Máquina)</th>
                                    <th>Relator</th>
                                    <th>Categoria</th>
                                    <th>Descrição</th>
                                    <th>Data Abertura</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lista_chamados_page)): ?>
                                    <tr><td colspan="8" class="text-center text-muted p-5"><i class="fas fa-check-circle fa-3x"></i><br>Nenhum chamado para exibir.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lista_chamados_page as $chamado): ?>
                                        <tr>
                                            <td><strong><?= $chamado['id'] ?></strong></td>
                                            <td><span class="badge <?= get_chamado_badge_class($chamado['status']) ?>"><?= $chamado['status'] ?></span></td>
                                            <td><a href="?page=detalhes&maquina=<?= urlencode($chamado['nome_maquina']) ?>"><?= htmlspecialchars($chamado['nome_maquina'] ?? '') ?></a></td>
                                            <td><?= htmlspecialchars($chamado['nome_relator'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($chamado['categoria'] ?? '') ?></td>
                                            <td><small><?= htmlspecialchars($chamado['descricao'] ?? '') ?></small></td>
                                            <td><?= formatar_data($chamado['data_abertura'], 'd/m/Y H:i') ?></td>
                                            <td>
                                                <a href="?page=fechar_chamado&id=<?= $chamado['id'] ?>" class="btn btn-sm btn-<?= $chamado['status'] == 'Fechado' ? 'success' : 'warning' ?>">
                                                    <i class="fas fa-<?= $chamado['status'] == 'Fechado' ? 'eye' : 'edit' ?>"></i> <?= $chamado['status'] == 'Fechado' ? 'Ver' : 'Atender' ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
        <?php elseif ($pagina_atual === 'fechar_chamado' && $chamado_detalhes): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h3><i class="fas fa-edit"></i> Atender Chamado #<?= $chamado_detalhes['id'] ?></h3></div>
                <div class="col-md-6 text-end">
                    <a href="?page=chamados" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card bg-light">
                                <h5><i class="fas fa-desktop"></i> Ativo</h5>
                                <p><strong>Máquina:</strong> <a href="?page=detalhes&maquina=<?= urlencode($chamado_detalhes['nome_maquina']) ?>"><?= htmlspecialchars($chamado_detalhes['nome_maquina'] ?? '') ?></a></p>
                                <h5><i class="fas fa-user"></i> Relator</h5>
                                <p><strong>Nome:</strong> <?= htmlspecialchars($chamado_detalhes['nome_relator'] ?? 'N/A') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($chamado_detalhes['email'] ?? '-') ?></p>
                                <p><strong>Setor:</strong> <?= htmlspecialchars($chamado_detalhes['setor'] ?? '-') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5><i class="fas fa-exclamation-circle"></i> Problema</h5>
                                <p><strong>Data:</strong> <?= formatar_data($chamado_detalhes['data_abertura'], 'd/m/Y H:i') ?></p>
                                <p><strong>Categoria:</strong> <?= htmlspecialchars($chamado_detalhes['categoria'] ?? '') ?></p>
                                <p><strong>Descrição:</strong></p>
                                <blockquote class="blockquote"><p><?= nl2br(htmlspecialchars($chamado_detalhes['descricao'] ?? '')) ?></p></blockquote>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <form method="POST">
                        <input type="hidden" name="id_chamado" value="<?= $chamado_detalhes['id'] ?>">
                        <div class="mb-3">
                            <label for="solucao" class="form-label"><h5><i class="fas fa-check-circle"></i> Solução Aplicada</h5></label>
                            <textarea class="form-control" name="solucao" id="solucao" rows="5" <?= $chamado_detalhes['status'] == 'Fechado' ? 'readonly' : '' ?>><?= htmlspecialchars($chamado_detalhes['solucao'] ?? '') ?></textarea>
                        </div>
                        
                        <?php if ($chamado_detalhes['status'] != 'Fechado'): ?>
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Atualizar Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Em Andamento" <?= ($chamado_detalhes['status'] == 'Em Andamento') ? 'selected' : '' ?>>Em Andamento</option>
                                    <option value="Fechado" <?= ($chamado_detalhes['status'] == 'Fechado') ? 'selected' : '' ?>>Fechado</option>
                                    <option value="Aberto" <?= ($chamado_detalhes['status'] == 'Aberto') ? 'selected' : '' ?>>Reabrir</option>
                                </select>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" name="fechar_chamado" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Salvar e Atualizar Chamado
                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                            <p class="text-center text-success"><i class="fas fa-check-circle"></i> Este chamado foi fechado em <?= formatar_data($chamado_detalhes['data_fechamento'], 'd/m/Y H:i') ?>.</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'usuarios'): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h2><i class="fas fa-users"></i> Gerenciar Usuários</h2></div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarUsuario">
                        <i class="fas fa-user-plus"></i> Adicionar Usuário
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Usuários (<?= count($lista_usuarios_page) ?> registros)</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome Completo</th>
                                    <th>Email</th>
                                    <th>Setor</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lista_usuarios_page)): ?>
                                    <tr><td colspan="5" class="text-center text-muted p-5">Nenhum usuário cadastrado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lista_usuarios_page as $usuario): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($usuario['nome_completo'] ?? '') ?></strong></td>
                                            <td><?= htmlspecialchars($usuario['email'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($usuario['setor'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $usuario['status'] == 'Ativo' ? 'success' : 'secondary' ?>">
                                                    <?= $usuario['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?page=detalhes_usuario&id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-info" title="Detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?page=editar_usuario&id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?page=usuarios&acao=excluir_usuario&id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'editar_usuario' && $usuario_editar): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h3><i class="fas fa-user-edit"></i> Editar Usuário</h3></div>
                <div class="col-md-6 text-end">
                    <a href="?page=usuarios" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $usuario_editar['id'] ?>">
                        <div class="mb-3">
                            <label for="nome_completo" class="form-label">Nome Completo</label>
                            <input type="text" name="nome_completo" id="nome_completo" class="form-control" value="<?= htmlspecialchars($usuario_editar['nome_completo'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($usuario_editar['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="setor" class="form-label">Setor</label>
                            <input type="text" name="setor" id="setor" class="form-control" value="<?= htmlspecialchars($usuario_editar['setor'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Ativo" <?= ($usuario_editar['status'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= ($usuario_editar['status'] == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                        <button type="submit" name="editar_usuario" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        
        <?php elseif ($pagina_atual === 'detalhes_usuario' && $usuario_detalhes): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h3><i class="fas fa-user"></i> Detalhes do Usuário</h3></div>
                <div class="col-md-6 text-end">
                    <a href="?page=usuarios" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="info-card">
                        <h5><?= htmlspecialchars($usuario_detalhes['nome_completo'] ?? '') ?></h5>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario_detalhes['email'] ?? '') ?></p>
                        <p><i class="fas fa-briefcase"></i> <?= htmlspecialchars($usuario_detalhes['setor'] ?? '') ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-<?= $usuario_detalhes['status'] == 'Ativo' ? 'success' : 'secondary' ?>"><?= $usuario_detalhes['status'] ?></span></p>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-life-ring"></i> Histórico de Chamados (<?= count($chamados_usuario) ?>)</h5></div>
                        <div class="card-body">
                            <?php if (empty($chamados_usuario)): ?>
                                <p class="text-muted text-center">Nenhum chamado aberto por este usuário.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($chamados_usuario as $chamado): ?>
                                    <a href="?page=fechar_chamado&id=<?= $chamado['id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($chamado['categoria'] ?? '') ?> (Máquina: <?= htmlspecialchars($chamado['nome_maquina'] ?? '') ?>)</h6>
                                            <small><span class="badge <?= get_chamado_badge_class($chamado['status']) ?>"><?= $chamado['status'] ?></span></small>
                                        </div>
                                        <p class="mb-1 small"><?= htmlspecialchars($chamado['descricao'] ?? '') ?></p>
                                        <small class="text-muted"><?= formatar_data($chamado['data_abertura'], 'd/m/Y H:i') ?></small>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'inventario'): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h2><i class="fas fa-warehouse"></i> Inventário (Armazém)</h2></div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarItemInventario">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>
                </div>
            </div>
            
            <!-- (Erro 1) Filtros de Categoria -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?= empty($_GET['categoria']) ? 'active' : '' ?>" href="?page=inventario">Todos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (($_GET['categoria'] ?? '') == 'Peças') ? 'active' : '' ?>" href="?page=inventario&categoria=Peças">Peças</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (($_GET['categoria'] ?? '') == 'Infra') ? 'active' : '' ?>" href="?page=inventario&categoria=Infra">Infra</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (($_GET['categoria'] ?? '') == 'Licenças') ? 'active' : '' ?>" href="?page=inventario&categoria=Licenças">Licenças</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (($_GET['categoria'] ?? '') == 'Outro') ? 'active' : '' ?>" href="?page=inventario&categoria=Outro">Outros</a>
                </li>
            </ul>

            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Itens em Estoque (<?= count($lista_inventario) ?>)</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Categoria</th>
                                    <th>Tipo</th>
                                    <th>Fabricante/Modelo</th>
                                    <th>Localização</th>
                                    <th>Status</th>
                                    <th>Qtde</th>
                                    <th>Custo Unit.</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lista_inventario)): ?>
                                    <tr><td colspan="9" class="text-center text-muted p-5">Nenhum item no inventário.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lista_inventario as $item): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($item['nome_item'] ?? '') ?></strong></td>
                                            <td><span class="badge bg-dark"><?= htmlspecialchars($item['categoria'] ?? '') ?></span></td>
                                            <td><?= htmlspecialchars($item['tipo_componente'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($item['fabricante'] ?? '-') ?> / <?= htmlspecialchars($item['modelo'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($item['localizacao'] ?? '') ?></td>
                                            <td><span class="badge bg-info"><?= $item['status'] ?></span></td>
                                            <td><?= $item['quantidade'] ?></td>
                                            <td><?= formatar_moeda($item['custo']) ?></td>
                                            <!-- (Erro 1) Botões CRUD habilitados -->
                                            <td>
                                                <a href="?page=editar_item_inventario&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?page=inventario&acao=excluir_item_inventario&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este item do inventário?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'editar_item_inventario' && $item_inventario_editar): // (Erro 1) Nova Página ?>
             <div class="row mb-4">
                <div class="col-md-6"><h3><i class="fas fa-edit"></i> Editar Item do Inventário</h3></div>
                <div class="col-md-6 text-end">
                    <a href="?page=inventario" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                     <form method="POST">
                        <input type="hidden" name="id" value="<?= $item_inventario_editar['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Item</label>
                                <input type="text" name="nome_item" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['nome_item'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Categoria</label>
                                <select name="categoria" class="form-select">
                                    <option value="Peças" <?= ($item_inventario_editar['categoria'] == 'Peças') ? 'selected' : '' ?>>Peças (Mouse, Teclado, SSD, etc)</option>
                                    <option value="Infra" <?= ($item_inventario_editar['categoria'] == 'Infra') ? 'selected' : '' ?>>Infra (Cabos, Conectores, etc)</option>
                                    <option value="Licenças" <?= ($item_inventario_editar['categoria'] == 'Licenças') ? 'selected' : '' ?>>Licenças (Software, SO)</option>
                                    <option value="Outro" <?= ($item_inventario_editar['categoria'] == 'Outro') ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo (Ex: Teclado, Cabo)</label>
                                <input type="text" name="tipo_componente" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['tipo_componente'] ?? '') ?>" placeholder="Ex: Teclado, Cabo de Força">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fabricante</label>
                                <input type="text" name="fabricante" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['fabricante'] ?? '') ?>" placeholder="Ex: Dell, Logitech">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['modelo'] ?? '') ?>" placeholder="Ex: KB216, M90">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Número de Série (opcional)</label>
                                <input type="text" name="numero_serie" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['numero_serie'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Estoque" <?= ($item_inventario_editar['status'] == 'Estoque') ? 'selected' : '' ?>>Estoque</option>
                                    <option value="Em Uso" <?= ($item_inventario_editar['status'] == 'Em Uso') ? 'selected' : '' ?>>Em Uso</option>
                                    <option value="Defeito" <?= ($item_inventario_editar['status'] == 'Defeito') ? 'selected' : '' ?>>Defeito</option>
                                    <option value="Descartado" <?= ($item_inventario_editar['status'] == 'Descartado') ? 'selected' : '' ?>>Descartado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Localização</label>
                                <input type="text" name="localizacao" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['localizacao'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantidade</label>
                                <input type="number" name="quantidade" class="form-control" value="<?= intval($item_inventario_editar['quantidade']) ?>" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data Aquisição (opcional)</label>
                                <input type="date" name="data_aquisicao" class="form-control" value="<?= htmlspecialchars($item_inventario_editar['data_aquisicao'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Custo Unit. (R$)</label>
                                <input type="number" step="0.01" name="custo" class="form-control" value="<?= floatval($item_inventario_editar['custo']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes" class="form-control" rows="2"><?= htmlspecialchars($item_inventario_editar['observacoes'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <button type="submit" name="editar_item_inventario" class="btn btn-primary mt-3">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'auditoria'): // (Erro 5 e 8) ?>
            <div class="row mb-4">
                <div class="col-md-6"><h2><i class="fas fa-history"></i> Relatório de Auditoria</h2></div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5></div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="page" value="auditoria">
                        <div class="col-md-2">
                            <label class="form-label">Ação</label>
                            <select name="acao" class="form-select">
                                <option value="">Todas</option>
                                <option value="Inserir" <?= (($_GET['acao'] ?? '') == 'Inserir') ? 'selected' : '' ?>>Inserir</option>
                                <option value="Atualizar" <?= (($_GET['acao'] ?? '') == 'Atualizar') ? 'selected' : '' ?>>Atualizar</option>
                                <option value="Excluir" <?= (($_GET['acao'] ?? '') == 'Excluir') ? 'selected' : '' ?>>Excluir</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tabela</label>
                            <select name="tabela" class="form-select">
                                <option value="">Todas</option>
                                <option value="ativos_hardware" <?= (($_GET['tabela'] ?? '') == 'ativos_hardware') ? 'selected' : '' ?>>Ativos</option>
                                <option value="usuarios" <?= (($_GET['tabela'] ?? '') == 'usuarios') ? 'selected' : '' ?>>Usuários</option>
                                <option value="chamados_ti" <?= (($_GET['tabela'] ?? '') == 'chamados_ti') ? 'selected' : '' ?>>Chamados</option>
                                <option value="manutencoes" <?= (($_GET['tabela'] ?? '') == 'manutencoes') ? 'selected' : '' ?>>Manutenções</option>
                                <option value="inventario_componentes" <?= (($_GET['tabela'] ?? '') == 'inventario_componentes') ? 'selected' : '' ?>>Inventário</option>
                            </select>
                        </div>
                         <div class="col-md-3">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>">
                        </div>
                         <div class="col-md-3">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list"></i> Últimas Ações</h5></div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 70vh;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Ação</th>
                                    <th>Tabela</th>
                                    <th>Registro ID</th>
                                    <th>IP</th>
                                    <th>Dados Novos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lista_logs)): ?>
                                    <tr><td colspan="6" class="text-center text-muted p-5">Nenhum log encontrado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lista_logs as $log): ?>
                                        <tr>
                                            <td><small><?= formatar_data($log['timestamp'], 'd/m/Y H:i:s') ?></small></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($log['acao'] ?? '') ?></span></td>
                                            <td><small><?= htmlspecialchars($log['tabela'] ?? '') ?></small></td>
                                            <td><small><?= htmlspecialchars($log['registro_id'] ?? '') ?></small></td>
                                            <td><small><?= htmlspecialchars($log['ip'] ?? '') ?></small></td>
                                            <td><small title="<?= htmlspecialchars($log['dados_novos'] ?? '') ?>"><?= substr(htmlspecialchars($log['dados_novos'] ?? '-'), 0, 100) ?>...</small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($pagina_atual === 'relatorios'): ?>
            <div class="row mb-4">
                <div class="col-md-6"><h2><i class="fas fa-chart-bar"></i> Relatório de Ativos</h2></div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=relatorios&acao=exportar&formato=csv"><i class="fas fa-file-csv"></i> CSV</a></li>
                            <li><a class="dropdown-item" href="?page=relatorios&acao=exportar&formato=json"><i class="fas fa-file-code"></i> JSON</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5></div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="page" value="relatorios">
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Todos Status</option>
                                <option value="Ativo" <?= (($_GET['status'] ?? '') == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                                <option value="Manutenção" <?= (($_GET['status'] ?? '') == 'Manutenção') ? 'selected' : '' ?>>Manutenção</option>
                                <option value="Estoque" <?= (($_GET['status'] ?? '') == 'Estoque') ? 'selected' : '' ?>>Estoque</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="tipo_ativo">
                                <option value="">Todos Tipos</option>
                                <option value="NTB" <?= (($_GET['tipo_ativo'] ?? '') == 'NTB') ? 'selected' : '' ?>>Notebook</option>
                                <option value="DSK" <?= (($_GET['tipo_ativo'] ?? '') == 'DSK') ? 'selected' : '' ?>>Desktop</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-table"></i> Resultado (<?= count($relatorios_dados) ?> registros)</h5></div>
                <div class="card-body">
                    <?php if (!empty($relatorios_dados)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Máquina</th>
                                        <th>Usuário</th>
                                        <th>Local</th>
                                        <th>Status</th>
                                        <th>Tipo</th>
                                        <th>CPU</th>
                                        <th>RAM</th>
                                        <th>Custo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($relatorios_dados as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nome_maquina'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['nome_usuario'] ?? '-') ?></td>
                                            <td><?= $row['loc_cidade'] ?>-<?= $row['loc_predio'] ?></td>
                                            <td><span class="status-badge <?= get_status_badge_class($row['status']) ?>"><?= $row['status'] ?></span></td>
                                            <td><?= $row['tipo_ativo'] ?></td>
                                            <td><?= htmlspecialchars($row['modelo_cpu'] ?? '-') ?></td>
                                            <td><?= $row['memoria_gb'] ?> GB</td>
                                            <td><?= formatar_moeda($row['custo_aquisicao']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-invoice fa-3x mb-2"></i><br>
                            Nenhum dado encontrado
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <footer class="mt-5 text-center text-muted small">
            <p><?= SISTEMA_NOME ?> v<?= SISTEMA_VERSAO ?> &copy; <?= date('Y') ?></p>
        </footer>
    </div>

    <!-- Modal Adicionar Ativo (ATUALIZADO V3.2 - Erro 7 & 9) -->
    <div class="modal fade" id="modalAdicionar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus"></i> Adicionar Ativo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form method="POST" id="formAdicionar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-primary">Localização (Padrão: A-B-C-D-E)</h6>
                                <div class="row g-2">
                                    <div class="col-6"><label class="form-label small">A: Cidade</label>
                                        <select name="loc_cidade" class="form-select" required>
                                            <?php foreach (LOC_CIDADES as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6"><label class="form-label small">B: Prédio/Unidade</label>
                                        <select name="loc_predio" class="form-select" required>
                                            <?php foreach (LOC_PREDIOS as $p): ?><option value="<?= $p ?>"><?= $p ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6"><label class="form-label small mt-2">C: Setor</label>
                                        <select name="loc_setor" class="form-select" required>
                                            <?php foreach (LOC_SETORES as $key => $label): ?><option value="<?= $key ?>"><?= $key ?> (<?= $label ?>)</option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6"><label class="form-label small mt-2">D: Posição/Tipo</label>
                                        <select name="loc_tipo" class="form-select" required>
                                             <?php foreach (LOC_TIPOS as $key => $label): ?><option value="<?= $key ?>"><?= $key ?> (<?= $label ?>)</option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12"><label class="form-label small mt-2">E: ID (Número)</label>
                                        <input type="number" name="loc_id" class="form-control" placeholder="ID (Ex: 01, 02)" required min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Identificação</h6>
                                <label class="form-label small">Status Inicial</label>
                                <select class="form-select" name="status">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Estoque" selected>Estoque</option>
                                </select>
                                <label class="form-label small mt-2">Tipo de Ativo</label>
                                <select class="form-select" name="tipo_ativo">
                                    <option value="DSK">Desktop</option>
                                    <option value="NTB">Notebook</option>
                                </select>
                                <label class="form-label small mt-2">IP (opcional)</label>
                                <input type="text" name="ip_address" class="form-control" placeholder="IP (opcional)">
                                <label class="form-label small mt-2">Sistema Operacional</label>
                                <input type="text" name="sistema_operacional" class="form-control" placeholder="SO" required>
                            </div>
                            <div class="col-12">
                                <h6 class="text-primary">Atribuição</h6>
                                <select class="form-select" name="usuario_id_atual">
                                    <option value="0">Nenhum (Deixar em Estoque)</option>
                                    <?php foreach ($lista_usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id'] ?>">
                                            <?= htmlspecialchars($usuario['nome_completo'] ?? '') ?> (<?= htmlspecialchars($usuario['setor'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <h6 class="text-primary">Especificações</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="text" name="modelo_cpu" class="form-control" placeholder="CPU" required>
                                        <input type="number" name="memoria_gb" class="form-control mt-2" placeholder="Memória GB" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="modelo_disco_1" class="form-control" placeholder="Modelo Disco" required>
                                        <input type="text" name="serial_disco_1" class="form-control mt-2" placeholder="Serial Disco" required>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6">
                                        <label for="add_data_aquisicao" class="form-label small">Data Aquisição</label>
                                        <input type="date" id="add_data_aquisicao" name="data_aquisicao" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="add_custo_aquisicao" class="form-label small">Custo</label>
                                        <input type="number" id="add_custo_aquisicao" step="0.01" name="custo_aquisicao" class="form-control" placeholder="Custo" required>
                                    </div>
                                </div>
                                <label for="add_garantia_fim" class="form-label small mt-2">Fim da Garantia (opcional)</label>
                                <input type="date" id="add_garantia_fim" name="garantia_fim" class="form-control">
                            </div>
                            <!-- (Erro 9) Observações -->
                            <div class="col-12">
                                <h6 class="text-primary">Observações</h6>
                                <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações (opcional)"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="adicionar_ativo" form="formAdicionar" class="btn btn-success">
                        <i class="fas fa-save"></i> Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Usuário -->
    <div class="modal fade" id="modalAdicionarUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus"></i> Adicionar Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form method="POST" id="formAddUsuario">
                        <div class="mb-3">
                            <label for="nome_completo" class="form-label">Nome Completo</label>
                            <input type="text" name="nome_completo" id="nome_completo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="setor" class="form-label">Setor</label>
                            <input type="text" name="setor" id="setor" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="adicionar_usuario" form="formAddUsuario" class="btn btn-success">
                        <i class="fas fa-save"></i> Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Abrir Chamado -->
    <div class="modal fade" id="modalAbrirChamado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-life-ring"></i> Abrir Novo Chamado</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form method="POST" id="formAbrirChamado">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cham_nome_maquina" class="form-label">Ativo (Computador)</label>
                                <select name="nome_maquina" id="cham_nome_maquina" class="form-select" required>
                                    <option value="">Selecione o ativo...</option>
                                    <?php foreach ($lista_ativos_all as $ativo): ?>
                                        <option value="<?= $ativo['nome_maquina'] ?>">
                                            <?= htmlspecialchars($ativo['nome_maquina'] ?? '') ?> (<?= htmlspecialchars($ativo['nome_usuario'] ?? 'Sem Usuário') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="cham_usuario" class="form-label">Usuário Relator</label>
                                <select name="usuario_id_relator" id="cham_usuario" class="form-select" required>
                                    <option value="">Selecione o usuário...</option>
                                    <?php foreach ($lista_usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id'] ?>">
                                            <?= htmlspecialchars($usuario['nome_completo'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="cham_categoria" class="form-label">Categoria</label>
                                <select name="categoria" id="cham_categoria" class="form-select" required>
                                    <option value="Hardware">Hardware (lento, não liga, tela, etc)</option>
                                    <option value="Software">Software (sistema, erro, lentidão)</option>
                                    <option value="Rede">Rede (sem internet, sem acesso)</option>
                                    <option value="Impressora">Impressora</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="cham_descricao" class="form-label">Descrição do Problema</label>
                                <textarea name="descricao" id="cham_descricao" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="adicionar_chamado" form="formAbrirChamado" class="btn btn-danger">
                        <i class="fas fa-save"></i> Abrir Chamado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Manutenção (V3.1) -->
    <div class="modal fade" id="modalAdicionarManutencao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-tools"></i> Registrar Manutenção</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form method="POST" id="formAddManutencao">
                        <!-- O valor é preenchido dinamicamente se $ativo_detalhes existir -->
                        <input type="hidden" name="nome_maquina" value="<?= htmlspecialchars($ativo_detalhes['nome_maquina'] ?? '') ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Data</label>
                                <input type="date" name="data_manutencao" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_manutencao" class="form-select">
                                    <option value="Preventiva">Preventiva</option>
                                    <option value="Corretiva">Corretiva</option>
                                    <option value="Preditiva">Preditiva</option>
                                    <option value="Emergencial">Emergencial</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Técnico Responsável</label>
                                <input type="text" name="tecnico_responsavel" class="form-control" placeholder="Nome do Técnico" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Custo (R$)</label>
                                <input type="number" step="0.01" name="custo" class="form-control" value="0.00">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Concluída">Concluída</option>
                                    <option value="Em Andamento">Em Andamento</option>
                                    <option value="Agendada">Agendada</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrição do Serviço</label>
                                <textarea name="descricao" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Peças Substituídas (opcional)</label>
                                <textarea name="pecas_substituidas" class="form-control" rows="2" placeholder="Ex: 1x SSD 240GB Kingston, 1x Mem 8GB DDR4"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="adicionar_manutencao" form="formAddManutencao" class="btn btn-success">
                        <i class="fas fa-save"></i> Registrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Item Inventário (ATUALIZADO V3.2 - Erro 1) -->
    <div class="modal fade" id="modalAdicionarItemInventario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus"></i> Adicionar Item ao Inventário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form method="POST" id="formAddItemInventario">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Item</label>
                                <input type="text" name="nome_item" class="form-control" placeholder="Ex: Teclado USB Dell KB216" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Categoria</label>
                                <select name="categoria" class="form-select">
                                    <option value="Peças">Peças (Mouse, Teclado, SSD, etc)</option>
                                    <option value="Infra">Infra (Cabos, Conectores, etc)</option>
                                    <option value="Licenças">Licenças (Software, SO)</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo (Ex: Teclado, Cabo)</label>
                                <input type="text" name="tipo_componente" class="form-control" placeholder="Ex: Teclado, Cabo de Força">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fabricante</label>
                                <input type="text" name="fabricante" class="form-control" placeholder="Ex: Dell, Logitech">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="modelo" class="form-control" placeholder="Ex: KB216, M90">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Número de Série (opcional, p/ itens únicos como monitores)</label>
                                <input type="text" name="numero_serie" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Estoque">Estoque</option>
                                    <option value="Em Uso">Em Uso</option>
                                    <option value="Defeito">Defeito</option>
                                    <option value="Descartado">Descartado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Localização</label>
                                <input type="text" name="localizacao" class="form-control" value="Armazem TI">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantidade</label>
                                <input type="number" name="quantidade" class="form-control" value="1" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data Aquisição (opcional)</label>
                                <input type="date" name="data_aquisicao" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Custo Unit. (R$)</label>
                                <input type="number" step="0.01" name="custo" class="form-control" value="0.00">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes" class="form-control" rows="2" placeholder="Ex: Pedido 12345"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="adicionar_item_inventario" form="formAddItemInventario" class="btn btn-success">
                        <i class="fas fa-save"></i> Salvar Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Previsão Falha -->
    <div class="modal fade" id="modalPrevisao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-chart-line"></i> Análise Preditiva</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="modalPrevisaoBody">
                    <p class="text-center"><div class="spinner"></div> Analisando dados...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Exportação (Página Ativos)
    // ATUALIZADO V3.2 (Erro 7): Inclui novos filtros na exportação
    const currentParams = new URLSearchParams(window.location.search);
    const filterParams = new URLSearchParams();
    if (currentParams.has('busca')) filterParams.append('busca', currentParams.get('busca'));
    if (currentParams.has('status')) filterParams.append('status', currentParams.get('status'));
    if (currentParams.has('tipo_ativo')) filterParams.append('tipo_ativo', currentParams.get('tipo_ativo'));
    if (currentParams.has('loc_predio')) filterParams.append('loc_predio', currentParams.get('loc_predio'));
    if (currentParams.has('loc_setor')) filterParams.append('loc_setor', currentParams.get('loc_setor'));
    const buscaParams = filterParams.toString();

    if (document.getElementById('export-csv')) {
        document.getElementById('export-csv').href = '?page=ativos&acao=exportar&formato=csv&' + buscaParams;
    }
    if (document.getElementById('export-json')) {
        document.getElementById('export-json').href = '?page=ativos&acao=exportar&formato=json&' + buscaParams;
    }

    // Modal de Previsão
    var modalPrevisao = new bootstrap.Modal(document.getElementById('modalPrevisao'));
    function preverFalha(maquina) {
        modalPrevisao.show();
        document.getElementById('modalPrevisaoBody').innerHTML = '<p class="text-center"><div class="spinner"></div> Analisando dados...</p>';
        
        var formData = new FormData();
        formData.append('api_prever_falha', '1');
        formData.append('maquina', maquina);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var p = data.previsao;
                // Usando as classes de badge definidas no PHP
                var badge = '<?= get_risco_badge_class("Baixo") ?>'; // Default
                if (p.risco === 'Crítico') badge = '<?= get_risco_badge_class("Crítico") ?>';
                else if (p.risco === 'Alto') badge = '<?= get_risco_badge_class("Alto") ?>';
                else if (p.risco === 'Médio') badge = '<?= get_risco_badge_class("Médio") ?>';
                
                document.getElementById('modalPrevisaoBody').innerHTML = `
                    <h5>Risco: <span class="badge ${badge}">${p.risco}</span> (Confiança: ${p.confianca}%)</h5>
                    <p><strong>Recomendação:</strong> ${p.recomendacao}</p>
                    <hr>
                    <p class="small text-muted"><strong>Dados analisados:</strong><br>
                    - Idade (desde últ. manut.): ${p.idade_dias} dias.<br>
                    - Alertas de hardware (30d): ${p.alertas_recentes}.<br>
                    - Chamados de TI (90d): ${p.chamados_recentes}.
                    </p>
                `;
            } else {
                document.getElementById('modalPrevisaoBody').innerHTML = '<p class="text-danger">Erro ao analisar: ' + data.error + '</p>';
            }
        })
        .catch(error => {
            document.getElementById('modalPrevisaoBody').innerHTML = '<p class="text-danger">Erro de conexão.</p>';
        });
    }

    <?php if ($pagina_atual === 'dashboard'): ?>
    // Gráfico 1: Status
    const statusData = <?= json_encode($stats['status_grafico']) ?>;
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(row => row.status),
            datasets: [{
                data: statusData.map(row => row.quantidade),
                backgroundColor: [
                    '#198754', // Ativo (Success)
                    '#ffc107', // Manutenção (Warning)
                    '#0dcaf0', // Estoque (Info)
                    '#dc3545', // Desativado (Danger)
                    '#212529'  // Outro (Dark)
                ]
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Gráfico 2: Localização
    const localData = <?= json_encode($stats['localizacao_grafico']) ?>;
    new Chart(document.getElementById('localizacaoChart'), {
        type: 'bar',
        data: {
            labels: localData.map(row => row.localizacao),
            datasets: [{
                label: 'Qtd. Ativos',
                data: localData.map(row => row.quantidade),
                backgroundColor: '#0d6efd'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
    });

    // Gráfico 3: Tipo (CORREÇÃO Erro 2)
    const tipoData = <?= json_encode($stats['tipo_grafico']) ?>;
    // Garantir que os dados existem antes de tentar mapear
    const tipoLabels = tipoData.length > 0 ? tipoData.map(row => row.tipo_ativo) : ['Nenhum'];
    const tipoValores = tipoData.length > 0 ? tipoData.map(row => row.quantidade) : [0];
    
    new Chart(document.getElementById('tipoChart'), {
        type: 'pie',
        data: {
            labels: tipoLabels,
            datasets: [{
                data: tipoValores,
                backgroundColor: ['#0d6efd', '#6c757d', '#198754', '#ffc107']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Alertas Dashboard (AJAX - Erro 4)
    function carregarAlertas() {
        var tbody = document.getElementById('alertasTable');
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'api_get_alertas=1'
        })
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = ''; // Limpar
            if (data.success && data.alertas.length > 0) {
                data.alertas.forEach(alerta => {
                    // Mapeamento de classes de badge (deve ser consistente com o PHP)
                    let badgeClass = 'bg-secondary';
                    if (alerta.gravidade === 'Crítico') badgeClass = 'bg-danger';
                    else if (alerta.gravidade === 'Médio') badgeClass = 'bg-info';
                    else if (alerta.gravidade === 'Baixo') badgeClass = 'bg-success';
                    let row = `
                    <tr>
                    <td>${alerta.nome_maquina}</td>
                    <td>${alerta.metric_type}</td>
                        <td>${alerta.metric_value} ${alerta.metric_unit}</td>
                        <td><span class="badge ${badgeClass}">${alerta.gravidade}</span></td>
                        <td>${alerta.mensagem}</td>
                        <td>${alerta.timestamp_formatado}</td>
                        <td><a href="?page=detalhes&maquina=${alerta.nome_maquina}" class="btn btn-sm btn-outline-info">Ver</a></td>
                        </tr>
                    `;
                tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum alerta recente encontrado.</td></tr>';
            }
        })
        .catch(error => {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erro ao carregar alertas.</td></tr>';
            console.error('Erro no fetch de alertas:', error);
        });
    }

    // Carregar na inicialização
    document.addEventListener('DOMContentLoaded', function() {
        carregarAlertas();
        setInterval(carregarAlertas, 30000); // (Erro 4) Atualiza a cada 30 segundos
    });
    <?php endif; ?> // Fim do if ($pagina_atual === 'dashboard')

    </script>
</body>
</html>