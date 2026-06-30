<?php
/**
 * api_dashboard.php — Dados para o Painel Admin (JSON)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

if (!$conexao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sem BD']);
    exit;
}

$acao = $_GET['acao'] ?? 'resumo';

switch ($acao) {

    case 'resumo':
        // ── KPIs principais ──
        $r = [];
        $r['total_moradores']          = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM morador"))[0] ?? 0);
        $r['total_admins']             = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM administrador"))[0] ?? 0);
        $r['total_apartamentos']       = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento"))[0] ?? 0);
        $r['apartamentos_disponiveis'] = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento WHERE estado='Disponivel'"))[0] ?? 0);
        $r['apartamentos_ocupados']    = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento WHERE estado IN ('Ocupado','ocupada','Ocupada')"))[0] ?? 0);
        $r['apartamentos_manutencao']  = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM apartamento WHERE estado IN ('Manutencao','Em Manutenção','manutencao')"))[0] ?? 0);
        $r['mensalidades_pendentes']   = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade WHERE estado='pendente'"))[0] ?? 0);
        $r['mensalidades_pagas']       = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade WHERE estado='paga'"))[0] ?? 0);
        $r['mensalidades_vencidas']    = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade WHERE estado='vencida'"))[0] ?? 0);
        $r['receitas_mes']             = (float)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COALESCE(SUM(valor_pago),0) FROM mensalidade_pagamento WHERE MONTH(data_pagamento)=MONTH(NOW()) AND YEAR(data_pagamento)=YEAR(NOW()) AND estado='confirmado'"))[0] ?? 0);
        $r['total_receitas']           = (float)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COALESCE(SUM(valor_pago),0) FROM mensalidade_pagamento WHERE estado='confirmado'"))[0] ?? 0);

        // ── Receitas dos últimos 6 meses (para gráfico de linha) ──
        $receitas6 = [];
        $labels6   = [];
        $nomes_mes_pt = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        for ($i = 5; $i >= 0; $i--) {
            $ts   = mktime(0,0,0, date('n') - $i, 1, date('Y'));
            $m    = (int)date('n', $ts);
            $y    = (int)date('Y', $ts);
            $val  = mysqli_fetch_row(mysqli_query($conexao,
                "SELECT COALESCE(SUM(valor_pago),0) FROM mensalidade_pagamento
                 WHERE MONTH(data_pagamento)=$m AND YEAR(data_pagamento)=$y AND estado='confirmado'"
            ))[0] ?? 0;
            $receitas6[] = (float)$val;
            $labels6[]   = $nomes_mes_pt[$m - 1] . ' ' . $y;
        }
        $r['receitas_6meses'] = $receitas6;
        $r['labels_6meses']   = $labels6;

        // ── Pagamentos por estado (para gráfico doughnut de pagamentos) ──
        $r['pag_confirmados'] = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade_pagamento WHERE estado='confirmado'"))[0] ?? 0);
        $r['pag_pendentes']   = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade_pagamento WHERE estado='pendente'"))[0] ?? 0);
        $r['pag_rejeitados']  = (int)(mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM mensalidade_pagamento WHERE estado='rejeitado'"))[0] ?? 0);

        $r['localizacao'] = "Viana, Luanda, Angola";
        $r['apoio']       = "+244 923 000 000";

        echo json_encode(['sucesso' => true, 'dados' => $r]);
        break;


    case 'casas':
        $sql = "SELECT a.id, b.letra as bloco, a.numero, a.andar, a.tipologia, a.estado, a.codigo,
                       m.nome as morador_nome
                FROM apartamento a
                JOIN bloco b ON b.id = a.id_bloco
                LEFT JOIN morador_apartamento ma ON ma.id_apartamento = a.id AND ma.activo = 1
                LEFT JOIN morador m ON m.id = ma.id_morador
                ORDER BY b.letra, a.numero";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'moradores':
        $sql = "SELECT m.id, m.nome, m.email, m.telefone, m.numbi, m.estado_conta,
                       a.codigo as apartamento
                FROM morador m
                LEFT JOIN morador_apartamento ma ON ma.id_morador = m.id AND ma.activo = 1
                LEFT JOIN apartamento a ON a.id = ma.id_apartamento
                ORDER BY m.nome";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'admins':
        $sql = "SELECT id, nome, email, funcao, activo, ultimo_login FROM administrador ORDER BY nome";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'mensalidades':
        $sql = "SELECT men.id, mor.nome, a.codigo as apartamento, men.servico,
                       men.mes, men.ano, men.valor, men.vencimento, men.estado
                FROM mensalidade men
                JOIN morador mor ON mor.id = men.id_morador
                JOIN apartamento a ON a.id = men.id_apartamento
                ORDER BY men.ano DESC, men.mes DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'pagamentos':
        // Inclui o recibo submetido para permitir aprovação do admin
        $sql = "SELECT mp.id, mor.nome as morador, a.codigo as apartamento,
                       mp.valor_pago, mp.metodo, mp.referencia,
                       mp.data_pagamento, mp.estado, mp.notas_admin,
                       mp.comprovativo_url as recibo_path
                FROM mensalidade_pagamento mp
                JOIN mensalidade men ON men.id = mp.id_mensalidade
                JOIN morador mor ON mor.id = men.id_morador
                JOIN apartamento a ON a.id = men.id_apartamento
                ORDER BY mp.data_pagamento DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'confirmar_pagamento':
        $id_pay   = intval($_POST['id'] ?? 0);
        $id_admin = intval($_SESSION['id'] ?? 0);
        $notas    = trim($_POST['notas'] ?? '');
        $estado   = $_POST['estado'] ?? 'confirmado'; // confirmado | rejeitado

        if (!$id_pay) { echo json_encode(['sucesso'=>false,'erro'=>'ID inválido']); exit; }

        $stmt = $conexao->prepare("UPDATE mensalidade_pagamento SET estado=?, confirmado_por=?, notas_admin=? WHERE id=?");
        $stmt->bind_param("sisi", $estado, $id_admin, $notas, $id_pay);

        if ($stmt->execute()) {
            // Se confirmado, actualiza a mensalidade para "pago"
            if ($estado === 'confirmado') {
                $mens_id = mysqli_fetch_row(mysqli_query($conexao, "SELECT id_mensalidade FROM mensalidade_pagamento WHERE id=$id_pay"))[0] ?? 0;
                if ($mens_id) {
                    mysqli_query($conexao, "UPDATE mensalidade SET estado='pago' WHERE id=$mens_id");
                }
            }
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'visitas':
        $sql = "SELECT v.id, m.nome as morador, a.codigo as apartamento, v.nome_visitante,
                       v.data_prevista, v.hora_prevista, v.estado, v.codigo_acesso
                FROM visita v
                JOIN morador m ON m.id = v.id_morador
                JOIN apartamento a ON a.id = v.id_apartamento
                ORDER BY v.data_prevista DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'agendamentos_area':
        $sql = "SELECT age.id, m.nome as morador, age.area_comum, age.data_evento,
                       age.hora_inicio, age.hora_fim, age.estado
                FROM agendamento age
                JOIN morador m ON m.id = age.id_morador
                ORDER BY age.data_evento DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'validar_agendamento':
        $id     = intval($_POST['id'] ?? 0);
        $tipo   = $_POST['tipo'] ?? 'area'; // area | visita
        $estado = $_POST['estado'] ?? 'confirmado'; // confirmado | cancelado / negado
        
        if (!$id) { echo json_encode(['sucesso'=>false,'erro'=>'ID inválido']); exit; }
        
        $tabela = ($tipo === 'area') ? 'agendamento' : 'visita';
        $col_estado = ($tipo === 'area') ? 'estado' : 'estado'; // Ambos usam 'estado'
        
        $stmt = $conexao->prepare("UPDATE $tabela SET estado=? WHERE id=?");
        $stmt->bind_param("si", $estado, $id);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        $stmt->close();
        break;

    case 'processar_morador':
        $id_morador = intval($_POST['id_morador'] ?? 0);
        $id_apartamento = intval($_POST['id_apartamento'] ?? 0);
        $estado = $_POST['estado'] ?? 'Activo';

        if (!$id_morador) { echo json_encode(['sucesso'=>false,'erro'=>'ID Morador inválido']); exit; }

        mysqli_begin_transaction($conexao);
        try {
            // Actualizar estado do morador
            $stmt = $conexao->prepare("UPDATE morador SET estado_conta = ? WHERE id = ?");
            $stmt->bind_param("si", $estado, $id_morador);
            $stmt->execute();
            $stmt->close();

            if ($id_apartamento > 0) {
                // Desactivar atribuições anteriores
                $stmt = $conexao->prepare("UPDATE morador_apartamento SET activo = 0 WHERE id_morador = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                // Nova atribuição
                $stmt = $conexao->prepare("INSERT INTO morador_apartamento (id_morador, id_apartamento, data_entrada, activo) VALUES (?, ?, NOW(), 1)");
                $stmt->bind_param("ii", $id_morador, $id_apartamento);
                $stmt->execute();
                $stmt->close();

                // Marcar casa como ocupada
                $stmt = $conexao->prepare("UPDATE apartamento SET estado = 'Ocupado' WHERE id = ?");
                $stmt->bind_param("i", $id_apartamento);
                $stmt->execute();
                $stmt->close();
            }

            mysqli_commit($conexao);
            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    case 'eliminar_morador':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['sucesso'=>false]); exit; }
        // Remove associações e o morador
        mysqli_query($conexao, "DELETE FROM morador_apartamento WHERE id_morador=$id");
        mysqli_query($conexao, "DELETE FROM mensalidade_pagamento WHERE id_mensalidade IN (SELECT id FROM mensalidade WHERE id_morador=$id)");
        mysqli_query($conexao, "DELETE FROM mensalidade WHERE id_morador=$id");
        mysqli_query($conexao, "DELETE FROM mensagem WHERE id_remetente=$id AND tipo_remetente='morador'");
        $res = mysqli_query($conexao, "DELETE FROM morador WHERE id=$id");
        echo json_encode(['sucesso' => $res]);
        break;

    case 'eliminar_casa':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['sucesso'=>false]); exit; }
        mysqli_query($conexao, "DELETE FROM morador_apartamento WHERE id_apartamento=$id");
        $res = mysqli_query($conexao, "DELETE FROM apartamento WHERE id=$id");
        echo json_encode(['sucesso' => $res]);
        break;

    case 'atualizar_morador':
        $id = intval($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $numbi = trim($_POST['numbi'] ?? '');
        
        if (!$id) { echo json_encode(['sucesso'=>false,'erro'=>'ID inválido']); exit; }
        
        $stmt = $conexao->prepare("UPDATE morador SET nome=?, telefone=?, email=?, numbi=? WHERE id=?");
        $stmt->bind_param("ssssi", $nome, $telefone, $email, $numbi, $id);
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'atualizar_admin':
        $id = intval($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $funcao = trim($_POST['funcao'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if (!$id) { echo json_encode(['sucesso'=>false,'erro'=>'ID inválido']); exit; }
        
        $stmt = $conexao->prepare("UPDATE administrador SET nome=?, email=?, funcao=?, activo=? WHERE id=?");
        $stmt->bind_param("sssii", $nome, $email, $funcao, $activo, $id);
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'eliminar_admin':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['sucesso'=>false]); exit; }
        $res = mysqli_query($conexao, "DELETE FROM administrador WHERE id=$id");
        echo json_encode(['sucesso' => $res]);
        break;
    
    case 'blocos':
        $res = mysqli_query($conexao, "SELECT id, letra, descricao FROM bloco ORDER BY letra");
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'cadastrar_admin':
        // Require admin session
        if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']); exit;
        }
        $nome     = trim($_POST['nome']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $senha    = $_POST['senha']    ?? '123456';
        $funcao_in = $_POST['funcao']   ?? 'Administrador';
        $telefone = trim($_POST['telefone'] ?? '');
        $numbi    = trim($_POST['numbi']    ?? '');
        $hash     = password_hash($senha, PASSWORD_DEFAULT);
        // Required NOT NULL defaults
        $nasc     = $_POST['nasc']      ?? date('Y-m-d', strtotime('-30 years'));
        $morada   = trim($_POST['morada'] ?? 'Luanda');
        $emissao  = $_POST['emissao']   ?? date('Y-m-d');
        $validade = $_POST['validade']  ?? date('Y-m-d', strtotime('+5 years'));
        $locale   = trim($_POST['locale'] ?? 'Luanda');
        $id_cond  = 1;

        // Map frontend role input strings to the exact MySQL administrator table ENUM values
        $funcao = 'Administrador';
        if (stripos($funcao_in, 'rh') !== false || stripos($funcao_in, 'recurso') !== false) {
            $funcao = 'Recursos Humanos';
        } elseif (stripos($funcao_in, 'segur') !== false) {
            $funcao = 'Seguranca';
        } elseif (stripos($funcao_in, 'tecn') !== false) {
            $funcao = 'Area Tecnica';
        } elseif (stripos($funcao_in, 'super') !== false) {
            $funcao = 'Super Admin';
        }

        if (!$nome || !$email || !$numbi) {
            echo json_encode(['sucesso' => false, 'erro' => 'Nome, e-mail e BI são obrigatórios']);
            break;
        }
        // Check duplicate email
        $chk = $conexao->prepare("SELECT id FROM administrador WHERE email=? LIMIT 1");
        $chk->bind_param("s", $email); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { echo json_encode(['sucesso'=>false,'erro'=>'Email já registado']); $chk->close(); break; }
        $chk->close();

        // Check duplicate BI
        $chk = $conexao->prepare("SELECT id FROM administrador WHERE numbi=? LIMIT 1");
        $chk->bind_param("s", $numbi); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { echo json_encode(['sucesso'=>false,'erro'=>'Nº de BI já registado']); $chk->close(); break; }
        $chk->close();

        $stmt = $conexao->prepare(
            "INSERT INTO administrador (id_condominio, nome, telefone, email, nasc, nacionalidade, morada, numbi, emissao_bi, validade_bi, locale_bi, funcao, iban, senha_hash, activo)
             VALUES (?, ?, ?, ?, ?, 'Angolana', ?, ?, ?, ?, 'Luanda', ?, '', ?, 1)"
        );
        $stmt->bind_param("issssssssss", $id_cond, $nome, $telefone, $email, $nasc, $morada, $numbi, $emissao, $validade, $funcao, $hash);
        if ($stmt->execute()) echo json_encode(['sucesso' => true]);
        else echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        $stmt->close();
        break;

    case 'cadastrar_morador':
        $nome       = trim($_POST['nome']      ?? '');
        $email      = trim($_POST['email']     ?? '');
        $senha      = $_POST['senha']      ?? '123456';
        $telefone   = trim($_POST['telefone']  ?? '');
        $numbi      = trim($_POST['numbi']     ?? '');
        $nascimento = !empty($_POST['nascimento']) ? $_POST['nascimento'] : date('Y-m-d', strtotime('-25 years'));
        $hash       = password_hash($senha, PASSWORD_DEFAULT);

        if (!$nome || !$email || !$numbi) {
            echo json_encode(['sucesso' => false, 'erro' => 'Nome, e-mail e BI são obrigatórios']);
            break;
        }
        // Check duplicate email
        $chk = $conexao->prepare("SELECT id FROM morador WHERE email=? LIMIT 1");
        $chk->bind_param("s", $email); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { echo json_encode(['sucesso'=>false,'erro'=>'Email já registado']); $chk->close(); break; }
        $chk->close();

        // Check duplicate BI
        $chk = $conexao->prepare("SELECT id FROM morador WHERE numbi=? LIMIT 1");
        $chk->bind_param("s", $numbi); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { echo json_encode(['sucesso'=>false,'erro'=>'Nº de BI já registado']); $chk->close(); break; }
        $chk->close();

        $emissao_bi_def = date('Y-m-d');
        $validade_bi_def = date('Y-m-d', strtotime('+10 years'));
        $locale_bi = trim($_POST['locale'] ?? 'Luanda');
        $stmt = $conexao->prepare(
            "INSERT INTO morador (nome, email, senha_hash, telefone, numbi, nasc, emissao_bi, validade_bi, locale_bi, estado_conta)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')"
        );
       $stmt->bind_param("sssssssss", $nome, $email, $hash, $telefone, $numbi, $nascimento, $emissao_bi_def, $validade_bi_def, $locale_bi);
        if ($stmt->execute()) echo json_encode(['sucesso' => true, 'id' => $stmt->insert_id]);
        else echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        $stmt->close();
        break;

    case 'cadastrar_casa':
        $id_bloco  = intval($_POST['id_bloco']  ?? 0);
        $numero    = trim($_POST['numero']    ?? '');
        $tipologia = $_POST['tipologia'] ?? 'V3';
        $andar     = $_POST['andar']     ?? '0';
        $estado    = $_POST['estado']    ?? 'Disponivel';

        if (!$id_bloco || $numero === '') {
            echo json_encode(['sucesso' => false, 'erro' => 'Bloco e número são obrigatórios']);
            break;
        }
        // Get bloco letra for unique codigo
        $bl = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT letra FROM bloco WHERE id=$id_bloco"));
        $letra = $bl['letra'] ?? 'X';
        $codigo = strtoupper($letra) . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);

        // Check duplicate
        $chk = $conexao->prepare("SELECT id FROM apartamento WHERE codigo=? LIMIT 1");
        $chk->bind_param("s", $codigo); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { echo json_encode(['sucesso'=>false,'erro'=>"Unidade $codigo já existe"]); $chk->close(); break; }
        $chk->close();

        $stmt = $conexao->prepare("INSERT INTO apartamento (id_bloco, numero, tipologia, andar, estado, codigo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $id_bloco, $numero, $tipologia, $andar, $estado, $codigo);
        if ($stmt->execute()) echo json_encode(['sucesso' => true, 'codigo' => $codigo]);
        else echo json_encode(['sucesso' => false, 'erro' => $stmt->error]);
        $stmt->close();
        break;

    case 'casas_disponiveis':
        // For the assignment modal — returns only available apartments
        $res = mysqli_query($conexao, "SELECT a.id, CONCAT(b.letra, '-', LPAD(a.numero,3,'0'), ' (', a.tipologia, ')') as label
                                       FROM apartamento a JOIN bloco b ON b.id = a.id_bloco
                                       WHERE a.estado='Disponivel' ORDER BY b.letra, a.numero");
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'listar_prospectos':
        if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']); exit;
        }
        $sql = "SELECT m.id, m.nome, m.email, m.telefone, m.numbi,
                       m.estado_conta, m.tipo_interesse,
                       m.preferencia_bloco, m.preferencia_tipologia, m.preferencia_andar,
                       m.observacoes, m.criado_em,
                       COALESCE(rp.estado, 'PendenteValidacao') as estado_processo,
                       rp.notas_admin, rp.validado_em, rp.id_apartamento_atribuido
                FROM morador m
                LEFT JOIN registo_prospecto rp ON rp.id_morador = m.id
                WHERE m.estado_conta IN ('AguardandoValidacaoPagamento','AguardandoAtribuicaoCasa','Aprovado','Pendente')
                ORDER BY m.criado_em DESC";
        $res = mysqli_query($conexao, $sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        echo json_encode(['sucesso' => true, 'dados' => $rows]);
        break;

    case 'processar_prospecto':
        if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'funcionario')) {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']); exit;
        }
        $id_morador = intval($_POST['id_morador'] ?? 0);
        $acao = $_POST['acao'] ?? ''; // 'validar_pagamento', 'atribuir_casa', 'rejeitar', 'aprovar'
        $notas = trim($_POST['notas'] ?? '');
        $id_apartamento = intval($_POST['id_apartamento'] ?? 0);

        if (!$id_morador) { echo json_encode(['sucesso'=>false,'erro'=>'ID Morador inválido']); exit; }

        mysqli_begin_transaction($conexao);
        try {
            if ($acao === 'validar_pagamento') {
                $id_admin = intval($_SESSION['id'] ?? 0);
                $stmt = $conexao->prepare("UPDATE morador SET estado_conta = 'AguardandoAtribuicaoCasa' WHERE id = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("
                    INSERT INTO registo_prospecto (id_morador, estado, validado_por, validado_em, notas_admin)
                    VALUES (?, 'Validado', ?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE estado='Validado', validado_por=?, validado_em=NOW(), notas_admin=?
                ");
                $stmt->bind_param("iissi", $id_morador, $id_admin, $notas, $id_admin, $notas);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("
                    SELECT mp.id FROM mensalidade_pagamento mp
                    JOIN mensalidade m ON m.id = mp.id_mensalidade
                    WHERE m.id_morador = ? AND mp.estado = 'pendente'
                    ORDER BY mp.data_pagamento DESC LIMIT 1
                ");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $res = $stmt->get_result();
                $pag = $res->fetch_assoc();
                $stmt->close();
                if ($pag) {
                    $stmt2 = $conexao->prepare("UPDATE mensalidade_pagamento SET estado='confirmado', confirmado_por=?, notas_admin=? WHERE id=?");
                    $stmt2->bind_param("isi", $id_admin, $notas, $pag['id']);
                    $stmt2->execute();
                    $stmt2->close();
                    $mp_id = $pag['id'];
                    $men_id = mysqli_fetch_row(mysqli_query($conexao, "SELECT id_mensalidade FROM mensalidade_pagamento WHERE id=$mp_id"))[0] ?? 0;
                    if ($men_id) {
                        mysqli_query($conexao, "UPDATE mensalidade SET estado='pago' WHERE id=$men_id");
                    }
                }
            }
            elseif ($acao === 'atribuir_casa') {
                if (!$id_apartamento) throw new Exception('Apartamento não seleccionado');

                $stmt = $conexao->prepare("UPDATE morador SET estado_conta = 'Aprovado' WHERE id = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("UPDATE morador_apartamento SET activo = 0 WHERE id_morador = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("INSERT INTO morador_apartamento (id_morador, id_apartamento, data_entrada, activo) VALUES (?, ?, NOW(), 1)");
                $stmt->bind_param("ii", $id_morador, $id_apartamento);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("UPDATE apartamento SET estado = 'Ocupado' WHERE id = ?");
                $stmt->bind_param("i", $id_apartamento);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("UPDATE registo_prospecto SET estado='Atribuido', id_apartamento_atribuido=? WHERE id_morador=?");
                $stmt->bind_param("ii", $id_apartamento, $id_morador);
                $stmt->execute();
                $stmt->close();

                $mensalidade_base = 140000;
                $data_atual = new DateTime();
                for ($i = 0; $i < 12; $i++) {
                    $mes = $data_atual->format('n');
                    $ano = $data_atual->format('Y');
                    $vencimento = $data_atual->format('Y-m-t');
                    $stmt = $conexao->prepare(
                        "INSERT INTO mensalidade (id_morador, id_apartamento, servico, mes, ano, valor, vencimento, estado) 
                         VALUES (?, ?, 'Quota Condominal', ?, ?, ?, ?, 'pendente')"
                    );
                    $stmt->bind_param("iiiisd", $id_morador, $id_apartamento, $mes, $ano, $mensalidade_base, $vencimento);
                    $stmt->execute();
                    $stmt->close();
                    $data_atual->modify('+1 month');
                }
            }
            elseif ($acao === 'rejeitar') {
                $stmt = $conexao->prepare("UPDATE morador SET estado_conta = 'Inactivo' WHERE id = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("
                    INSERT INTO registo_prospecto (id_morador, estado, notas_admin)
                    VALUES (?, 'Rejeitado', ?)
                    ON DUPLICATE KEY UPDATE estado='Rejeitado', notas_admin=?
                ");
                $stmt->bind_param("isi", $id_morador, $notas, $notas);
                $stmt->execute();
                $stmt->close();
            }
            elseif ($acao === 'aprovar') {
                $stmt = $conexao->prepare("UPDATE morador SET estado_conta = 'AguardandoAtribuicaoCasa' WHERE id = ?");
                $stmt->bind_param("i", $id_morador);
                $stmt->execute();
                $stmt->close();

                $stmt = $conexao->prepare("
                    INSERT INTO registo_prospecto (id_morador, estado, notas_admin)
                    VALUES (?, 'Validado', ?)
                    ON DUPLICATE KEY UPDATE estado='Validado', notas_admin=?
                ");
                $stmt->bind_param("isi", $id_morador, $notas, $notas);
                $stmt->execute();
                $stmt->close();
            }

            mysqli_commit($conexao);
            echo json_encode(['sucesso' => true]);
        } catch (Exception $e) {
            mysqli_rollback($conexao);
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Acção desconhecida']);
}
