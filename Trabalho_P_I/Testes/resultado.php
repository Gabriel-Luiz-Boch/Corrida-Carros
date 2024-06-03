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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['cadastro_inicial'])) {
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
            $carroId = filter_input(INPUT_POST, 'carro_id', FILTER_VALIDATE_INT);
            $distanciaPercorrida = filter_input(INPUT_POST, 'distancia_percorrida', FILTER_VALIDATE_FLOAT);

            if ($carroId === false || $distanciaPercorrida === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            $stmt = $conn->prepare('INSERT INTO prova_1 (carro_id, distancia_percorrida) VALUES (:carro_id, :distancia_percorrida)');
            $stmt->bindParam(':carro_id', $carroId);
            $stmt->bindParam(':distancia_percorrida', $distanciaPercorrida);
            $stmt->execute();

            $stmt = $conn->prepare('SELECT id, distancia_percorrida FROM prova_1 ORDER BY distancia_percorrida DESC');
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
        } elseif (isset($_POST['prova_2'])) {
            $numeroCarro = filter_input(INPUT_POST, 'numero_prova2', FILTER_VALIDATE_INT);
            $tempo = filter_input(INPUT_POST, 'tempo', FILTER_VALIDATE_FLOAT);
            $penalidade = isset($_POST['penalidade']) ? 1 : 0;

            if ($numeroCarro === false || $tempo === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            $tempoAjustado = $tempo + ($penalidade ? 2 : 0);

            $stmt = $conn->prepare('INSERT INTO prova_2 (carro_id, tempo, penalidade, tempo_ajustado) VALUES (:carro_id, :tempo, :penalidade, :tempo_ajustado)');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->bindParam(':tempo', $tempo);
            $stmt->bindParam(':penalidade', $penalidade);
            $stmt->bindParam(':tempo_ajustado', $tempoAjustado);
            $stmt->execute();

            $stmt = $conn->prepare('SELECT id, tempo_ajustado FROM prova_2 ORDER BY tempo_ajustado ASC');
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
        } elseif (isset($_POST['prova_3'])) {
            $numeroCarro = filter_input(INPUT_POST, 'numero_prova3', FILTER_VALIDATE_INT);
            $pesoRetentor = filter_input(INPUT_POST, 'peso_retentor', FILTER_VALIDATE_FLOAT);

            if ($numeroCarro === false || $pesoRetentor === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            $stmt = $conn->prepare('INSERT INTO prova_3 (carro_id, peso_retentor) VALUES (:carro_id, :peso_retentor)');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->bindParam(':peso_retentor', $pesoRetentor);
            $stmt->execute();

            $stmt = $conn->prepare('SELECT id, peso_retentor FROM prova_3 ORDER BY peso_retentor DESC');
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
        }
    } catch (Exception $e) {
        echo 'Erro ao processar o formulário: ' . $e->getMessage();
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Competição</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#cadastro">Cadastro</a></li>
                <li class="nav-item"><a class="nav-link" href="#prova1">Prova 1</a></li>
                <li class="nav-item"><a class="nav-link" href="#prova2">Prova 2</a></li>
                <li class="nav-item"><a class="nav-link" href="#prova3">Prova 3</a></li>
                <li class="nav-item"><a class="nav-link" href="#resultado">Resultados</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Competição de Carros de Controle Remoto</h1>

        <!-- Formulário de Cadastro -->
        <section id="cadastro">
            <h2>Cadastro Inicial</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="numero">Número do Carro:</label>
                    <input type="number" class="form-control" id="numero" name="numero" required>
                </div>
                <div class="form-group">
                    <label for="equipe">Nome da Equipe:</label>
                    <input type="text" class="form-control" id="equipe" name="equipe" required>
                </div>
                <button type="submit" class="btn btn-primary" name="cadastro_inicial">Cadastrar</button>
            </form>
        </section>

        <!-- Formulário Prova 1 -->
        <section id="prova1" class="mt-5">
            <h2>Prova 1 - Distância Percorrida</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="carro_id">Número do Carro:</label>
                    <select class="form-control" id="carro_id" name="carro_id" required>
                        <?php
                        $stmt = $conn->query('SELECT id, numero FROM carros');
                        while ($row = $stmt->fetch()) {
                            echo '<option value="'.$row['id'].'">'.$row['numero'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="distancia_percorrida">Distância Percorrida (m):</label>
                    <input type="number" class="form-control" id="distancia_percorrida" name="distancia_percorrida" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary" name="prova_1">Registrar</button>
            </form>

            <h3>Resultados Prova 1</h3>
            <table id="resultadosProva1" class="display">
                <thead>
                    <tr>
                        <th>Número do Carro</th>
                        <th>Distância Percorrida (m)</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query('
                        SELECT carros.numero, prova_1.distancia_percorrida, prova_1.pontuacao 
                        FROM prova_1
                        INNER JOIN carros ON prova_1.carro_id = carros.id
                        ORDER BY prova_1.pontuacao DESC
                    ');
                    while ($row = $stmt->fetch()) {
                        echo '<tr>';
                        echo '<td>'.$row['numero'].'</td>';
                        echo '<td>'.$row['distancia_percorrida'].'</td>';
                        echo '<td>'.$row['pontuacao'].'</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Formulário Prova 2 -->
        <section id="prova2" class="mt-5">
            <h2>Prova 2 - Tempo de Volta</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="numero_prova2">Número do Carro:</label>
                    <select class="form-control" id="numero_prova2" name="numero_prova2" required>
                        <?php
                        $stmt = $conn->query('SELECT id, numero FROM carros');
                        while ($row = $stmt->fetch()) {
                            echo '<option value="'.$row['id'].'">'.$row['numero'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tempo">Tempo (segundos):</label>
                    <input type="number" class="form-control" id="tempo" name="tempo" step="0.01" required>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="penalidade" name="penalidade">
                    <label class="form-check-label" for="penalidade">Penalidade de 2 segundos</label>
                </div>
                <button type="submit" class="btn btn-primary" name="prova_2">Registrar</button>
            </form>

            <h3>Resultados Prova 2</h3>
            <table id="resultadosProva2" class="display">
                <thead>
                    <tr>
                        <th>Número do Carro</th>
                        <th>Tempo (segundos)</th>
                        <th>Penalidade</th>
                        <th>Tempo Ajustado (segundos)</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query('
                        SELECT carros.numero, prova_2.tempo, prova_2.penalidade, prova_2.tempo_ajustado, prova_2.pontuacao 
                        FROM prova_2
                        INNER JOIN carros ON prova_2.carro_id = carros.id
                        ORDER BY prova_2.pontuacao DESC
                    ');
                    while ($row = $stmt->fetch()) {
                        echo '<tr>';
                        echo '<td>'.$row['numero'].'</td>';
                        echo '<td>'.$row['tempo'].'</td>';
                        echo '<td>'.($row['penalidade'] ? 'Sim' : 'Não').'</td>';
                        echo '<td>'.$row['tempo_ajustado'].'</td>';
                        echo '<td>'.$row['pontuacao'].'</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Formulário Prova 3 -->
        <section id="prova3" class="mt-5">
            <h2>Prova 3 - Peso do Retentor</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="numero_prova3">Número do Carro:</label>
                    <select class="form-control" id="numero_prova3" name="numero_prova3" required>
                        <?php
                        $stmt = $conn->query('SELECT id, numero FROM carros');
                        while ($row = $stmt->fetch()) {
                            echo '<option value="'.$row['id'].'">'.$row['numero'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="peso_retentor">Peso Retentor (g):</label>
                    <input type="number" class="form-control" id="peso_retentor" name="peso_retentor" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary" name="prova_3">Registrar</button>
            </form>

            <h3>Resultados Prova 3</h3>
            <table id="resultadosProva3" class="display">
                <thead>
                    <tr>
                        <th>Número do Carro</th>
                        <th>Peso do Retentor (g)</th>
                        <th>Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query('
                        SELECT carros.numero, prova_3.peso_retentor, prova_3.pontuacao 
                        FROM prova_3
                        INNER JOIN carros ON prova_3.carro_id = carros.id
                        ORDER BY prova_3.pontuacao DESC
                    ');
                    while ($row = $stmt->fetch()) {
                        echo '<tr>';
                        echo '<td>'.$row['numero'].'</td>';
                        echo '<td>'.$row['peso_retentor'].'</td>';
                        echo '<td>'.$row['pontuacao'].'</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Resultados Finais -->
        <section id="resultado" class="mt-5">
            <h2>Resultados Finais</h2>
            <table id="resultadosFinais" class="display">
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
        </section>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#resultadosProva1').DataTable();
            $('#resultadosProva2').DataTable();
            $('#resultadosProva3').DataTable();
            $('#resultadosFinais').DataTable();

            // Smooth scrolling for navigation links
            $('a.nav-link').on('click', function(event) {
                if (this.hash !== "") {
                    event.preventDefault();
                    var hash = this.hash;
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top
                    }, 800, function(){
                        window.location.hash = hash;
                    });
                }
            });
        });
    </script>
</body>
</html>
