@extends('layouts.app')

@section('title', 'Masuk — E-Journal')
@section('description', 'Masuk ke akun E-Journal Anda untuk mengakses jurnal ilmiah.')

@section('content')

<section style="min-height:calc(100vh - 64px);display:flex;align-items:center;justify-content:center;padding:2rem 1.5rem;position:relative;overflow:hidden;">

    {{-- Background glow --}}
    <div style="position:absolute;top:-100px;right:-100px;width:500px;height:500px;background:radial-gradient(circle,rgba(99,102,241,0.06) 0%,transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-100px;left:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(139,92,246,0.05) 0%,transparent 70%);pointer-events:none;"></div>

    <div class="container-xs animate-fade-up">

        {{-- Header --}}
        <div style="text-align:center;margin-bottom:2rem;">
            <a href="{{ route('landing') }}" style="display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;color:var(--color-text-primary);font-weight:700;font-size:1.1rem;margin-bottom:1.5rem;">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--color-brand),var(--color-accent));border-radius:8px;display:flex;align-items:center;justify-content:center;">📚</div>
                E-Journal
            </a>
            <h1 style="font-size:1.75rem;margin-bottom:0.5rem;">Selamat Datang Kembali</h1>
            <p style="color:var(--color-text-secondary);font-size:0.9rem;">Masuk untuk mengakses koleksi jurnal ilmiah Anda</p>
        </div>

        {{-- Login Card --}}
        <div class="card" style="padding:2rem;">

            {{-- Error dari session --}}
            @if(session('login_error'))
                <div class="alert alert-danger" style="margin-bottom:1.5rem;">
                    <span>✕</span>
                    <span>{{ session('login_error') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div style="display:flex;flex-direction:column;gap:1.25rem;">

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label" for="email">Alamat Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="nama@institusi.ac.id"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                        >
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <label class="form-label" for="password">Password</label>
                            <a href="#" style="font-size:0.8rem;color:var(--color-brand-light);">Lupa password?</a>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Masukkan password Anda"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <label style="display:flex;align-items:center;gap:0.625rem;cursor:pointer;">
                        <input type="checkbox" name="remember" id="remember" style="width:16px;height:16px;accent-color:var(--color-brand);">
                        <span style="font-size:0.875rem;color:var(--color-text-secondary);">Ingat saya selama 30 hari</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:0.85rem;">
                        Masuk ke Akun
                    </button>

                </div>
            </form>

        </div>

        {{-- Register CTA --}}
        <p style="text-align:center;font-size:0.875rem;color:var(--color-text-secondary);margin-top:1.5rem;">
            Belum punya akun?
            <a href="{{ route('plans') }}" style="color:var(--color-brand-light);font-weight:600;">Mulai berlangganan &rarr;</a>
        </p>

        {{-- Dev Test Accounts --}}
        @if(config('app.debug'))
        <div style="margin-top:1.5rem;padding:1rem;background:var(--color-bg-elevated);border:1px dashed var(--color-border-subtle);border-radius:var(--radius-md);">
            <p style="font-size:0.75rem;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.75rem;">🧪 Akun Testing (Dev Only)</p>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                @foreach([
                    ['active@example.com','Aktif ✓'],
                    ['expired@example.com','Expired ✗'],
                    ['nosub@example.com','Tanpa Langganan'],
                    ['lifetime@example.com','Lifetime ∞'],
                ] as $acc)
                <button
                    onclick="document.getElementById('email').value='{{ $acc[0] }}';document.getElementById('password').value='password';"
                    style="font-size:0.72rem;padding:0.3rem 0.6rem;background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-sm);color:var(--color-text-secondary);cursor:pointer;"
                >
                    {{ $acc[1] }}
                </button>
                @endforeach
            </div>
            <p style="font-size:0.7rem;color:var(--color-text-muted);margin-top:0.5rem;">Password semua akun: <code>password</code></p>
        </div>
        @endif

    </div>
</section>

@endsection
