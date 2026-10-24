<?php
// Seed idempotente para instalações já iniciadas: não apaga dados do usuário.
use App\Core\Database;
foreach (
    [
        [null, 'first-world', 'Primeiro universo', 'Adicione seu primeiro jogo à biblioteca.'],
        [null, 'collector', 'Colecionador de mundos', 'Guarde três jogos diferentes na biblioteca.'],
        [null, 'favorite', 'É amor pelo primeiro pixel', 'Marque um jogo como favorito.'],
        [null, 'creator', 'Do papel para o mundo', 'Envie seu primeiro projeto para revisão.'],
        [1, 'first-flight', 'Decolagem autorizada', 'Comece uma aventura em Órbita de Papel.'],
        [1, 'five-stars', 'Um punhado de estrelas', 'Recolha cinco estrelas em uma partida.'],
        [1, 'fifteen-stars', 'Cartógrafo do infinito', 'Recolha quinze estrelas em uma partida.'],
    ]
    as $definition
) {
    Database::run(
        'INSERT OR IGNORE INTO achievements(project_id,key,title,description) VALUES (?,?,?,?)',
        $definition,
    );
}
