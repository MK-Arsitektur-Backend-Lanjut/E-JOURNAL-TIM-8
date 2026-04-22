<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\WebSubscribeRequest;
use App\Models\Subscription;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PageController — Serve halaman Blade (web, bukan API).
 *
 * Tanggung jawab:
 * - Ambil data dari Service/Repository
 * - Pass data ke View (termasuk computed data agar view bebas logika)
 * - Handle redirect dengan session flash
 *
 * Tidak ada business logic, tidak ada query DB langsung, tidak ada guard manual.
 */
class PageController extends Controller
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $repository,
        private readonly SubscriptionService $service
    ) {}

    // ── Public Pages ──────────────────────────────────────────────────────

    /**
     * GET / — Landing Page
     */
    public function landing(): View
    {
        return view('pages.landing');
    }

    /**
     * GET /plans — Halaman Paket Berlangganan
     */
    public function plans(): View
    {
        return view('pages.plans');
    }

    // ── Auth ──────────────────────────────────────────────────────────────

    /**
     * GET /login — Tampilkan form login.
     * Redirect ke dashboard jika sudah login.
     */
    public function loginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.login');
    }

    /**
     * POST /login — Proses autentikasi.
     * Validasi ditangani LoginRequest (Form Request).
     */
    public function loginPost(LoginRequest $request): RedirectResponse
    {
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('login_error', 'Email atau password salah. Silakan coba lagi.');
    }

    /**
     * POST /logout — Hapus sesi dan redirect ke landing.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')
            ->with('success', 'Anda telah berhasil keluar.');
    }

    // ── Protected Pages ───────────────────────────────────────────────────

    /**
     * GET /dashboard — Dashboard user.
     * Data computed di sini agar view bebas dari logika bisnis.
     */
    public function dashboard(): View
    {
        $userId = Auth::id();

        $activeSubscription = $this->repository->findActiveByUser($userId);
        $subscriptions      = $this->repository->getAllByUser($userId);

        // Hitung persentase progress bar di controller, bukan di view
        $progressPct = 0;
        if ($activeSubscription) {
            if ($activeSubscription->is_lifetime) {
                $progressPct = 100;
            } else {
                $planDuration = config("plans.durations.{$activeSubscription->plan}", 30);
                $progressPct  = max(0, min(100, ($activeSubscription->remaining_days / $planDuration) * 100));
            }
        }

        return view('dashboard.index', [
            'activeSubscription' => $activeSubscription,
            'subscriptions'      => $subscriptions,
            'subscriptionCount'  => $subscriptions->count(),
            'progressPct'        => round($progressPct),
        ]);
    }

    /**
     * POST /subscribe — Proses berlangganan dari form web.
     * Validasi ditangani WebSubscribeRequest (Form Request).
     */
    public function subscribePost(WebSubscribeRequest $request): RedirectResponse
    {
        $result = $this->service->subscribe(
            Auth::user(),
            $request->validated('plan')
        );

        return match ($result['status']) {
            'created'  => redirect()->route('dashboard')
                ->with('success', $result['message'] . ' Cek email Anda untuk konfirmasi.'),

            'extended' => redirect()->route('dashboard')
                ->with('success', $result['message']),

            'conflict' => redirect()->route('plans')
                ->with('error', $result['message']),

            default    => redirect()->route('plans'),
        };
    }

    /**
     * PATCH /subscription/{subscription}/cancel — Batalkan langganan.
     *
     * Menggunakan Route Model Binding untuk inject Subscription otomatis.
     * Policy 'cancel' memastikan hanya pemilik yang bisa membatalkan.
     */
    public function cancelSubscription(Subscription $subscription): RedirectResponse
    {
        $this->authorize('cancel', $subscription);

        $result = $this->service->cancel($subscription);

        return redirect()->route('dashboard')->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
