<?php
session_start(); // Inicia a sessão
require 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password); // Cria uma nova conexão PDO com o banco de dados
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define o modo de erro do PDO para exceções
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()]); // Exibe uma mensagem de erro se a conexão falhar
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Filtra e valida os dados do formulário
    $carroId = filter_input(INPUT_POST, 'editCarroId', FILTER_VALIDATE_INT);
    $distanciaPercorrida = filter_input(INPUT_POST, 'editDistanciaPercorrida', FILTER_VALIDATE_FLOAT);
    $naoParticipou = isset($_POST['editNaoParticipou']) ? 1 : 0;

    if ($carroId === false || ($distanciaPercorrida === false && !$naoParticipou)) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    try {
        if ($naoParticipou) {
            // Atualiza os dados da prova para indicar que o carro não participou
            $stmt = $conn->prepare('UPDATE prova_1 SET distancia_percorrida = 0, colocacao = 0, pontuacao = 0, nao_participou = :nao_participou WHERE carro_id = :carro_id');
        } else {
            // Atualiza os dados da prova com a nova distância percorrida
            $stmt = $conn->prepare('UPDATE prova_1 SET distancia_percorrida = :distancia_percorrida, nao_participou = :nao_participou WHERE carro_id = :carro_id');
            $stmt->bindParam(':distancia_percorrida', $distanciaPercorrida);
        }
        $stmt->bindParam(':nao_participou', $naoParticipou);
        $stmt->bindParam(':carro_id', $carroId);
        $stmt->execute();

        // Atualiza a colocação e pontuação
        if (!$naoParticipou) {
            $stmt = $conn->prepare('SELECT id, distancia_percorrida FROM prova_1 WHERE nao_participou = 0 ORDER BY distancia_percorrida DESC');
            $stmt->execute();
            $colocacao = 0;
            $ultimaDistancia = null;
            $posicaoAtual = 0;
            $empateContador = 0;

            while ($row = $stmt->fetch()) {
                $posicaoAtual++;

                if ($row['distancia_percorrida'] != $ultimaDistancia) {
                    $colocacao = $posicaoAtual;
                    $ultimaDistancia = $row['distancia_percorrida'];
                    $empateContador = 0;
                } else {
                    $empateContador++;
                }

                $pontuacao = calcularPontuacao($colocacao);
                $stmtUpdate = $conn->prepare('UPDATE prova_1 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                $stmtUpdate->bindParam(':colocacao', $colocacao);
                $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                $stmtUpdate->bindParam(':id', $row['id']);
                $stmtUpdate->execute();
            }
        }

        echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar dados: ' . $e->getMessage()]);
    }
}

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
?>
