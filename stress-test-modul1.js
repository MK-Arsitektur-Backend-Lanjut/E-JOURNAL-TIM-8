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
const BASE_URL = 'http://172.28.222.67:8000';
const CREDENTIALS = {
  email:    'test@example.com',
  password: 'password',
};

// ─── Skenario ─────────────────────────────────────────────────────
export const options = {
  stages: [
    { duration: '30s', target: 100  },
    { duration: '1m',  target: 500  },
    { duration: '2m',  target: 1000 },
    { duration: '2m',  target: 2000 },
    { duration: '1m',  target: 2000 },
    { duration: '30s', target: 0    },
  ],
  thresholds: {
    http_req_duration:        ['p(95)<10000'],
    error_rate:               ['rate<0.10'],
    list_documents_duration:  ['p(95)<10000'],
    detail_document_duration: ['p(95)<10000'],
  },
};

export function setup() {
  console.log('🔐 Login sekali untuk semua VU...');

  const res = http.post(
    `${BASE_URL}/api/login`,
    JSON.stringify(CREDENTIALS),
    { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } }
  );

  console.log('Status:', res.status);
  console.log('Body:', res.body);

  const ok = check(res, { 'setup: login berhasil': (r) => r.status === 200 });
  if (!ok) {
    console.error(`❌ Login gagal! Status: ${res.status}`);
    return { token: null };
  }

  const body  = JSON.parse(res.body);
  const token = body.token || null;

  console.log('✅ Token:', token);
  return { token };
}

// ─── Main Test ────────────────────────────────────────────────────
export default function (data) {
  if (!data.token) {
    errorRate.add(1);
    sleep(1);
    return;
  }

  const headers = {
    'Authorization': `Bearer ${data.token}`,
    'Accept':        'application/json',
    'Content-Type':  'application/json',
  };

  // 1. List Dokumen (tanpa filter)
  group('GET /api/v1/documents', () => {
    const res = http.get(`${BASE_URL}/api/v1/documents?page=1&per_page=15`, { headers });
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
    const res = http.get(`${BASE_URL}/api/v1/documents?year=2023&page=1`, { headers });
    listLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'filter year status 200': (r) => r.status === 200 });
  });

  sleep(0.2);

  // 3. Filter tag
  group('GET /api/v1/documents?tag=teknologi', () => {
    const res = http.get(`${BASE_URL}/api/v1/documents?tag=teknologi&page=1`, { headers });
    listLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'filter tag status 200': (r) => r.status === 200 });
  });

  sleep(0.2);

  // 4. Detail Dokumen (random ID 1–100)
  group('GET /api/v1/documents/{id}', () => {
    const id  = Math.floor(Math.random() * 100) + 1;
    const res = http.get(`${BASE_URL}/api/v1/documents/${id}`, { headers });
    detailLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 && res.status !== 404 ? 1 : 0);
    check(res, { 'detail status 200 or 404': (r) => r.status === 200 || r.status === 404 });
  });

  sleep(0.2);

  // 5. List Tags
  group('GET /api/v1/tags', () => {
    const res = http.get(`${BASE_URL}/api/v1/tags`, { headers });
    tagsLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'tags status 200': (r) => r.status === 200 });
  });

  sleep(0.2);

  // 6. List Authors
  group('GET /api/v1/authors', () => {
    const res = http.get(`${BASE_URL}/api/v1/authors`, { headers });
    authorsLatency.add(res.timings.duration);
    requestCount.add(1);
    errorRate.add(res.status !== 200 ? 1 : 0);
    check(res, { 'authors status 200': (r) => r.status === 200 });
  });

  sleep(0.5);
}