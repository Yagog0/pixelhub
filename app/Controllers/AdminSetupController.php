<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database as DB;

/** Ativação por convite local: cadastrar um e-mail comum nunca concede privilégios. */
final class AdminSetupController
{
    private function invitation(): ?array
    {
        return DB::one('SELECT * FROM admin_invitations WHERE token_hash=? AND expires_at>? AND used_at IS NULL',[$_SESSION['admin_invite']??'',time()]);
    }
    public function show(): void
    {
        if (isset($_GET['token'])) {
            $token=$_GET['token'];
            $_SESSION['admin_invite']=is_string($token) && preg_match('/^[a-f0-9]{64}$/',$token) ? hash('sha256',$token) : '';
            // Retira a chave da URL antes de carregar a página e seus recursos.
            redirect('admin-setup');
        }
        $invitation=$this->invitation();
        if(!$invitation) { http_response_code(403); render('message',['title'=>'Convite indisponível','message'=>'Este convite já foi usado ou expirou. Peça ao responsável pela instalação um novo link de ativação.']); return; }
        render('admin/setup',['title'=>'Ative seu acesso administrativo','invitation'=>$invitation]);
    }
    public function activate(): void
    {
        $name=$_POST['name']??''; $password=$_POST['password']??'';
        if(!is_string($name)||mb_strlen(trim($name))<2||mb_strlen($name)>60||!is_string($password)||strlen($password)<10||strlen($password)>200||$password!==($_POST['confirm_password']??null)) {
            flash('Informe seu nome e duas senhas iguais, com pelo menos 10 caracteres.','error'); redirect('admin-setup');
        }
        // Bloqueio de escrita impede que duas requisições reutilizem o convite.
        DB::run('BEGIN IMMEDIATE');
        try {
            $invitation=$this->invitation();
            if(!$invitation) throw new \RuntimeException('Convite inválido, expirado ou já utilizado.');
            if(DB::one('SELECT id FROM users WHERE email=?',[$invitation['email']])) throw new \RuntimeException('Essa conta já foi cadastrada. Entre com sua senha e peça a promoção pelo terminal; este convite não altera senhas existentes.');
            DB::run("INSERT INTO users(name,email,password,role) VALUES (?,?,?,'admin')",[trim($name),$invitation['email'],password_hash($password,PASSWORD_DEFAULT)]);
            $id=DB::id();
            DB::run('UPDATE admin_invitations SET used_at=CURRENT_TIMESTAMP WHERE email=?',[$invitation['email']]);
            DB::run('INSERT INTO audit(actor_id,action) VALUES (?,?)',[$id,'Conta administrativa ativada por convite do responsável']);
            DB::run('COMMIT');
        } catch (\Throwable $error) {
            DB::run('ROLLBACK');
            flash($error instanceof \PDOException ? 'Não foi possível ativar. Tente novamente.' : $error->getMessage(),'error');
            redirect('admin-setup');
        }
        session_regenerate_id(true); $_SESSION=['user_id'=>$id];
        flash('Seu acesso administrativo está pronto. Bem-vindo à central de controle!'); redirect('admin');
    }
}
