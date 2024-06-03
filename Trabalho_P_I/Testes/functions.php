<?php
    function inserirCarro($conn, $numero, $equipe) {
        $stmt = $conn->prepare("INSERT INTO carros (numero, equipe) VALUES (:numero, :equipe)");
        $stmt->execute(['numero' => $numero, 'equipe' => $equipe]);
    }

    function obterCarros($conn) {
        $stmt = $conn->query("SELECT id, numero, equipe FROM carros");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function inserirProva1($conn, $carro_id, $distancia_percorrida) {
        $stmt = $conn->prepare("INSERT INTO prova_1 (carro_id, distancia_percorrida) VALUES (:carro_id, :distancia_percorrida)");
        $stmt->execute(['carro_id' => $carro_id, 'distancia_percorrida' => $distancia_percorrida]);
    }

    function obterResultadosProva1($conn) {
        $stmt = $conn->query("SELECT carros.numero, prova_1.distancia_percorrida, prova_1.colocacao, prova_1.pontuacao
                            FROM prova_1
                            JOIN carros ON prova_1.carro_id = carros.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function atualizarColocacaoPontuacaoProva1($conn) {
        // lógica para atualizar colocação e pontuação da Prova 1
    }

    function inserirProva2($conn, $carro_id, $tempo, $penalidade) {
        $stmt = $conn->prepare("INSERT INTO prova_2 (carro_id, tempo, penalidade) VALUES (:carro_id, :tempo, :penalidade)");
        $stmt->execute(['carro_id' => $carro_id, 'tempo' => $tempo, 'penalidade' => $penalidade]);
    }

    function obterResultadosProva2($conn) {
        $stmt = $conn->query("SELECT carros.numero, prova_2.tempo, prova_2.penalidade, prova_2.tempo_total, prova_2.colocacao, prova_2.pontuacao
                            FROM prova_2
                            JOIN carros ON prova_2.carro_id = carros.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function atualizarColocacaoPontuacaoProva2($conn) {
        // lógica para atualizar colocação e pontuação da Prova 2
    }

    function obterResultadosGerais($conn) {
        $stmt = $conn->query("SELECT carros.numero, carros.equipe, (SUM(prova_1.pontuacao) + SUM(prova_2.pontuacao)) as pontuacao_total
                            FROM carros
                            LEFT JOIN prova_1 ON carros.id = prova_1.carro_id
                            LEFT JOIN prova_2 ON carros.id = prova_2.carro_id
                            GROUP BY carros.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>
