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
    // Tratamento de erro na conexão com o banco de dados
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
        // Adicione mais regras de pontuação conforme necessário
    ];

    return $regrasPontuacao[$colocacao] ?? 0; // Retorna a pontuação correspondente ou 0 se não encontrada
}

// Verifica se foi enviado um formulário via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['cadastro_inicial'])) {
            // Validação dos dados do formulário de cadastro inicial
            $numeroCarro = filter_input(INPUT_POST, 'numero', FILTER_VALIDATE_INT);
            $nomeEquipe = filter_input(INPUT_POST, 'equipe', FILTER_SANITIZE_STRING);

            // Verifica se os dados do formulário são válidos
            if ($numeroCarro === false || $nomeEquipe === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            // Insere os dados na tabela carros
            $stmt = $conn->prepare('INSERT INTO carros (numero, equipe) VALUES (:numero, :equipe)');
            $stmt->bindParam(':numero', $numeroCarro);
            $stmt->bindParam(':equipe', $nomeEquipe);
            $stmt->execute();
        } elseif (isset($_POST['prova_1'])) {
            // Validação dos dados do formulário da prova 1
            $carroId = filter_input(INPUT_POST, 'carro_id', FILTER_VALIDATE_INT);
            $distanciaPercorrida = filter_input(INPUT_POST, 'distancia_percorrida', FILTER_VALIDATE_FLOAT);

            // Verifica se os dados do formulário são válidos
            if ($carroId === false || $distanciaPercorrida === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            // Insere os dados na tabela prova_1
            $stmt = $conn->prepare('INSERT INTO prova_1 (carro_id, distancia_percorrida) VALUES (:carro_id, :distancia_percorrida)');
            $stmt->bindParam(':carro_id', $carroId);
            $stmt->bindParam(':distancia_percorrida', $distanciaPercorrida);
            $stmt->execute();

            // Recupera a colocação com base na distância percorrida
            $stmt = $conn->prepare('SELECT id, distancia_percorrida FROM prova_1 ORDER BY distancia_percorrida DESC');
            $stmt->execute();
            $colocacao = 0;
            $ultimaDistancia = null;
            while ($row = $stmt->fetch()) {
                if ($row['distancia_percorrida'] != $ultimaDistancia) {
                    $colocacao++;
                    $ultimaDistancia = $row['distancia_percorrida'];
                }
                // Calcula a pontuação
                $pontuacao = calcularPontuacao($colocacao);
                // Atualiza a colocação e pontuação na tabela prova_1
                $stmtUpdate = $conn->prepare('UPDATE prova_1 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                $stmtUpdate->bindParam(':colocacao', $colocacao);
                $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                $stmtUpdate->bindParam(':id', $row['id']);
                $stmtUpdate->execute();
            }
        } elseif (isset($_POST['prova_2'])) {
            // Validação dos dados do formulário da prova 2
            $numeroCarro = filter_input(INPUT_POST, 'numero_prova2', FILTER_VALIDATE_INT);
            $tempo = filter_input(INPUT_POST, 'tempo', FILTER_VALIDATE_FLOAT);
            $penalidade = isset($_POST['penalidade']) ? 1 : 0;

            // Verifica se os dados do formulário são válidos
            if ($numeroCarro === false || $tempo === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            // Calcula o tempo ajustado com base na penalidade
            $tempoAjustado = $tempo + ($penalidade ? 2 : 0);

            // Insere os dados na tabela prova_2
            $stmt = $conn->prepare('INSERT INTO prova_2 (carro_id, tempo, penalidade, tempo_ajustado) VALUES (:carro_id, :tempo, :penalidade, :tempo_ajustado)');
            $stmt->bindParam(':carro_id', $numeroCarro);
            $stmt->bindParam(':tempo', $tempo);
            $stmt->bindParam(':penalidade', $penalidade);
            $stmt->bindParam(':tempo_ajustado', $tempoAjustado);
            $stmt->execute();

            // Recupera a colocação com base no tempo ajustado
            $stmt = $conn->prepare('SELECT id, tempo_ajustado FROM prova_2 ORDER BY tempo_ajustado ASC');
            $stmt->execute();
            $colocacao = 0;
            $ultimoTempo = null;
            while ($row = $stmt->fetch()) {
                if ($row['tempo_ajustado'] != $ultimoTempo) {
                    $colocacao++;
                    $ultimoTempo = $row['tempo_ajustado'];
                }
                // Calcula a pontuação
                $pontuacao = calcularPontuacao($colocacao);
                // Atualiza a colocação e pontuação na tabela prova_2
                $stmtUpdate = $conn->prepare('UPDATE prova_2 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                $stmtUpdate->bindParam(':colocacao', $colocacao);
                $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                $stmtUpdate->bindParam(':id', $row['id']);
                $stmtUpdate->execute();
            }
        } elseif (isset($_POST['prova_3'])) {
            // Validação dos dados do formulário da prova 3
            $carroId = filter_input(INPUT_POST, 'carro_id', FILTER_VALIDATE_INT);
            $pesoRetentor = filter_input(INPUT_POST, 'peso_retentor', FILTER_VALIDATE_FLOAT);

            // Verifica se os dados do formulário são válidos
            if ($carroId === false || $pesoRetentor === false) {
                throw new Exception('Dados do formulário inválidos.');
            }

            // Insere os dados na tabela prova_3
            $stmt = $conn->prepare('INSERT INTO prova_3 (carro_id, peso_retentor) VALUES (:carro_id, :peso_retentor)');
            $stmt->bindParam(':carro_id', $carroId);
            $stmt->bindParam(':peso_retentor', $pesoRetentor);
            $stmt->execute();

            // Recupera a colocação com base no peso do retentor
            $stmt = $conn->prepare('SELECT id, peso_retentor FROM prova_3 ORDER BY peso_retentor DESC');
            $stmt->execute();
            $colocacao = 0;
            $ultimoPeso = null;
            while ($row = $stmt->fetch()) {
                if ($row['peso_retentor'] != $ultimoPeso) {
                    $colocacao++;
                    $ultimoPeso = $row['peso_retentor'];
                }
                // Calcula a pontuação
                $pontuacao = calcularPontuacao($colocacao);
                // Atualiza a colocação e pontuação na tabela prova_3
                $stmtUpdate = $conn->prepare('UPDATE prova_3 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                $stmtUpdate->bindParam(':colocacao', $colocacao);
                $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                $stmtUpdate->bindParam(':id', $row['id']);
                $stmtUpdate->execute();
            }
        }
    } catch (Exception $e) {
        // Tratamento de erro ao processar o formulário
        echo 'Erro ao processar o formulário: ' . $e->getMessage();
    }
}

// Consulta os carros e suas pontuações
$queryCarros = 'SELECT c.numero, c.equipe, 
                COALESCE(p1.pontuacao, 0) AS pontuacao_prova_1, 
                COALESCE(p2.pontuacao, 0) AS pontuacao_prova_2,
                COALESCE(p3.pontuacao, 0) AS pontuacao_prova_3
                FROM carros c
                LEFT JOIN prova_1 p1 ON c.id = p1.carro_id
                LEFT JOIN prova_2 p2 ON c.id = p2.carro_id
                LEFT JOIN prova_3 p3 ON c.id = p3.carro_id';

$stmtCarros = $conn->prepare($queryCarros);
$stmtCarros->execute();
$carros = $stmtCarros->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrida de Carros</title>
    <!-- Adicionando Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Corrida de Carros</h1>

        <!-- Formulário de Cadastro Inicial -->
        <div class="mt-5">
            <h2>Cadastro Inicial</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="numero" class="form-label">Número do Carro:</label>
                    <input type="number" id="numero" name="numero" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="equipe" class="form-label">Nome da Equipe:</label>
                    <input type="text" id="equipe" name="equipe" class="form-control" required>
                </div>
                <input type="hidden" name="cadastro_inicial">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </form>
        </div>

        <!-- Formulário Prova 1 -->
        <div class="mt-5">
            <h2>Prova 1</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="carro_id" class="form-label">ID do Carro:</label>
                    <input type="number" id="carro_id" name="carro_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="distancia_percorrida" class="form-label">Distância Percorrida (km):</label>
                    <input type="number" step="0.01" id="distancia_percorrida" name="distancia_percorrida" class="form-control" required>
                </div>
                <input type="hidden" name="prova_1">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>

        <!-- Formulário Prova 2 -->
        <div class="mt-5">
            <h2>Prova 2</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="numero_prova2" class="form-label">ID do Carro:</label>
                    <input type="number" id="numero_prova2" name="numero_prova2" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="tempo" class="form-label">Tempo (segundos):</label>
                    <input type="number" step="0.01" id="tempo" name="tempo" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" id="penalidade" name="penalidade" class="form-check-input">
                    <label for="penalidade" class="form-check-label">Penalidade</label>
                </div>
                <input type="hidden" name="prova_2">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>

        <!-- Formulário Prova 3 -->
        <div class="mt-5">
            <h2>Prova 3</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="carro_id" class="form-label">ID do Carro:</label>
                    <input type="number" id="carro_id" name="carro_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="peso_retentor" class="form-label">Peso do Retentor (g):</label>
                    <input type="number" step="0.01" id="peso_retentor" name="peso_retentor" class="form-control" required>
                </div>
                <input type="hidden" name="prova_3">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>

        <!-- Tabela de Pontuações -->
        <div class="mt-5">
            <h2>Pontuações</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Número do Carro</th>
                        <th>Equipe</th>
                        <th>Pontuação Prova 1</th>
                        <th>Pontuação Prova 2</th>
                        <th>Pontuação Prova 3</th>
                        <th>Pontuação Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carros as $carro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($carro['numero']); ?></td>
                        <td><?php echo htmlspecialchars($carro['equipe']); ?></td>
                        <td><?php echo htmlspecialchars($carro['pontuacao_prova_1']); ?></td>
                        <td><?php echo htmlspecialchars($carro['pontuacao_prova_2']); ?></td>
                        <td><?php echo htmlspecialchars($carro['pontuacao_prova_3']); ?></td>
                        <td><?php echo htmlspecialchars($carro['pontuacao_prova_1'] + $carro['pontuacao_prova_2'] + $carro['pontuacao_prova_3']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
    </script>
</body>
</html>

