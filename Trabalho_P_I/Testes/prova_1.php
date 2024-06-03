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
        // Validação dos dados do formulário
        $numeroCarro = filter_input(INPUT_POST, 'numero', FILTER_VALIDATE_INT);
        $nomeEquipe = filter_input(INPUT_POST, 'equipe', FILTER_SANITIZE_STRING);
        $distanciaPercorrida = filter_input(INPUT_POST, 'distancia_percorrida', FILTER_VALIDATE_FLOAT);

        // Verifica se os dados do formulário são válidos
        if ($numeroCarro === false || $nomeEquipe === false || $distanciaPercorrida === false) {
            throw new Exception('Dados do formulário inválidos.');
        }

        // Insere os dados na tabela carros
        $stmt = $conn->prepare('INSERT INTO carros (numero, equipe) VALUES (:numero, :equipe)');
        $stmt->bindParam(':numero', $numeroCarro);
        $stmt->bindParam(':equipe', $nomeEquipe);
        $stmt->execute();

        // Recupera o ID do carro inserido
        $carroId = $conn->lastInsertId();

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
    } catch (Exception $e) {
        // Tratamento de erros no processamento do formulário
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
                        <a class="dropdown-item" href="#" onclick="mostrarCadastro('prova1')">Prova 1</a>
                        <!-- Adicione aqui opções para outras provas, se necessário -->
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#resultados">Resultados</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4" id="prova1">
        <h2>Cadastro de Carros - Prova 1</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="numero">Número do Carro</label>
                <input type="text" class="form-control" id="numero" name="numero">
            </div>
            <div class="form-group">
                <label for="equipe">Equipe</label>
                <input type="text" class="form-control" id="equipe" name="equipe">
            </div>
            <div class="form-group">
                <label for="distancia_percorrida">Distância Percorrida</label>
                <input type="text" class="form-control" id="distancia_percorrida" name="distancia_percorrida">
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>

    <div class="container mt-4" id="resultados">
        <h2>Resultados da Primeira Prova</h2>
        <table id="tabela-resultados" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Número do Carro</th>
                    <th>Equipe</th>
                    <th>Distância Percorrida</th>
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
                ORDER BY prova_1.colocacao");

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['numero']}</td>";
                    echo "<td>{$row['equipe']}</td>";
                    echo "<td>{$row['distancia_percorrida']}</td>";
                    echo "<td>{$row['colocacao']}</td>";
                    echo "<td>{$row['pontuacao']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
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
            document.querySelectorAll('.container[id^="prova"]').forEach(container => {
                container.style.display = 'none';
            });
            // Mostra a área de cadastro da prova selecionada
            document.getElementById(prova).style.display = 'block';
        }

        // Inicializa a DataTable para a tabela de resultados
        $(document).ready(function () {
            $('#tabela-resultados').DataTable({
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
