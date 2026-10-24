-- SQLite: chaves estrangeiras e unicidade preservam os vínculos entre contas e jogos.
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL, bio TEXT NOT NULL DEFAULT '', role TEXT NOT NULL DEFAULT 'member',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT, owner_id INTEGER REFERENCES users(id),
    slug TEXT NOT NULL UNIQUE, title TEXT NOT NULL, creator TEXT NOT NULL, summary TEXT NOT NULL,
    description TEXT NOT NULL, genre TEXT NOT NULL, type TEXT NOT NULL DEFAULT 'Jogo completo',
    platform TEXT NOT NULL DEFAULT 'Windows', language TEXT NOT NULL DEFAULT 'Português',
    duration TEXT NOT NULL DEFAULT 'Não informada', requirements TEXT NOT NULL DEFAULT '',
    controls TEXT NOT NULL DEFAULT 'Teclado e mouse', content_notice TEXT NOT NULL DEFAULT 'Sem avisos informados',
    license TEXT NOT NULL DEFAULT '', rights TEXT NOT NULL DEFAULT '',
    art TEXT NOT NULL DEFAULT 'orbit', collection TEXT NOT NULL DEFAULT 'Novos horizontes',
    version TEXT NOT NULL DEFAULT '1.0.0', changelog TEXT NOT NULL DEFAULT 'Primeira versão.',
    status TEXT NOT NULL DEFAULT 'draft', review_note TEXT NOT NULL DEFAULT '',
    build_path TEXT, build_size INTEGER NOT NULL DEFAULT 0, build_hash TEXT,
    demo INTEGER NOT NULL DEFAULT 0, playable INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS library (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_id INTEGER NOT NULL REFERENCES projects(id), favorite INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(user_id, project_id)
);
CREATE TABLE IF NOT EXISTS reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id),
    project_id INTEGER NOT NULL REFERENCES projects(id), reason TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'open', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS audit (
    id INTEGER PRIMARY KEY AUTOINCREMENT, actor_id INTEGER REFERENCES users(id),
    project_id INTEGER REFERENCES projects(id), action TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS login_attempts (key TEXT PRIMARY KEY, attempts INTEGER NOT NULL, started INTEGER NOT NULL);
CREATE INDEX IF NOT EXISTS projects_status ON projects(status, created_at);
-- Conquistas têm chaves estáveis; o mesmo jogador só desbloqueia cada uma uma vez.
CREATE TABLE IF NOT EXISTS achievements (
    id INTEGER PRIMARY KEY AUTOINCREMENT, project_id INTEGER REFERENCES projects(id),
    key TEXT NOT NULL, title TEXT NOT NULL, description TEXT NOT NULL,
    secret INTEGER NOT NULL DEFAULT 0, UNIQUE(project_id, key)
);
CREATE UNIQUE INDEX IF NOT EXISTS achievement_platform_key ON achievements(key) WHERE project_id IS NULL;
CREATE TABLE IF NOT EXISTS unlocks (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    achievement_id INTEGER NOT NULL REFERENCES achievements(id), source TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(user_id, achievement_id)
);
CREATE TABLE IF NOT EXISTS game_tokens (
    hash TEXT PRIMARY KEY, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_id INTEGER NOT NULL REFERENCES projects(id), expires_at INTEGER NOT NULL
);
-- Curadoria separada da build: editar a vitrine não altera a versão publicada.
CREATE TABLE IF NOT EXISTS project_display (
    project_id INTEGER PRIMARY KEY REFERENCES projects(id),
    hidden INTEGER NOT NULL DEFAULT 0 CHECK(hidden IN (0,1)),
    featured INTEGER NOT NULL DEFAULT 0 CHECK(featured IN (0,1)),
    position INTEGER NOT NULL DEFAULT 100 CHECK(position BETWEEN 0 AND 9999),
    collection TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS site_settings (key TEXT PRIMARY KEY, value TEXT NOT NULL);
-- Mede autorizações válidas de download; não afirma conclusão ou instalação.
CREATE TABLE IF NOT EXISTS download_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL REFERENCES projects(id),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS download_events_date ON download_events(created_at);
-- Convites administrativos só podem ser emitidos pelo terminal do responsável.
CREATE TABLE IF NOT EXISTS admin_invitations (
    email TEXT PRIMARY KEY, token_hash TEXT NOT NULL UNIQUE,
    expires_at INTEGER NOT NULL, used_at TEXT
);
