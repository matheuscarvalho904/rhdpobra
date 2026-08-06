<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processando Fechamento e Folha - VOKTAR</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, system-ui, sans-serif; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#07090d; color:#f8fafc; }
        .card { width:min(760px,calc(100% - 32px)); padding:32px; border:1px solid #2a2f39; border-radius:18px; background:#15181e; }
        h1 { margin:0 0 8px; font-size:28px; }
        .subtitle { color:#a7afbd; margin-bottom:28px; }
        .progress { height:18px; border-radius:999px; background:#252a33; overflow:hidden; margin:18px 0 12px; }
        .bar { height:100%; width:0; background:linear-gradient(90deg,#f59e0b,#f97316); transition:width .25s ease; }
        .stage { padding:16px; margin-top:22px; border-radius:12px; background:#20242c; }
        .metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:18px; }
        .metric { padding:14px; border-radius:12px; background:#20242c; }
        .metric strong { display:block; font-size:22px; margin-top:5px; }
        .error { color:#fca5a5; }
        .actions { display:flex; gap:12px; margin-top:22px; flex-wrap:wrap; }
        a, button { border:0; border-radius:10px; padding:12px 18px; font-weight:700; text-decoration:none; cursor:pointer; }
        .success { background:#16a34a; color:white; }
        .warning { background:#f59e0b; color:#111827; }
        .hidden { display:none; }
    </style>
</head>
<body>
<div class="card">
    <h1>Fechamento, Eventos e Folha</h1>
    <div class="subtitle">
        {{ $closing->name }} — {{ $closing->start_date->format('d/m/Y') }}
        a {{ $closing->end_date->format('d/m/Y') }}
    </div>

    <div class="progress"><div id="bar" class="bar"></div></div>
    <div>
        <strong id="percentage">0%</strong>
        <span id="stageLabel" style="float:right;color:#a7afbd">Preparando...</span>
    </div>

    <div class="metrics">
        <div class="metric">Eventos<strong id="events">0</strong></div>
        <div class="metric">Folhas concluídas<strong id="runs">0</strong></div>
        <div class="metric">Total de folhas<strong id="totalRuns">0</strong></div>
    </div>

    <div id="status" class="stage">Iniciando fluxo.</div>

    <div class="actions">
        <a id="back" class="success hidden" href="{{ $backUrl }}">Voltar aos Fechamentos</a>
        <button id="restart" class="warning hidden" type="button">Reiniciar processamento</button>
    </div>
</div>

<script>
const processUrl = @json($processUrl);
const statusUrl = @json($statusUrl);
const restartUrl = @json($restartUrl);

const bar = document.getElementById('bar');
const percentage = document.getElementById('percentage');
const stageLabel = document.getElementById('stageLabel');
const statusBox = document.getElementById('status');
const events = document.getElementById('events');
const runs = document.getElementById('runs');
const totalRuns = document.getElementById('totalRuns');
const back = document.getElementById('back');
const restart = document.getElementById('restart');

let running = false;
let retries = 0;
const maxRetries = 4;

function render(data) {
    const value = Number(data.percentage || 0);
    bar.style.width = `${value}%`;
    percentage.textContent = `${value}%`;
    stageLabel.textContent = data.stage_label || 'Processando';
    statusBox.textContent = data.message || 'Processando...';
    statusBox.classList.toggle('error', data.status === 'failed');

    events.textContent = data.events_count || 0;
    runs.textContent = data.processed_payroll_runs || 0;
    totalRuns.textContent = data.total_payroll_runs || 0;

    if (data.status === 'completed') {
        back.classList.remove('hidden');
        restart.classList.add('hidden');
    }

    if (data.status === 'failed') {
        back.classList.remove('hidden');
        restart.classList.remove('hidden');
    }
}

async function requestJson(url) {
    const response = await fetch(url, {
        headers: {
            'Accept':'application/json',
            'X-Requested-With':'XMLHttpRequest',
        },
        credentials:'same-origin',
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        throw new Error(data?.message || `Erro HTTP ${response.status}`);
    }

    return data;
}

async function processNext() {
    if (running) return;
    running = true;

    try {
        const data = await requestJson(processUrl);
        retries = 0;
        render(data);
        running = false;

        if (data.status === 'processing') {
            setTimeout(processNext, 450);
        }
    } catch (error) {
        retries++;
        running = false;

        if (retries >= maxRetries) {
            statusBox.textContent =
                `O servidor falhou ${maxRetries} vezes: ${error.message}. Use Reiniciar processamento.`;
            statusBox.classList.add('error');
            restart.classList.remove('hidden');
            back.classList.remove('hidden');
            return;
        }

        statusBox.textContent =
            `Falha temporária: ${error.message}. Tentativa ${retries} de ${maxRetries} em 5 segundos.`;
        statusBox.classList.add('error');
        setTimeout(processNext, 5000);
    }
}

restart.addEventListener('click', async () => {
    restart.classList.add('hidden');
    back.classList.add('hidden');
    statusBox.classList.remove('error');

    try {
        const data = await requestJson(restartUrl);
        render(data);
        processNext();
    } catch (error) {
        statusBox.textContent = `Não foi possível reiniciar: ${error.message}`;
        statusBox.classList.add('error');
        restart.classList.remove('hidden');
    }
});

(async () => {
    try {
        const data = await requestJson(statusUrl);
        render(data);

        if (data.status === 'processing') {
            processNext();
        }
    } catch (error) {
        statusBox.textContent = `Não foi possível iniciar: ${error.message}`;
        statusBox.classList.add('error');
        restart.classList.remove('hidden');
        back.classList.remove('hidden');
    }
})();
</script>
</body>
</html>
