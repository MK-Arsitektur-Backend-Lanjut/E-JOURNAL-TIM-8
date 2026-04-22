@extends('layouts.app')

@section('title', 'E-Journal — Platform Jurnal Ilmiah Digital Indonesia')
@section('description', 'Akses ribuan jurnal ilmiah terpercaya dan terindeks. Berlangganan untuk download tidak terbatas.')

@section('content')

{{-- ── HERO SECTION ──────────────────────────────────────────────────────── --}}
<section style="position:relative;overflow:hidden;padding:6rem 1.5rem 5rem;">

    {{-- Glow Background --}}
    <div style="position:absolute;top:-200px;left:50%;transform:translateX(-50%);width:800px;height:800px;background:radial-gradient(circle,rgba(99,102,241,0.07) 0%,transparent 65%);pointer-events:none;"></div>

    <div class="container" style="text-align:center;position:relative;">

        {{-- Badge --}}
        <div class="animate-fade-up" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 0.9rem;border-radius:9999px;border:1px solid rgba(99,102,241,0.3);background:rgba(99,102,241,0.08);margin-bottom:1.75rem;">
            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;animation:pulse-glow 2s infinite;"></span>
            <span style="font-size:0.78rem;color:var(--color-brand-light);font-weight:600;letter-spacing:0.05em;">PLATFORM JURNAL RESMI TERAKREDITASI</span>
        </div>

        {{-- Headline --}}
        <h1 class="animate-fade-up animate-delay-1" style="font-size:clamp(2.5rem,6vw,4rem);font-weight:900;line-height:1.1;margin-bottom:1.25rem;letter-spacing:-0.03em;">
            Akses Ribuan<br>
            <span class="gradient-text">Jurnal Ilmiah</span><br>
            Dalam Satu Platform
        </h1>

        {{-- Subheadline --}}
        <p class="animate-fade-up animate-delay-2" style="font-size:1.1rem;color:var(--color-text-secondary);max-width:560px;margin:0 auto 2.5rem;line-height:1.7;">
            Dari riset hingga publikasi — dapatkan akses penuh ke koleksi jurnal terindeks nasional dan internasional. Mulai dari Rp 0 dengan paket trial.
        </p>

        {{-- CTA Buttons --}}
        <div class="animate-fade-up animate-delay-3" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-bottom:3rem;">
            <a href="{{ route('plans') }}" class="btn btn-primary btn-lg">
                Mulai Berlangganan &rarr;
            </a>
            <a href="#features" class="btn btn-secondary btn-lg">
                Pelajari Fitur
            </a>
        </div>

        {{-- Stats Row --}}
        <div class="animate-fade-up animate-delay-4" style="display:flex;gap:2rem;justify-content:center;flex-wrap:wrap;padding-top:2rem;border-top:1px solid var(--color-border);">
            @foreach([['10.000+','Jurnal Tersedia'],['5.200+','Peneliti Aktif'],['98%','Tingkat Kepuasan'],['24/7','Akses Online']] as $stat)
            <div style="text-align:center;">
                <div style="font-size:1.5rem;font-weight:800;color:var(--color-text-primary);">{{ $stat[0] }}</div>
                <div style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.2rem;">{{ $stat[1] }}</div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ── FEATURES SECTION ──────────────────────────────────────────────────── --}}
<section id="features" class="section" style="background:var(--color-bg-surface);">
    <div class="container">

        <div style="text-align:center;margin-bottom:3.5rem;">
            <div class="badge badge-brand" style="margin-bottom:1rem;">Fitur Unggulan</div>
            <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);margin-bottom:1rem;">Dirancang untuk Peneliti Modern</h2>
            <p style="color:var(--color-text-secondary);max-width:480px;margin:0 auto;">Semua yang Anda butuhkan untuk mengakses, mengelola, dan berbagi pengetahuan ilmiah.</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;">
            @foreach([
                ['🔐','Akses Terproteksi','Unduh jurnal hanya untuk pelanggan aktif. Sistem otorisasi berlapis menjamin keamanan konten.'],
                ['📋','Langganan Fleksibel','Pilih paket yang sesuai: Trial 7 hari, Monthly, Yearly, hingga Lifetime. Bebas sesuaikan anggaran.'],
                ['⚡','Performa Tinggi','Teknologi Redis Cache memastikan pengecekan akses berjalan cepat meski ribuan pengguna aktif bersamaan.'],
                ['📧','Pengingat Otomatis','Dapatkan notifikasi email H-7 dan H-3 sebelum langganan berakhir agar tidak terputus aksesnya.'],
                ['📊','Dashboard Personal','Pantau status langganan, sisa hari, dan riwayat transaksi dari satu halaman yang rapi.'],
                ['🔄','Perpanjang Mudah','Perpanjang paket yang sama secara otomatis, atau ganti paket kapan saja dengan proses yang simpel.'],
            ] as $feature)
            <div class="card" style="display:flex;flex-direction:column;gap:1rem;">
                <div style="font-size:2rem;width:52px;height:52px;display:flex;align-items:center;justify-content:center;background:var(--color-bg-elevated);border-radius:var(--radius-md);border:1px solid var(--color-border);">{{ $feature[0] }}</div>
                <div>
                    <h3 style="font-size:1rem;margin-bottom:0.5rem;">{{ $feature[1] }}</h3>
                    <p style="font-size:0.875rem;color:var(--color-text-secondary);line-height:1.6;margin:0;">{{ $feature[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── PRICING PREVIEW ───────────────────────────────────────────────────── --}}
<section class="section">
    <div class="container" style="text-align:center;">
        <div class="badge badge-brand" style="margin-bottom:1rem;">Harga Transparan</div>
        <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);margin-bottom:1rem;">Paket yang Tepat untuk Anda</h2>
        <p style="color:var(--color-text-secondary);margin-bottom:2.5rem;">Mulai gratis, upgrade kapan saja. Tidak ada biaya tersembunyi.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;max-width:900px;margin:0 auto 2.5rem;">
            @foreach([
                ['Trial','Rp 0','7 Hari','Coba gratis semua fitur','badge-success',false],
                ['Monthly','Rp 49.000','30 Hari','Akses penuh 1 bulan','badge-brand',false],
                ['Yearly','Rp 399.000','365 Hari','Hemat 32%, akses 1 tahun penuh','badge-brand',true],
            ] as $p)
            <div class="pricing-card {{ $p[4] === 'badge-brand' && $p[5] ? 'pricing-card--featured' : '' }}">
                @if($p[5])
                    <div class="pricing-card__badge">⭐ Terpopuler</div>
                @endif
                <div class="pricing-card__plan">{{ $p[0] }}</div>
                <div class="pricing-card__price">{{ $p[1] }}</div>
                <div class="pricing-card__period">untuk {{ $p[2] }}</div>
                <p style="font-size:0.85rem;color:var(--color-text-secondary);margin:0 0 1.5rem;">{{ $p[3] }}</p>
                <a href="{{ route('plans') }}" class="btn {{ $p[5] ? 'btn-primary' : 'btn-secondary' }}" style="width:100%;">Pilih Paket</a>
            </div>
            @endforeach
        </div>

        <a href="{{ route('plans') }}" style="font-size:0.875rem;color:var(--color-brand-light);">Lihat semua paket lengkap &rarr;</a>
    </div>
</section>

{{-- ── CTA SECTION ───────────────────────────────────────────────────────── --}}
<section style="padding:5rem 1.5rem;background:var(--color-bg-surface);border-top:1px solid var(--color-border);">
    <div class="container-sm" style="text-align:center;">
        <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--color-brand),var(--color-accent));border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1.5rem;">📚</div>
        <h2 style="font-size:clamp(1.5rem,4vw,2.25rem);margin-bottom:1rem;">Bergabung dengan 5.000+ Peneliti</h2>
        <p style="color:var(--color-text-secondary);margin-bottom:2rem;line-height:1.7;">Mulai perjalanan akademik Anda hari ini. Coba 7 hari gratis, tidak perlu kartu kredit.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('plans') }}" class="btn btn-primary btn-lg">Coba Gratis 7 Hari</a>
            <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">Sudah punya akun? Masuk</a>
        </div>
    </div>
</section>

@endsection
