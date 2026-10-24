/* Minijogo original: loop independente da taxa de quadros, controles por teclado e toque. */
'use strict';
(() => {
    const canvas = document.querySelector('#orbit-game');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const scoreNode = document.querySelector('#game-score');
    const bestNode = document.querySelector('#game-best');
    const statusNode = document.querySelector('#game-status');
    const startButton = document.querySelector('#game-start');
    const pauseButton = document.querySelector('#game-pause');
    let best = 0;
    try {
        best = Number(localStorage.getItem('pixelhub-orbit-best')) || 0;
    } catch (_) {
        /* Armazenamento pode estar bloqueado no navegador. */
    }
    bestNode.textContent = best;
    let state = 'ready',
        score = 0,
        shipX = 480,
        objects = [],
        elapsed = 0,
        spawn = 0,
        last = 0;
    const keys = new Set();
    const submitted = new Set();
    async function unlock(key) {
        if (!canvas.dataset.csrf || submitted.has(key)) return;
        submitted.add(key);
        try {
            const response = await fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'game_achievement', csrf: canvas.dataset.csrf, key }),
            });
            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                submitted.delete(key);
                return;
            }
            const result = await response.json();
            if (result.unlocked) {
                const toast = document.getElementById('achievement-toast');
                toast.textContent = 'Conquista desbloqueada: ' + result.unlocked;
                toast.hidden = false;
                window.setTimeout(() => {
                    toast.hidden = true;
                }, 5000);
            }
        } catch (_) {
            submitted.delete(key); /* A partida continua mesmo sem conexão. */
        }
    }
    const stars = Array.from({ length: 65 }, (_, i) => ({
        x: (i * 137 + 43) % 960,
        y: (i * 79 + 16) % 540,
        size: i % 4 === 0 ? 3 : 1,
    }));
    function message(title, subtitle) {
        ctx.fillStyle = '#07120dde';
        ctx.fillRect(0, 0, 960, 540);
        ctx.textAlign = 'center';
        ctx.fillStyle = '#d1f77b';
        ctx.font = 'bold 40px Segoe UI';
        ctx.fillText(title, 480, 250);
        ctx.fillStyle = '#b5c7aa';
        ctx.font = '19px Segoe UI';
        ctx.fillText(subtitle, 480, 290);
    }
    function end() {
        state = 'over';
        keys.clear();
        if (score > best) {
            best = score;
            try {
                localStorage.setItem('pixelhub-orbit-best', String(best));
            } catch (_) {}
            bestNode.textContent = best;
        }
        statusNode.textContent = `Fim da viagem! Você recolheu ${score} estrelas.`;
        startButton.textContent = 'Jogar novamente';
        pauseButton.textContent = 'Pausar';
    }
    function pause() {
        if (!['running', 'paused'].includes(state)) return;
        state = state === 'running' ? 'paused' : 'running';
        keys.clear();
        pauseButton.textContent = state === 'paused' ? 'Continuar' : 'Pausar';
        statusNode.textContent =
            state === 'paused' ? 'Aventura pausada.' : 'Setas ou A/D para mover · espaço para pausar';
    }
    function draw(now) {
        const dt = Math.min((now - last) / 1000, 0.04);
        last = now;
        ctx.fillStyle = '#101e26';
        ctx.fillRect(0, 0, 960, 540);
        ctx.fillStyle = '#253242';
        ctx.beginPath();
        ctx.arc(770, 90, 110, 0, Math.PI * 2);
        ctx.fill();
        stars.forEach((star) => {
            if (state === 'running') star.y = (star.y + dt * 30) % 540;
            ctx.fillStyle = star.size > 1 ? '#87b69e' : '#415d65';
            ctx.fillRect(star.x, star.y, star.size, star.size);
        });
        if (state === 'running') {
            elapsed += dt;
            spawn -= dt;
            if (keys.has('left')) shipX -= dt * 420;
            if (keys.has('right')) shipX += dt * 420;
            shipX = Math.max(24, Math.min(936, shipX));
            if (spawn <= 0) {
                spawn = Math.max(0.26, 0.65 - elapsed / 200);
                objects.push({ x: 25 + Math.random() * 910, y: -30, star: Math.random() > 0.47 });
            }
            for (const item of objects) {
                item.y += dt * (140 + Math.min(elapsed * 3, 240));
                if (Math.abs(item.x - shipX) < 25 && Math.abs(item.y - 465) < 27) {
                    if (item.star) {
                        score++;
                        scoreNode.textContent = score;
                        item.y = 600;
                        if (score >= 5) unlock('five-stars');
                        if (score >= 15) unlock('fifteen-stars');
                    } else {
                        end();
                        break;
                    }
                }
            }
            objects = objects.filter((item) => item.y < 580);
        }
        objects.forEach((item) => {
            if (item.star) {
                ctx.fillStyle = '#d1f77b';
                ctx.fillRect(item.x - 4, item.y - 12, 8, 24);
                ctx.fillRect(item.x - 12, item.y - 4, 24, 8);
                ctx.fillStyle = '#f7ffd8';
                ctx.fillRect(item.x - 4, item.y - 4, 8, 8);
            } else {
                ctx.fillStyle = '#ac8275';
                ctx.fillRect(item.x - 14, item.y - 14, 28, 28);
                ctx.fillRect(item.x - 20, item.y - 6, 40, 16);
                ctx.fillStyle = '#6c5260';
                ctx.fillRect(item.x - 5, item.y - 10, 12, 9);
                ctx.fillRect(item.x - 12, item.y + 5, 9, 8);
            }
        });
        // Nave de papel feita de triângulos; nenhuma imagem externa é necessária.
        ctx.fillStyle = '#f3edd8';
        ctx.beginPath();
        ctx.moveTo(shipX, 437);
        ctx.lineTo(shipX - 24, 486);
        ctx.lineTo(shipX, 476);
        ctx.lineTo(shipX + 24, 486);
        ctx.closePath();
        ctx.fill();
        ctx.fillStyle = '#adba97';
        ctx.beginPath();
        ctx.moveTo(shipX, 437);
        ctx.lineTo(shipX, 476);
        ctx.lineTo(shipX + 24, 486);
        ctx.closePath();
        ctx.fill();
        if (state === 'ready') message('ÓRBITA DE PAPEL', 'Um pequeno passo. Uma imensidão de estrelas.');
        if (state === 'paused')
            message('Uma pausa entre as estrelas', 'Clique em Continuar para voltar à aventura.');
        if (state === 'over')
            message('Toda órbita é um recomeço', `${score} estrelas encontradas · recorde ${best}`);
        requestAnimationFrame(draw);
    }
    startButton.addEventListener('click', () => {
        state = 'running';
        score = 0;
        shipX = 480;
        objects = [];
        elapsed = 0;
        spawn = 0;
        keys.clear();
        scoreNode.textContent = '0';
        startButton.textContent = 'Recomeçar';
        pauseButton.textContent = 'Pausar';
        statusNode.textContent = 'Setas ou A/D para mover · espaço para pausar';
        canvas.focus();
        unlock('first-flight');
    });
    pauseButton.addEventListener('click', pause);
    document.addEventListener('keydown', (event) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (['ArrowLeft', 'ArrowRight', 'a', 'A', 'd', 'D'].includes(event.key)) {
            event.preventDefault();
            keys.add(['ArrowLeft', 'a', 'A'].includes(event.key) ? 'left' : 'right');
        }
        if (event.code === 'Space' && document.activeElement === canvas) {
            event.preventDefault();
            if (!event.repeat) pause();
        }
    });
    document.addEventListener('keyup', (event) => {
        if (['ArrowLeft', 'a', 'A'].includes(event.key)) keys.delete('left');
        if (['ArrowRight', 'd', 'D'].includes(event.key)) keys.delete('right');
    });
    for (const [id, direction] of [
        ['move-left', 'left'],
        ['move-right', 'right'],
    ]) {
        const button = document.getElementById(id);
        button.addEventListener('pointerdown', (event) => {
            button.setPointerCapture(event.pointerId);
            keys.add(direction);
        });
        ['pointerup', 'pointercancel', 'lostpointercapture'].forEach((name) =>
            button.addEventListener(name, () => keys.delete(direction)),
        );
    }
    window.addEventListener('blur', () => {
        keys.clear();
        if (state === 'running') pause();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && state === 'running') pause();
    });
    requestAnimationFrame(draw);
})();
