<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database as DB;
use App\Models\Project;
use App\Models\SiteSettings;
use RuntimeException;

/** Operação e curadoria da plataforma; não modifica os arquivos das builds. */
final class AdminController
{
    public const TABS = ['overview'=>'Visão geral', 'queue'=>'O que está chegando', 'catalog'=>'Vitrine e catálogo', 'appearance'=>'Página inicial', 'users'=>'Contas', 'reports'=>'Denúncias', 'history'=>'Histórico'];

    private function authorize(): ?array
    {
        if (!user()) {
            $_SESSION['after_login'] = 'admin';
            require_user();
        }
        $user = user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            render('message', ['title'=>'Acesso restrito', 'message'=>'Esta conta ainda não tem acesso administrativo. O responsável pela instalação pode liberar seu e-mail com o comando scripts/admin.php.']);
            return null;
        }
        return $user;
    }

    public function show(): void
    {
        if (!$this->authorize()) return;
        $tab = is_string($_GET['tab'] ?? null) && isset(self::TABS[$_GET['tab']]) ? $_GET['tab'] : 'overview';
        $query = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';
        $status = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
        $number = max(1, (int) ($_GET['n'] ?? 1));
        $stats = DB::one("SELECT
            (SELECT COUNT(*) FROM users) AS accounts,
            (SELECT COUNT(*) FROM projects WHERE status='pending') AS pending,
            (SELECT COUNT(*) FROM projects WHERE status='published') AS published,
            (SELECT COUNT(*) FROM projects WHERE demo=0) AS real_projects,
            (SELECT COUNT(*) FROM library) AS additions,
            (SELECT COUNT(*) FROM reports WHERE status='open') AS reports,
            (SELECT COUNT(*) FROM download_events) AS downloads,
            (SELECT COUNT(*) FROM unlocks) AS unlocks");

        // Uma consulta paginada por aba evita carregar todo o banco no painel.
        $rows = []; $total = 0; $latest = []; $activity = []; $ranking = [];
        $join = " FROM projects p LEFT JOIN users u ON u.id=p.owner_id LEFT JOIN project_display d ON d.project_id=p.id ";
        $columns = "p.*, u.email, COALESCE(d.hidden,0) AS hidden, COALESCE(d.featured,0) AS featured, COALESCE(d.position,100) AS position, COALESCE(NULLIF(d.collection,''),p.collection) AS display_collection";
        if ($tab === 'overview') {
            $latest = DB::all("SELECT $columns $join WHERE p.status='pending' ORDER BY p.id DESC LIMIT 5");
            $ranking = DB::all("SELECT p.id,p.title,p.art,COUNT(l.user_id) AS total FROM projects p JOIN library l ON l.project_id=p.id GROUP BY p.id ORDER BY total DESC,p.id DESC LIMIT 5");
            for ($i=6; $i>=0; $i--) {
                $day = gmdate('Y-m-d', time()-$i*86400);
                $activity[] = ['day'=>$day] + DB::one("SELECT (SELECT COUNT(*) FROM users WHERE date(created_at)=?) AS accounts, (SELECT COUNT(*) FROM library WHERE date(created_at)=?) AS additions, (SELECT COUNT(*) FROM download_events WHERE date(created_at)=?) AS downloads", [$day,$day,$day]);
            }
        } elseif ($tab === 'queue' || $tab === 'catalog') {
            $where = [$tab === 'queue' ? 'p.owner_id IS NOT NULL' : '1=1']; $params = [];
            if ($query !== '') { $where[]='(p.title LIKE ? OR p.creator LIKE ?)'; $params[]='%'.$query.'%'; $params[]='%'.$query.'%'; }
            if (in_array($status,['draft','pending','published','rejected','withdrawn'],true)) { $where[]='p.status=?'; $params[]=$status; }
            $where = ' WHERE '.implode(' AND ',$where);
            $total = (int) DB::one("SELECT COUNT(*) AS total $join $where", $params)['total'];
            $number = min($number,max(1,(int)ceil($total/15)));
            $offset = ($number-1)*15;
            $rows = DB::all("SELECT $columns $join $where ORDER BY CASE p.status WHEN 'pending' THEN 0 ELSE 1 END, p.id DESC LIMIT 15 OFFSET $offset",$params);
        } elseif ($tab === 'users') {
            $params=['%'.$query.'%','%'.$query.'%'];
            $total=(int)DB::one('SELECT COUNT(*) AS total FROM users WHERE name LIKE ? OR email LIKE ?',$params)['total'];
            $number=min($number,max(1,(int)ceil($total/15))); $offset=($number-1)*15;
            $rows=DB::all("SELECT u.id,u.name,u.email,u.role,u.created_at,(SELECT COUNT(*) FROM projects p WHERE p.owner_id=u.id) AS projects FROM users u WHERE name LIKE ? OR email LIKE ? ORDER BY u.id DESC LIMIT 15 OFFSET $offset",$params);
        } elseif ($tab === 'reports') {
            $condition=$status==='resolved'?"r.status='resolved'":($status==='all'?'1=1':"r.status='open'");
            $total=(int)DB::one("SELECT COUNT(*) AS total FROM reports r WHERE $condition")['total'];
            $number=min($number,max(1,(int)ceil($total/15))); $offset=($number-1)*15;
            $rows=DB::all("SELECT r.*,p.title,u.name FROM reports r JOIN projects p ON p.id=r.project_id JOIN users u ON u.id=r.user_id WHERE $condition ORDER BY r.id DESC LIMIT 15 OFFSET $offset");
        } elseif ($tab === 'history') {
            $total=(int)DB::one('SELECT COUNT(*) AS total FROM audit')['total'];
            $number=min($number,max(1,(int)ceil($total/30))); $offset=($number-1)*30;
            $rows=DB::all("SELECT a.*,u.name,p.title FROM audit a LEFT JOIN users u ON u.id=a.actor_id LEFT JOIN projects p ON p.id=a.project_id ORDER BY a.id DESC LIMIT 30 OFFSET $offset");
        }
        render('admin', compact('tab','query','status','number','stats','rows','total','latest','activity','ranking') + ['title'=>'Central de controle', 'settings'=>SiteSettings::get(), 'pages'=>max(1,(int)ceil($total/($tab==='history'?30:15)))]);
    }

    /** Recebe somente POSTs que já passaram pela validação CSRF do front controller. */
    public function action(string $action): void
    {
        $user = $this->authorize();
        if (!$user) return;
        $tab = in_array($action,['admin_appearance','admin_reset'],true) ? 'appearance' : 'catalog';
        try {
            if ($action === 'admin_appearance') {
                $settings = SiteSettings::defaults();
                foreach (['heading'=>70,'heading_line2'=>70,'subtitle'=>180,'section_title'=>90] as $key=>$max) $settings[$key]=$this->text($key,$max);
                $settings['home_count']=$this->choice('home_count',['4','8','12']);
                $settings['automatic_order']=$this->choice('automatic_order',['new','name']);
                $settings['show_demos']=isset($_POST['show_demos'])?1:0;
                $settings['default_feature']=(int)$this->choice('default_feature',['0','1','2']);
                foreach ($settings['features'] as $i=>&$feature) {
                    $feature['art']=$this->choice("feature_{$i}_art",SiteSettings::ARTS);
                    $feature['collection']=$this->choice("feature_{$i}_collection",SiteSettings::COLLECTIONS);
                    $feature['kicker']=$this->text("feature_{$i}_kicker",70);
                    foreach (['title'=>55,'description'=>90] as $field=>$max) for($line=0;$line<2;$line++) $feature[$field][$line]=$this->text("feature_{$i}_{$field}_{$line}",$max);
                }
                unset($feature);
                $this->mutate(function() use($settings,$user) {
                    DB::run("INSERT INTO site_settings(key,value) VALUES ('appearance',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",[json_encode($settings,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
                    $this->audit($user['id'],null,'Aparência da página inicial atualizada');
                });
                flash('Página inicial atualizada. As alterações já estão no site.');
            } elseif ($action === 'admin_reset') {
                $this->mutate(function() use($user) {
                    DB::run("DELETE FROM site_settings WHERE key='appearance'");
                    $this->audit($user['id'],null,'Aparência da página inicial restaurada ao padrão');
                });
                flash('Textos, banners e regras gerais restaurados. A curadoria dos jogos foi preservada.');
            } elseif ($action === 'admin_display') {
                $id=(int)($_POST['id']??0); if(!Project::find($id)) throw new RuntimeException('Projeto não encontrado.');
                $position=filter_var($_POST['position']??'',FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>9999]]);
                if($position===false) throw new RuntimeException('A prioridade precisa ser um número entre 0 e 9999.');
                $collection=$this->choice('collection',SiteSettings::COLLECTIONS);
                $hidden=isset($_POST['hidden'])?1:0; $featured=isset($_POST['featured'])?1:0;
                $this->mutate(function() use($id,$position,$collection,$hidden,$featured,$user) {
                    DB::run('INSERT INTO project_display(project_id,hidden,featured,position,collection) VALUES (?,?,?,?,?) ON CONFLICT(project_id) DO UPDATE SET hidden=excluded.hidden,featured=excluded.featured,position=excluded.position,collection=excluded.collection',[$id,$hidden,$featured,$position,$collection]);
                    $this->audit($user['id'],$id,"Vitrine: ".($hidden?'oculto':'visível').", destaque=$featured, prioridade=$position, coleção=$collection");
                });
                flash('Curadoria salva. Projetos aprovados refletem a mudança imediatamente.');
            } elseif ($action === 'admin_auto') {
                $id=(int)($_POST['id']??0); if(!Project::find($id)) throw new RuntimeException('Projeto não encontrado.');
                $this->mutate(function() use($id,$user) { DB::run('DELETE FROM project_display WHERE project_id=?',[$id]); $this->audit($user['id'],$id,'Curadoria do jogo devolvida à seleção automática'); });
                flash('Jogo devolvido à seleção automática. O status de revisão foi preservado.');
            } else throw new RuntimeException('Ação administrativa desconhecida.');
        } catch (\PDOException $error) {
            error_log($error->getMessage()); flash('Não foi possível salvar. Tente novamente.', 'error');
        } catch (RuntimeException $error) { flash($error->getMessage(),'error'); }
        redirect('admin',['tab'=>$tab]);
    }

    private function text(string $key,int $max): string
    {
        $text=$_POST[$key]??'';
        if(!is_string($text)||trim($text)===''||mb_strlen($text)>$max) throw new RuntimeException("Preencha o campo $key com até $max caracteres.");
        return trim($text);
    }
    private function choice(string $key,array $allowed): string
    {
        $value=$_POST[$key]??null;
        if(!is_string($value)||!in_array($value,$allowed,true)) throw new RuntimeException('Escolha uma opção válida para '.$key.'.');
        return $value;
    }
    private function audit(int $actor,?int $project,string $action): void { DB::run('INSERT INTO audit(actor_id,project_id,action) VALUES (?,?,?)',[$actor,$project,$action]); }
    private function mutate(callable $operation): void
    {
        // A configuração e o registro de quem a alterou são gravados juntos.
        DB::run('BEGIN IMMEDIATE');
        try { $operation(); DB::run('COMMIT'); }
        catch (\Throwable $error) { DB::run('ROLLBACK'); throw $error; }
    }
}
