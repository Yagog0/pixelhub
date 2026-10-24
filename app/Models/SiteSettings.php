<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database as DB;

/** Valores padrão mantêm a home preenchida antes da primeira intervenção editorial. */
final class SiteSettings
{
    public const COLLECTIONS = ['Para jogar em uma tarde', 'Feitos de pixels', 'Novos horizontes'];
    public const ARTS = ['hero', 'orbit', 'garden', 'circuit', 'valley', 'fire', 'sea'];

    public static function defaults(): array
    {
        return [
            'heading' => 'Seu próximo jogo favorito',
            'heading_line2' => 'está por aqui',
            'subtitle' => 'Descubra jogos independentes e histórias que merecem ser jogadas.',
            'section_title' => 'Pequenas joias, grandes descobertas',
            'home_count' => 4,
            'automatic_order' => 'new',
            'show_demos' => 1,
            'default_feature' => 0,
            'features' => [
                ['art'=>'hero','title'=>['Nem todo universo','precisa ser gigante.'],'description'=>['Histórias para viver em uma tarde.','Mundos para lembrar por muito mais tempo.'],'collection'=>'Para jogar em uma tarde','kicker'=>'PEQUENOS JOGOS, GRANDES VIAGENS'],
                ['art'=>'circuit','title'=>['Pixels de ontem.','Ideias de amanhã.'],'description'=>['Uma viagem ao futuro dos clássicos.','Conheça o homebrew original.'],'collection'=>'Feitos de pixels','kicker'=>'NOSTALGIA ENCONTRA ORIGINALIDADE'],
                ['art'=>'valley','title'=>['O melhor caminho','é o que você descobre.'],'description'=>['Pegue a curiosidade e siga em frente.','Um novo horizonte espera por você.'],'collection'=>'Novos horizontes','kicker'=>'PARA QUEM NASCEU PARA EXPLORAR'],
            ],
        ];
    }

    public static function get(): array
    {
        $row = DB::one("SELECT value FROM site_settings WHERE key='appearance'");
        $saved = $row ? json_decode($row['value'], true) : [];
        return array_replace(self::defaults(), is_array($saved) ? $saved : []);
    }
}
