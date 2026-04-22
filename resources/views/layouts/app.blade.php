<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Journal — Platform Jurnal Ilmiah Digital')</title>
    <meta name="description" content="@yield('description', 'Akses ribuan jurnal ilmiah terpercaya. Berlangganan untuk download tidak terbatas.')">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    {{-- ── NAVBAR ─────────────────────────────────────────────────────────── --}}
    <nav class="navbar">
        <a href="{{ route('landing') }}" class="navbar__brand">
            <div class="navbar__brand-icon">📚</div>
            E-Journal
        </a>

        <div class="navbar__links">
            <a href="{{ route('landing') }}" class="navbar__link {{ request()->routeIs('landing') ? 'navbar__link--active' : '' }}">Beranda</a>
            <a href="{{ route('plans') }}" class="navbar__link {{ request()->routeIs('plans') ? 'navbar__link--active' : '' }}">Paket Langganan</a>
            <a href="{{ route('landing') }}#features" class="navbar__link">Fitur</a>
            <a href="{{ route('landing') }}#about" class="navbar__link">Tentang</a>
        </div>

        <div class="navbar__actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Dashboard</a>
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                <a href="{{ route('plans') }}" class="btn btn-primary btn-sm">Mulai Sekarang</a>
            @endauth
        </div>
    </nav>

    {{-- ── MAIN CONTENT ────────────────────────────────────────────────────── --}}
    <main style="padding-top: 64px;">
        @if(session('success'))
            <div id="toast-success" style="position:fixed;top:80px;right:1.5rem;z-index:200;max-width:380px;transition:all 0.4s ease;">
                <div class="alert alert-success animate-fade-in" style="box-shadow:0 8px 24px rgba(0,0,0,0.3);">
                    <span style="flex-shrink:0;">✓</span>
                    <span>{{ session('success') }}</span>
                    <button onclick="dismissToast('toast-success')" style="margin-left:auto;background:none;border:none;color:inherit;cursor:pointer;padding:0;font-size:1rem;opacity:0.7;">✕</button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div id="toast-error" style="position:fixed;top:80px;right:1.5rem;z-index:200;max-width:380px;transition:all 0.4s ease;">
                <div class="alert alert-danger animate-fade-in" style="box-shadow:0 8px 24px rgba(0,0,0,0.3);">
                    <span style="flex-shrink:0;">✕</span>
                    <span>{{ session('error') }}</span>
                    <button onclick="dismissToast('toast-error')" style="margin-left:auto;background:none;border:none;color:inherit;cursor:pointer;padding:0;font-size:1rem;opacity:0.7;">✕</button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ── FOOTER ──────────────────────────────────────────────────────────── --}}
    <footer class="footer">
        <div class="container">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                <div>
                    <div class="footer__brand">📚 E-Journal</div>
                    <div class="footer__copy" style="margin-top:0.25rem;">Platform Jurnal Ilmiah Digital Indonesia</div>
                </div>
                <div style="display:flex;gap:1.5rem;">
                    <a href="{{ route('plans') }}" style="font-size:0.8rem;color:var(--color-text-muted);">Paket Langganan</a>
                    <a href="{{ route('login') }}" style="font-size:0.8rem;color:var(--color-text-muted);">Masuk</a>
                </div>
            </div>
            <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--color-border);">
                <p class="footer__copy">© {{ date('Y') }} E-Journal. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')

    {{-- ── TOAST AUTO-DISMISS ──────────────────────────────────────────────── --}}
    <script>
        /**
         * Dismiss toast notification dengan animasi slide + fade.
         * Dipanggil otomatis setelah 4 detik, atau manual lewat tombol ✕.
         */
        function dismissToast(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.opacity    = '0';
            el.style.transform  = 'translateX(20px)';
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            setTimeout(() => el.remove(), 400);
        }

        // Auto-dismiss semua toast setelah 4 detik
        document.addEventListener('DOMContentLoaded', function () {
            ['toast-success', 'toast-error'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    setTimeout(() => dismissToast(id), 4000);
                }
            });
        });
    </script>
</body>
</html>
