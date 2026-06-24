import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Rate, Counter } from 'k6/metrics';

// ─── Custom Metrics ───────────────────────────────────────────────
const listLatency    = new Trend('list_documents_duration');
const detailLatency  = new Trend('detail_document_duration');
const tagsLatency    = new Trend('list_tags_duration');
const authorsLatency = new Trend('list_authors_duration');
const errorRate      = new Rate('error_rate');
const requestCount   = new Counter('total_requests');

// ─── Config ───────────────────────────────────────────────────────
const PORTS    = ['8000', '8001', '8002', '8003'];
const BASE_URL = (vu) => `http://127.0.0.1:${PORTS[vu % PORTS.length]}`;

const CREDENTIALS = {
  email:    'test@example.com',
  password: 'password',
};

// ─── Skenario ─────────────────────────────────────────────────────
export const options = {
  stages: [
    { duration: '30s', target: 10  },
    { duration: '1m',  target: 50  },
    { duration: '2m',  target: 100 },
    { duration: '1m',  target: 100 },
    { duration: '30s', target: 0   },
  ],
  thresholds: {
    http_req_duration:        ['p(95)<35000'],
    error_rate:               ['rate<0.05'],    
    list_documents_duration:  ['p(95)<35000'],
    detail_document_duration: ['p(95)<35000'],
  },
};

// ─── Setup: Login SEKALI, token dibagikan ke semua VU ─────────────
export function setup() {
  console.log('🔐 Login sekali untuk semua VU...');

  // Login dari port 8000
  const res = http.post(
    'http://127.0.0.1:8000/api/login',
    JSON.stringify(CREDENTIALS),
    { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } }
  );

  const ok = check(res, { 'setup: login berhasil': (r) => r.status === 200 });
  if (!ok) {
    console.error(`❌ Login gagal! Status: ${res.status}, Body: ${res.body}`);
    return { token: null };
  }

  const body  = res.json();
  const token = body.token || body.data?.token || body.access_token || null;

  if (!token) {
    console.error('❌ Token tidak ditemukan di response:', JSON.stringify(body));
    return { token: null };
  }

  console.log('✅ Token berhasil didapat, siap digunakan semua VU');
  return { token };
}

// ─── Main Test ────────────────────────────────────────────────────
export default function (data) {
  if (!data.token) {
    errorRate.add(1);
    sleep(1);
    return;
  }

  const url     = BASE_URL(__VU);
  const headers = {
    'Authorization': `Bearer ${data.token}`,
    'Accept':        'application/json',
    'Content-Type':  'application/json',
  };

  // 1. List Dokumen (tanpa filter)
  group('GET /api/v1/documents', () => {
    const res = http.get(`${url}/api/v1/documents?page=1&per_page=15`, { headers });
    listLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, {
      'list status 200': (r) => r.status === 200,
      'list has data':   (r) => { try { return r.json('data') !== null; } catch { return false; } },
    });
  });

  sleep(0.2);

  // 2. Filter tahun
  group('GET /api/v1/documents?year=2023', () => {
    const res = http.get(`${url}/api/v1/documents?year=2023&page=1`, { headers });
    listLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'filter year status 200': (r) => r.status === 200 });
  });

  sleep(0.2);

  // 3. Filter tag
  group('GET /api/v1/documents?tag=teknologi', () => {
    const res = http.get(`${url}/api/v1/documents?tag=teknologi&page=1`, { headers });
    listLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'filter tag status 200': (r) => r.status === 200 });
  });

  sleep(0.2);

  // 4. Detail Dokumen (random ID 1–100)
  group('GET /api/v1/documents/{id}', () => {
    const id  = Math.floor(Math.random() * 100) + 1;
    const res = http.get(`${url}/api/v1/documents/${id}`, { headers });
    detailLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 && res.status !== 404 ? 1 : 0);
    check(res, { 'detail status 200 or 404': (r) => r.status === 200 || r.status === 404 });
  });

  sleep(0.2);

  // 5. List Tags
  group('GET /api/v1/tags', () => {
    const res = http.get(`${url}/api/v1/tags`, { headers });
    tagsLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'tags status 200': (r) => r.status === 200 });
  });

  sleep(0.2);

  // 6. List Authors
  group('GET /api/v1/authors', () => {
    const res = http.get(`${url}/api/v1/authors`, { headers });
    authorsLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'authors status 200': (r) => r.status === 200 });
  });

  sleep(0.5);
}