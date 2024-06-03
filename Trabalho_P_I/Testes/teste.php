<?php
session_start();

// Conexão com o banco de dados
$host = 'localhost';
$dbname = 'carros';
$username = 'root';
$password = '1065';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão com o banco de dados: ' . $e->getMessage();
    exit;
}

// Função para calcular a pontuação com base na colocação
function calcularPontuacao($colocacao) {
    $regrasPontuacao = [
        1 => 1.0,
        2 => 1.0,
        3 => 1.0,
        4 => 0.8,
        5 => 0.8,
        6 => 0.8,
        7 => 0.6,
        8 => 0.6,
        9 => 0.4,
        10 => 0.4,
        11 => 0.4,
        12 => 0.4,
        13 => 0.4,
        14 => 0.4,
        15 => 0.4,
        16 => 0.4,
        17 => 0.4,
        18 => 0.4,
        19 => 0.4,
        20 => 0.4,
        21 => 0.4,
        22 => 0.4,
        23 => 0.4,
        24 => 0.4,
        25 => 0.4,
        26 => 0.4,
        27 => 0.4,
        28 => 0.4,
        29 => 0.4,
        30 => 0.4
    ];

    return $regrasPontuacao[$colocacao] ?? 0;
}

$formulario_submetido = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['cadastro_inicial'])) {
            $formulario_submetido = 'cadastro';
            $numeroCarro = filter_input(INPUT_POST, 'numero', FILTER_VALIDATE_INT);
            $nomeEquipe = filter_input(INPUT_POST, 'equipe', FILTER_SANITIZE_STRING);

            if ($numeroCarro === false || $nomeEquipe === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            $stmt = $conn->prepare('INSERT INTO carros (numero, equipe) VALUES (:numero, :equipe)');
            $stmt->bindParam(':numero', $numeroCarro);
            $stmt->bindParam(':equipe', $nomeEquipe);
            $stmt->execute();
        } elseif (isset($_POST['prova_1'])) {
            $formulario_submetido = 'prova1';
            $carroId = filter_input(INPUT_POST, 'carro_id', FILTER_VALIDATE_INT);
            $distanciaPercorrida = filter_input(INPUT_POST, 'distancia_percorrida', FILTER_VALIDATE_FLOAT);
            $naoParticipou = isset($_POST['nao_participou']) ? 1 : 0;

            // Verificar duplicata
            $stmt = $conn->prepare('SELECT COUNT(*) FROM prova_1 WHERE carro_id = :carro_id');
            $stmt->bindParam(':carro_id', $carroId);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists) {
                throw new Exception('Carro já cadastrado nesta prova.');
            }

            // Inserir dados se não existir
            $stmt = $conn->prepare('INSERT INTO prova_1 (carro_id, distancia_percorrida, nao_participou) VALUES (:carro_id, :distancia_percorrida, :nao_participou)');
            $stmt->bindParam(':carro_id', $carroId);
            $stmt->bindParam(':distancia_percorrida', $distanciaPercorrida);
            $stmt->bindParam(':nao_participou', $naoParticipou);
            $stmt->execute();

            if (!$naoParticipou) {
                $stmt = $conn->prepare('SELECT prova_1.id, prova_1.distancia_percorrida, carros.equipe, carros.numero FROM prova_1 JOIN carros ON prova_1.carro_id = carros.id ORDER BY distancia_percorrida DESC');
                $stmt->execute();
                $colocacao = 0;
                $ultimaDistancia = null;
                while ($row = $stmt->fetch()) {
                    if ($row['distancia_percorrida'] != $ultimaDistancia) {
                        $colocacao++;
                        $ultimaDistancia = $row['distancia_percorrida'];
                    }
                    $pontuacao = calcularPontuacao($colocacao);
                    $stmtUpdate = $conn->prepare('UPDATE prova_1 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                    $stmtUpdate->bindParam(':colocacao', $colocacao);
                    $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                    $stmtUpdate->bindParam(':id', $row['id']);
                    $stmtUpdate->execute();
                }
            } else {
                $stmt = $conn->prepare('INSERT INTO prova_1 (carro_id, distancia_percorrida, colocacao, pontuacao, nao_participou) VALUES (:carro_id, 0, 0, 0, :nao_participou)');
                $stmt->bindParam(':carro_id', $carroId);
                $stmt->bindParam(':nao_participou', $naoParticipou);
                $stmt->execute();
            }
        } elseif (isset($_POST['prova_2'])) {
            $formulario_submetido = 'prova2';
            $numeroCarro = filter_input(INPUT_POST, 'numero_prova2', FILTER_VALIDATE_INT);
            $tempo = filter_input(INPUT_POST, 'tempo', FILTER_VALIDATE_FLOAT);
            $penalidade = isset($_POST['penalidade']) ? 1 : 0;
            $naoParticipou = isset($_POST['nao_participou']) ? 1 : 0;

            // Verificar duplicata
            $stmt = $conn->prepare('SELECT COUNT(*) FROM prova_2 WHERE carro_id = :carro_id');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists) {
                throw new Exception('Carro já cadastrado nesta prova.');
            }

            // Inserir dados se não existir
            $stmt = $conn->prepare('INSERT INTO prova_2 (carro_id, tempo, penalidade, tempo_ajustado, nao_participou) VALUES (:carro_id, :tempo, :penalidade, :tempo_ajustado, :nao_participou)');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->bindParam(':tempo', $tempo);
            $stmt->bindParam(':penalidade', $penalidade);
            $tempoAjustado = $tempo + ($penalidade ? 2 : 0);
            $stmt->bindParam(':tempo_ajustado', $tempoAjustado);
            $stmt->bindParam(':nao_participou', $naoParticipou);
            $stmt->execute();

            if (!$naoParticipou) {
                $stmt = $conn->prepare('SELECT prova_2.id, prova_2.tempo_ajustado, carros.equipe, carros.numero FROM prova_2 JOIN carros ON prova_2.carro_id = carros.id ORDER BY tempo_ajustado ASC');
                $stmt->execute();
                $colocacao = 0;
                $ultimoTempo = null;
                while ($row = $stmt->fetch()) {
                    if ($row['tempo_ajustado'] != $ultimoTempo) {
                        $colocacao++;
                        $ultimoTempo = $row['tempo_ajustado'];
                    }
                    $pontuacao = calcularPontuacao($colocacao);
                    $stmtUpdate = $conn->prepare('UPDATE prova_2 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                    $stmtUpdate->bindParam(':colocacao', $colocacao);
                    $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                    $stmtUpdate->bindParam(':id', $row['id']);
                    $stmtUpdate->execute();
                }
            } else {
                $stmt = $conn->prepare('INSERT INTO prova_2 (carro_id, tempo, penalidade, tempo_ajustado, colocacao, pontuacao, nao_participou) VALUES (:carro_id, 0, 0, 0, 0, 0, :nao_participou)');
                $stmt->bindParam(':carro_id', $numeroCarro);
                $stmt->bindParam(':nao_participou', $naoParticipou);
                $stmt->execute();
            }
        } elseif (isset($_POST['prova_3'])) {
            $formulario_submetido = 'prova3';
            $numeroCarro = filter_input(INPUT_POST, 'numero_prova3', FILTER_VALIDATE_INT);
            $pesoRetentor = filter_input(INPUT_POST, 'peso_retentor', FILTER_VALIDATE_FLOAT);
            $naoParticipou = isset($_POST['nao_participou']) ? 1 : 0;

            // Verificar duplicata
            $stmt = $conn->prepare('SELECT COUNT(*) FROM prova_3 WHERE carro_id = :carro_id');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists) {
                throw new Exception('Carro já cadastrado nesta prova.');
            }

            // Inserir dados se não existir
            $stmt = $conn->prepare('INSERT INTO prova_3 (carro_id, peso_retentor, nao_participou) VALUES (:carro_id, :peso_retentor, :nao_participou)');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->bindParam(':peso_retentor', $pesoRetentor);
            $stmt->bindParam(':nao_participou', $naoParticipou);
            $stmt->execute();

            if (!$naoParticipou) {
                $stmt = $conn->prepare('SELECT prova_3.id, prova_3.peso_retentor, carros.equipe, carros.numero FROM prova_3 JOIN carros ON prova_3.carro_id = carros.id ORDER BY peso_retentor DESC');
                $stmt->execute();
                $colocacao = 0;
                $ultimoPeso = null;
                while ($row = $stmt->fetch()) {
                    if ($row['peso_retentor'] != $ultimoPeso) {
                        $colocacao++;
                        $ultimoPeso = $row['peso_retentor'];
                    }
                    $pontuacao = calcularPontuacao($colocacao);
                    $stmtUpdate = $conn->prepare('UPDATE prova_3 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                    $stmtUpdate->bindParam(':colocacao', $colocacao);
                    $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                    $stmtUpdate->bindParam(':id', $row['id']);
                    $stmtUpdate->execute();
                }
            } else {
                $stmt = $conn->prepare('INSERT INTO prova_3 (carro_id, peso_retentor, colocacao, pontuacao, nao_participou) VALUES (:carro_id, 0, 0, 0, :nao_participou)');
                $stmt->bindParam(':carro_id', $numeroCarro);
                $stmt->bindParam(':nao_participou', $naoParticipou);
                $stmt->execute();
            }
        }

        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Formulário submetido com sucesso!'
        ];

        header('Location: ' . $_SERVER['PHP_SELF'] . '?formulario_submetido=' . $formulario_submetido);
        exit;
    } catch (Exception $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Erro ao submeter o formulário: ' . $e->getMessage()
        ];

        header('Location: ' . $_SERVER['PHP_SELF'] . '?formulario_submetido=' . $formulario_submetido);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competição de Carros de Controle Remoto</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .container {
            max-width: 1200px; /* Aumentar largura do container */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Competição de Carros</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Cadastro
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#" onclick="mostrarCadastro('cadastro')">Cadastro Inicial</a>
                        <a class="dropdown-item" href="#" onclick="mostrarCadastro('prova1')">Prova 1</a>
                        <a class="dropdown-item" href="#" onclick="mostrarCadastro('prova2')">Prova 2</a>
                        <a class="dropdown-item" href="#" onclick="mostrarCadastro('prova3')">Prova 3</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="mostrarCadastro('resultado')">Resultados Finais</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4" id="cadastro">
        <!-- Conteúdo do formulário de cadastro -->
        <h2>Cadastro Inicial</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="formulario_submetido" value="cadastro">
            <div class="form-group">
                <label for="numero">Número do Carro</label>
                <input type="text" class="form-control" id="numero" name="numero" placeholder="Digite o número do carro - exemplo: 1">
            </div>
            <div class="form-group">
                <label for="equipe">Equipe</label>
                <input type="text" class="form-control" id="equipe" name="equipe" placeholder="Digite o nome da equipe - exemplo: Equipe 1">
            </div>
            <button type="submit" name="cadastro_inicial" class="btn btn-primary">Enviar</button>
        </form>
        <div class="mt-4">
            <h3>Carros Cadastrados</h3>
            <table id="tabela-carros-cadastrados" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>N° Carro</th>
                        <th>Equipe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Exibe os carros cadastrados
                    $stmt = $conn->query("SELECT numero, equipe FROM carros");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['numero']}</td>";
                        echo "<td>{$row['equipe']}</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container mt-4" id="prova1" style="display: none;">
        <!-- Conteúdo da prova 1 -->
        <h2>Prova 1 - Subida de Rampa em 45º (contagem de distância)</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="formulario_submetido" value="prova1">
            <div class="form-group">
                <label for="carro_id">Número do Carro</label>
                <select class="form-control" aria-label="Default select example" id="carro_id" name="carro_id">
                    <option selected>Selecione o nº do carro</option>
                    <?php
                    $stmt = $conn->query("SELECT id, numero FROM carros");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['numero']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="distancia_percorrida">Distância Percorrida</label>
                <input type="text" class="form-control" id="distancia_percorrida" name="distancia_percorrida" placeholder="Distância em metros - exemplo: 2.5">
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="nao_participou" name="nao_participou">
                <label class="form-check-label" for="nao_participou">
                    Não participou
                </label>
            </div>
            <br>
            <button type="submit" name="prova_1" class="btn btn-primary">Enviar</button>
        </form>

        <!-- Tabela de Resultados da Prova 1 -->
        <div class="mt-4">
            <h3>Resultados da Primeira Prova</h3>
            <table id="tabela-resultados-prova1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>N° Carro</th>
                        <th>Equipe</th>
                        <th>Distância Percorrida (m)</th>
                        <th>Colocação</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Exibição dos resultados da primeira prova
                    $stmt = $conn->query("SELECT carros.numero, carros.equipe, prova_1.distancia_percorrida, prova_1.colocacao, prova_1.pontuacao 
                    FROM carros 
                    JOIN prova_1 ON carros.id = prova_1.carro_id 
                    WHERE prova_1.nao_participou = 0
                    ORDER BY prova_1.colocacao");

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['numero']}</td>";
                        echo "<td>{$row['equipe']}</td>";
                        echo "<td>{$row['distancia_percorrida']}m</td>";
                        echo "<td>{$row['colocacao']}°</td>";
                        echo "<td>" . number_format($row['pontuacao'], 1) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container mt-4" id="prova2" style="display: none;">
        <!-- Conteúdo da prova 2 -->
        <h2>Prova 2 - Velocidade Máxima com Manobrabilidade (contagem de tempo)</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="formulario_submetido" value="prova2">
            <div class="form-group">
                <label for="numero_prova2">Número do Carro</label>
                <select class="form-control" aria-label="Default select example" id="numero_prova2" name="numero_prova2">
                    <option selected>Selecione o nº do carro</option>
                    <?php
                    $stmt = $conn->query("SELECT id, numero FROM carros");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['numero']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="tempo">Tempo (segundos)</label>
                <input type="text" class="form-control" id="tempo" name="tempo" placeholder="Tempo em segundos - exemplo: 30">
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="penalidade" name="penalidade">
                <label class="form-check-label" for="penalidade">
                    Penalidade
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="nao_participou" name="nao_participou">
                <label class="form-check-label" for="nao_participou">
                    Não participou
                </label>
            </div>
            <br>
            <button type="submit" name="prova_2" class="btn btn-primary">Enviar</button>
        </form>

        <!-- Tabela de Resultados da Prova 2 -->
        <div class="mt-4">
            <h3>Resultados da Segunda Prova</h3>
            <table id="tabela-resultados-prova2" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>N° Carro</th>
                        <th>Equipe</th>
                        <th>Tempo(seg)</th>
                        <th>Penalidade</th>
                        <th>Tempo Ajustado(seg)</th>
                        <th>Colocação</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Exibição dos resultados da segunda prova
                    $stmt = $conn->query("SELECT prova_2.id, prova_2.carro_id, prova_2.tempo, prova_2.penalidade, prova_2.tempo_ajustado, prova_2.colocacao, prova_2.pontuacao, carros.numero, carros.equipe
                                        FROM prova_2
                                        JOIN carros ON prova_2.carro_id = carros.id
                                        WHERE prova_2.nao_participou = 0
                                        ORDER BY prova_2.colocacao");

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['numero']}</td>"; // Número do carro
                        echo "<td>{$row['equipe']}</td>"; // Nome da equipe
                        echo "<td>{$row['tempo']}s</td>"; // Tempo original
                        echo "<td>" . ($row['penalidade'] ? 'Sim' : 'Não') . "</td>"; // Penalidade
                        echo "<td>{$row['tempo_ajustado']}s</td>"; // Tempo ajustado
                        echo "<td>{$row['colocacao']}°</td>"; // Colocação
                        echo "<td>" . number_format($row['pontuacao'], 1) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container mt-4" id="prova3" style="display: none;">
        <!-- Conteúdo da prova 3 -->
        <h2>Prova 3 - Tração (Peso Retentor)</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="formulario_submetido" value="prova3">
            <div class="form-group">
                <label for="numero_prova3">Número do Carro:</label>
                <select class="form-control" aria-label="Default select example" id="numero_prova3" name="numero_prova3" required>
                    <option selected>Selecione o nº do carro</option>
                    <?php
                    $stmt = $conn->prepare('SELECT id, numero FROM carros');
                    $stmt->execute();
                    $carros = $stmt->fetchAll();
                    foreach ($carros as $carro) {
                        echo '<option value="' . $carro['id'] . '">' . $carro['numero'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="peso_retentor">Peso Retentor (kg):</label>
                <input type="number" step="0.01" class="form-control" id="peso_retentor" placeholder="Peso retentor em Kg - exemplo: 0.5" name="peso_retentor" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="nao_participou" name="nao_participou">
                <label class="form-check-label" for="nao_participou">
                    Não participou
                </label>
            </div>
            <br>
            <button type="submit" class="btn btn-primary" name="prova_3">Enviar</button>
        </form>
        <hr>
        <h3>Resultados da Prova 3</h3>
        <table id="tabela-resultados-prova3" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Número do Carro</th>
                    <th>Equipe</th>
                    <th>Peso Retentor (kg)</th>
                    <th>Colocação</th>
                    <th>Pontuação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare('SELECT p3.*, c.numero, c.equipe FROM prova_3 p3 JOIN carros c ON p3.carro_id = c.id WHERE p3.nao_participou = 0 ORDER BY p3.peso_retentor DESC');
                $stmt->execute();
                $resultados = $stmt->fetchAll();
                $colocacao = 0;
                $ultimoPeso = null;
                foreach ($resultados as $resultado) {
                    if ($resultado['peso_retentor'] != $ultimoPeso) {
                        $colocacao++;
                        $ultimoPeso = $resultado['peso_retentor'];
                    }
                    $resultado['colocacao'] = $colocacao;
                    $resultado['pontuacao'] = calcularPontuacao($colocacao);
                    echo '<tr>';
                    echo '<td>' . $resultado['numero'] . '</td>';
                    echo '<td>' . $resultado['equipe'] . '</td>';
                    echo '<td>' . $resultado['peso_retentor'] . 'kg</td>';
                    echo '<td>' . $resultado['colocacao'] . '°</td>';
                    echo '<td>' . number_format($resultado['pontuacao'], 1) . '</td>';
                    echo '</tr>';

                    $stmtUpdate = $conn->prepare('UPDATE prova_3 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                    $stmtUpdate->bindParam(':colocacao', $resultado['colocacao']);
                    $stmtUpdate->bindParam(':pontuacao', $resultado['pontuacao']);
                    $stmtUpdate->bindParam(':id', $resultado['id']);
                    $stmtUpdate->execute();
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Resultados Finais -->
    <div class="container mt-4" id="resultado" style="display: none;">
        <h2>Resultados Finais</h2>
        <table id="resultadosFinais" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Número do Carro</th>
                    <th>Nome da Equipe</th>
                    <th>Pontuação Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query('
                    SELECT carros.numero, carros.equipe, 
                           (IFNULL((SELECT SUM(pontuacao) FROM prova_1 WHERE prova_1.carro_id = carros.id), 0) +
                            IFNULL((SELECT SUM(pontuacao) FROM prova_2 WHERE prova_2.carro_id = carros.id), 0) +
                            IFNULL((SELECT SUM(pontuacao) FROM prova_3 WHERE prova_3.carro_id = carros.id), 0)) AS pontuacao_total
                    FROM carros
                    ORDER BY pontuacao_total DESC
                ');
                while ($row = $stmt->fetch()) {
                    echo '<tr>';
                    echo '<td>'.$row['numero'].'</td>';
                    echo '<td>'.$row['equipe'].'</td>';
                    echo '<td>'.$row['pontuacao_total'].'</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
    <script>
        // Função para mostrar a área de cadastro correspondente à prova selecionada
        function mostrarCadastro(prova) {
            // Esconde todas as áreas de cadastro
            document.querySelectorAll('.container[id^="cadastro"]').forEach(container => {
                container.style.display = 'none';
            });
            document.querySelectorAll('.container[id^="prova"]').forEach(container => {
                container.style.display = 'none';
            });
            document.querySelectorAll('.container[id^="resultado"]').forEach(container => {
                container.style.display = 'none';
            });
            // Mostra a área de cadastro da prova selecionada
            document.getElementById(prova).style.display = 'block';
        }

        // Inicializa o DataTable para a tabela de carros cadastrados
        $(document).ready(function () {
            $('#tabela-carros-cadastrados').DataTable({
                // Configurações do DataTable
                "language": {
                    "lengthMenu": "Mostrar _MENU_ resultados", // Texto personalizado para mostrar a quantidade de resultados
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados", // Texto personalizado para mostrar informações sobre a exibição de resultados
                    "search": "Pesquisar:", // Texto personalizado para a barra de pesquisa
                    "paginate": {
                        "first": "Primeira",
                        "last": "Última",
                        "next": "Próxima",
                        "previous": "Anterior"
                    }
                },
                "lengthMenu": [5, 10, 25, 50, 100] // Define as opções de seleção do seletor de resultados por página
            });
        });

        // Inicializa o DataTable para a tabela de resultados da prova 1
        $(document).ready(function () {
            $('#tabela-resultados-prova1').DataTable({
                // Configurações do DataTable
                "order": [[3, "asc"]], // Ordena pela 4ª coluna (colocação) em ordem ascendente
                "language": {
                    "lengthMenu": "Mostrar _MENU_ resultados", // Texto personalizado para mostrar a quantidade de resultados
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados", // Texto personalizado para mostrar informações sobre a exibição de resultados
                    "search": "Pesquisar:", // Texto personalizado para a barra de pesquisa
                    "paginate": {
                        "first": "Primeira",
                        "last": "Última",
                        "next": "Próxima",
                        "previous": "Anterior"
                    }
                },
                "lengthMenu": [5, 10, 25, 50, 100] // Define as opções de seleção do seletor de resultados por página
            });
        });

        // Inicializa o DataTable para a tabela de resultados da prova 2
        $(document).ready(function () {
            $('#tabela-resultados-prova2').DataTable({
                // Configurações do DataTable
                "order": [[4, "asc"]], // Ordena pela 5ª coluna (colocação) em ordem ascendente
                "language": {
                    "lengthMenu": "Mostrar _MENU_ resultados", // Texto personalizado para mostrar a quantidade de resultados
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados", // Texto personalizado para mostrar informações sobre a exibição de resultados
                    "search": "Pesquisar:", // Texto personalizado para a barra de pesquisa
                    "paginate": {
                        "first": "Primeira",
                        "last": "Última",
                        "next": "Próxima",
                        "previous": "Anterior"
                    }
                },
                "lengthMenu": [5, 10, 25, 50, 100] // Define as opções de seleção do seletor de resultados por página
            });
        });

        // Inicializa o DataTable para a tabela de resultados da prova 3
        $(document).ready(function () {
            $('#tabela-resultados-prova3').DataTable({
                // Configurações do DataTable
                "order": [[3, "asc"]], // Ordena pela 4ª coluna (colocação) em ordem ascendente
                "language": {
                    "lengthMenu": "Mostrar _MENU_ resultados", // Texto personalizado para mostrar a quantidade de resultados
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados", // Texto personalizado para mostrar informações sobre a exibição de resultados
                    "search": "Pesquisar:", // Texto personalizado para a barra de pesquisa
                    "paginate": {
                        "first": "Primeira",
                        "last": "Última",
                        "next": "Próxima",
                        "previous": "Anterior"
                    }
                },
                "lengthMenu": [5, 10, 25, 50, 100] // Define as opções de seleção do seletor de resultados por página
            });
        });

        // Inicializa o DataTable para a tabela de resultados finais
        $(document).ready(function () {
            $('#resultadosFinais').DataTable({
                // Configurações do DataTable
                "language": {
                    "lengthMenu": "Mostrar _MENU_ resultados", // Texto personalizado para mostrar a quantidade de resultados
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados", // Texto personalizado para mostrar informações sobre a exibição de resultados
                    "search": "Pesquisar:", // Texto personalizado para a barra de pesquisa
                    "paginate": {
                        "first": "Primeira",
                        "last": "Última",
                        "next": "Próxima",
                        "previous": "Anterior"
                    }
                },
                "lengthMenu": [5, 10, 25, 50, 100] // Define as opções de seleção do seletor de resultados por página
            });
        });

        // Exibe a seção correta após o envio do formulário
        $(document).ready(function() {
            var formularioSubmetido = "<?php echo $_GET['formulario_submetido'] ?? ''; ?>";
            if (formularioSubmetido) {
                mostrarCadastro(formularioSubmetido);
            }

            <?php if (isset($_SESSION['message'])): ?>
            toastr.<?php echo $_SESSION['message']['type']; ?>("<?php echo $_SESSION['message']['text']; ?>");
            <?php unset($_SESSION['message']); endif; ?>
        });
    </script>
</body>
</html>