const express = require('express');
const fetch   = require('node-fetch');

const app  = express();
const PORT = 3000;

const PHP_URL    = process.env.PHP_URL    || 'http://php-service:80';
const PYTHON_URL = process.env.PYTHON_URL || 'http://python-service:5000';

app.use(express.json());

// ── Proxy semua /mahasiswa/* → PHP Service ──────────
async function proxyToPhp(req, res) {
  const url  = `${PHP_URL}${req.url}`;
  console.log(`Proxy ke: ${url}`);  // tambah ini
  const opts = {
    method : req.method,
    headers: { 'Content-Type': 'application/json' },
  };
  if (['POST', 'PUT'].includes(req.method)) {
    opts.body = JSON.stringify(req.body);
  }
  try {
    const response = await fetch(url, opts);
    console.log(`PHP response status: ${response.status}`);  // tambah ini
    const data     = await response.json();
    res.status(response.status).json(data);
  } catch (err) {
    console.error(`Error: ${err.message}`);  // ubah ini
    res.status(500).json({ error: 'PHP service tidak dapat dijangkau', detail: err.message });
  }
}

app.all('/mahasiswa',    proxyToPhp);
app.all('/mahasiswa/:id', proxyToPhp);

// ── Proxy /status → Python Service ──────────────────
app.get('/status', async (req, res) => {
  try {
    const response = await fetch(`${PYTHON_URL}/status`);
    const data     = await response.json();
    res.json(data);
  } catch (err) {
    res.status(500).json({ error: 'Python service tidak dapat dijangkau' });
  }
});

// ── Root ─────────────────────────────────────────────
app.get('/', (req, res) => {
  res.json({
    message : 'API Gateway aktif',
    routes  : {
      crud  : '/mahasiswa',
      status: '/status'
    }
  });
});

app.listen(PORT, () => console.log(`Gateway berjalan di http://localhost:${PORT}`));