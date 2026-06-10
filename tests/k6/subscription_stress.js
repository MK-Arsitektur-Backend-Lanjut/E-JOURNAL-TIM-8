import http from 'k6/http';
import { check, sleep } from 'k6';

// Konfigurasi skenario pengujian beban (Load/Stress Testing)
export const options = {
    scenarios: {
        // Skenario 1: Memeriksa akses langganan & download jurnal (Read-Heavy)
        read_access_stress: {
            executor: 'ramping-arrival-rate',
            startRate: 10,
            timeUnit: '1s',
            preAllocatedVUs: 50,
            maxVUs: 200,
            stages: [
                { duration: '1m', target: 50 },  // Naik ke 50 requests/detik selama 1 menit
                { duration: '2m', target: 50 },  // Bertahan di 50 requests/detik selama 2 menit
                { duration: '1m', target: 150 }, // Naik ke 150 requests/detik (Stress test!)
                { duration: '2m', target: 150 }, // Bertahan di 150 requests/detik selama 2 menit
                { duration: '1m', target: 0 },   // Turun ke 0
            ],
            exec: 'testReadAccess',
        },
        // Skenario 2: Transaksi subscribe & perpanjang langganan (Write-Heavy)
        write_subscribe_stress: {
            executor: 'ramping-arrival-rate',
            startRate: 2,
            timeUnit: '1s',
            preAllocatedVUs: 10,
            maxVUs: 50,
            stages: [
                { duration: '1m', target: 10 },  // Naik ke 10 requests/detik selama 1 menit
                { duration: '3m', target: 10 },  // Bertahan di 10 requests/detik selama 3 menit
                { duration: '1m', target: 0 },   // Turun ke 0
            ],
            exec: 'testWriteSubscribe',
        }
    },
    thresholds: {
        // Toleransi kegagalan request < 1%
        http_req_failed: ['rate<0.01'],
        // 95% request harus selesai di bawah 200ms
        http_req_duration: ['p(95)<200'],
    },
};

// URL Base Target (sesuaikan dengan host Docker/Local Anda)
const BASE_URL = __ENV.TARGET_URL || 'http://localhost:8000';

// Fungsi bantu untuk mendapatkan token autentikasi (Sanctum)
function getAuthToken(email) {
    const payload = JSON.stringify({
        email: email,
        password: 'password', // Password default dari factory DatabaseSeeder
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    const res = http.post(`${BASE_URL}/api/login`, payload, params);
    
    if (res.status === 200) {
        return res.json().token;
    }
    
    return null;
}

// Setup yang berjalan SATU KALI di awal pengujian untuk mengambil token autentikasi
export function setup() {
    const activeToken = getAuthToken('active@example.com');
    const writeToken = getAuthToken('test@example.com');
    
    if (!activeToken || !writeToken) {
        throw new Error('Gagal melakukan setup login awal. Pastikan database sudah di-seed dan container berjalan.');
    }

    return {
        activeToken: activeToken,
        writeToken: writeToken,
    };
}

// 1. Skenario Pengujian Read-Heavy (Akses Download & Jurnal)
export function testReadAccess(data) {
    const token = data.activeToken;
    
    const params = {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
    };

    // Langkah A: Cek Akses Download (Hits Cache Valid/Active)
    const accessRes = http.get(`${BASE_URL}/api/membership/download-access`, params);
    check(accessRes, {
        'GET download-access status is 200': (r) => r.status === 200,
        'GET download-access allowed is true': (r) => r.json().allowed === true,
    });

    sleep(0.5); // Jeda simulasi user membaca

    // Langkah B: Download Jurnal (Melewati Middleware subscription.access)
    const downloadRes = http.get(`${BASE_URL}/api/journals/download`, params);
    check(downloadRes, {
        'GET journals/download status is 200': (r) => r.status === 200,
        'GET journals/download contains file': (r) => r.json().file !== undefined,
    });

    sleep(1);
}

// 2. Skenario Pengujian Write-Heavy (Membuat & Memperpanjang Langganan)
export function testWriteSubscribe(data) {
    const token = data.writeToken;

    const params = {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    const plans = ['monthly', 'yearly'];
    const randomPlan = plans[Math.floor(Math.random() * plans.length)];

    const payload = JSON.stringify({
        plan: randomPlan,
    });

    // Jalankan transaksi Subscribe (Menulis DB & Invalidate Cache)
    const subscribeRes = http.post(`${BASE_URL}/api/membership/subscribe`, payload, params);
    
    check(subscribeRes, {
        'POST subscribe status is 200, 201 or 409': (r) => r.status === 200 || r.status === 201 || r.status === 409,
        'POST subscribe status is not 500': (r) => r.status !== 500,
    });

    sleep(2);
}
