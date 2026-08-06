<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reprocessando Importação - VOKTAR</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, system-ui, sans-serif; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#07090d; color:#f8fafc; }
        .card { width:min(720px,calc(100% - 32px)); padding:32px; border:1px solid #2a2f39; border-radius:18px; background:#15181e; }
        h1 { margin:0 0 8px; font-size:28px; }
        .subtitle { color:#a7afbd; margin:0 0 28px; }
        .progress { height:18px; border-radius:999px; background:#252a33; overflow:hidden; margin:18px 0 12px; }
        .bar { height:100%; width:0; border-radius:inherit; background:linear-gradient(90deg,#f59e0b,#f97316); transition:width .25s ease; }
        .grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:20px; }
        .metric { padding:14px; border-radius:12px; background:#20242c; }
        .metric strong { display:block; font-size:22px; margin-top:5px; }
        .status { margin-top:22px; padding:14px; border-radius:12px; background:#20242c; }
        .error { color:#fca5a5; }
        a { display:inline-block; margin-top:22px; border-radius:10px; padding:12px 18px; background:#16a34a; color:white; font-weight:700; text-decoration:none; }
        .hidden { display:none; }
    </style>
</head>
<body>
<div class="card">
    <h1>Reprocessando Importação</h1>
    <p class="subtitle">
        Período {{ $import->start_date->format('d/m/Y') }} a {{ $import->end_date->format('d/m/Y') }}.
        Lotes reduzidos para estabilidade.
    </p>

    <div class="progress"><div id="bar" class="bar"></div></div>
    <div>
        <strong id="percentage">0%</strong>
        <span id="pages" style="float:right;color:#a7afbd">Preparando...</span>
    </div>

    <div class="grid">
        <div class="metric">Total API<strong id="total">0</strong></div>
        <div class="metric">Marcações<strong id="imported">0</strong></div>
        <div class="metric">Ignorados<strong id="ignored">0</strong></div>
    </div>

    <div id="status" class="status">Iniciando reprocessamento.</div>
    <a id="back" class="hidden" href="{{ $backUrl }}">Voltar ao Histórico</a>
</div>

<script>
const processUrl = @json($processUrl);
const statusUrl = @json($statusUrl);

const bar = document.getElementById('bar');
const percentage = document.getElementById('percentage');
const pages = document.getElementById('pages');
const total = document.getElementById('total');
const imported = document.getElementById('imported');
const ignored = document.getElementById('ignored');
const statusBox = document.getElementById('status');
const back = document.getElementById('back');

let running = false;
let retryCount = 0;
const maxRetries = 3;

function render(data) {
    const value = Number(data.percentage || 0);
    bar.style.width = `${value}%`;
    percentage.textContent = `${value}%`;
    pages.textContent = `${data.processed_pages || 0} de ${data.total_pages || 1} páginas`;
    total.textContent = data.total_records || 0;
    imported.textContent = data.imported_records || 0;
    ignored.textContent = data.ignored_records || 0;
    statusBox.textContent = data.message || 'Processando...';
    statusBox.classList.toggle('error', data.status === 'failed');

    if (data.status === 'completed' || data.status === 'failed') {
        back.classList.remove('hidden');
    }
}

async function getJson(url) {
    const response = await fetch(url, {
        headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
        credentials: 'same-origin',
    });

    if (!response.ok) throw new Error(`Erro HTTP ${response.status}`);
    return response.json();
}

async function processNext() {
    if (running) return;
    running = true;

    try {
        const data = await getJson(processUrl);
        retryCount = 0;
        render(data);

        if (data.status === 'processing') {
            running = false;
            setTimeout(processNext, 500);
            return;
        }
    } catch (error) {
        retryCount++;
        running = false;

        if (retryCount >= maxRetries) {
            statusBox.textContent =
                `A página falhou ${maxRetries} vezes (${error.message}). Volte ao histórico e inicie um novo reprocessamento.`;
            statusBox.classList.add('error');
            back.classList.remove('hidden');
            return;
        }

        statusBox.textContent =
            `Falha temporária: ${error.message}. Nova tentativa ${retryCount} de ${maxRetries} em 5 segundos...`;
        statusBox.classList.add('error');
        setTimeout(processNext, 5000);
        return;
    }

    running = false;
}

(async () => {
    try {
        const data = await getJson(statusUrl);
        render(data);
        if (data.status === 'processing') processNext();
    } catch (error) {
        statusBox.textContent = `Não foi possível iniciar: ${error.message}`;
        statusBox.classList.add('error');
        back.classList.remove('hidden');
    }
})();
</script>
</body>
</html>
