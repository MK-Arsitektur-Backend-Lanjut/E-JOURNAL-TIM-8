@extends('layouts.app')

@section('title', 'Paket Langganan — E-Journal')
@section('description', 'Pilih paket berlangganan yang sesuai kebutuhan penelitian Anda. Mulai gratis 7 hari.')

@section('content')

<section style="padding:4rem 1.5rem 6rem;position:relative;overflow:hidden;">

    {{-- Background --}}
    <div style="position:absolute;top:-150px;left:50%;transform:translateX(-50%);width:700px;height:700px;background:radial-gradient(circle,rgba(99,102,241,0.06) 0%,transparent 65%);pointer-events:none;"></div>

    <div class="container" style="position:relative;">

        {{-- Header --}}
        <div style="text-align:center;margin-bottom:3.5rem;" class="animate-fade-up">
            <div class="badge badge-brand" style="margin-bottom:1rem;">Harga Transparan</div>
            <h1 style="font-size:clamp(2rem,5vw,3rem);margin-bottom:1rem;">Pilih Paket yang Tepat</h1>
            <p style="color:var(--color-text-secondary);max-width:480px;margin:0 auto;font-size:1rem;line-height:1.7;">
                Semua paket termasuk akses penuh ke seluruh koleksi jurnal. <br>Tidak ada biaya tersembunyi.
            </p>
        </div>

        {{-- Pricing Grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;max-width:1000px;margin:0 auto 3rem;">

            {{-- TRIAL --}}
            <div class="pricing-card animate-fade-up animate-delay-1">
                <div class="pricing-card__plan">Trial</div>
                <div class="pricing-card__price"><span>Rp </span>0</div>
                <div class="pricing-card__period">untuk 7 hari pertama</div>
                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">Akses semua jurnal selama 7 hari</li>
                    <li class="pricing-card__feature">Download tidak terbatas</li>
                    <li class="pricing-card__feature">Dashboard personal</li>
                    <li class="pricing-card__feature">Email notifikasi</li>
                    <li class="pricing-card__feature--dim">Akses riwayat 5 tahun terakhir</li>
                    <li class="pricing-card__feature--dim">Prioritas support</li>
                </ul>
                <form action="{{ route('subscribe.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="trial">
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">
                        Mulai Trial Gratis
                    </button>
                </form>
            </div>

            {{-- MONTHLY --}}
            <div class="pricing-card animate-fade-up animate-delay-2">
                <div class="pricing-card__plan">Monthly</div>
                <div class="pricing-card__price"><span>Rp </span>49.000</div>
                <div class="pricing-card__period">per bulan (30 hari)</div>
                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">Akses penuh 1 bulan</li>
                    <li class="pricing-card__feature">Download tidak terbatas</li>
                    <li class="pricing-card__feature">Dashboard personal</li>
                    <li class="pricing-card__feature">Email notifikasi</li>
                    <li class="pricing-card__feature">Akses riwayat 5 tahun</li>
                    <li class="pricing-card__feature--dim">Prioritas support</li>
                </ul>
                <form action="{{ route('subscribe.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">
                        Pilih Monthly
                    </button>
                </form>
            </div>

            {{-- YEARLY (FEATURED) --}}
            <div class="pricing-card pricing-card--featured animate-fade-up animate-delay-3">
                <div class="pricing-card__badge">⭐ Terpopuler</div>
                <div class="pricing-card__plan" style="color:var(--color-brand-light);">Yearly</div>
                <div class="pricing-card__price" style="color:var(--color-brand-light);">
                    <span style="color:var(--color-brand-light);">Rp </span>399.000
                </div>
                <div class="pricing-card__period">per tahun (365 hari)</div>
                <div style="display:inline-block;padding:0.25rem 0.75rem;background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);border-radius:9999px;font-size:0.75rem;color:#10b981;font-weight:600;margin-bottom:1rem;">
                    Hemat 32% vs Monthly
                </div>
                <ul class="pricing-card__features">
                    <li class="pricing-card__feature">Akses penuh 1 tahun</li>
                    <li class="pricing-card__feature">Download tidak terbatas</li>
                    <li class="pricing-card__feature">Dashboard personal</li>
                    <li class="pricing-card__feature">Email notifikasi & reminder</li>
                    <li class="pricing-card__feature">Akses riwayat 5 tahun</li>
                    <li class="pricing-card__feature">Prioritas support</li>
                </ul>
                <form action="{{ route('subscribe.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Pilih Yearly — Hemat 32%
                    </button>
                </form>
            </div>

        </div>

        {{-- Guarantee Row --}}
        <div style="text-align:center;margin-bottom:3rem;">
            <div style="display:flex;justify-content:center;gap:2rem;flex-wrap:wrap;">
                @foreach(['🔒 Pembayaran Aman','✓ Tanpa Kartu Kredit untuk Trial','⚡ Aktivasi Instan','📧 Email Konfirmasi Otomatis'] as $item)
                <div style="font-size:0.8rem;color:var(--color-text-muted);display:flex;align-items:center;gap:0.3rem;">{{ $item }}</div>
                @endforeach
            </div>
        </div>

        {{-- FAQ Section --}}
        <div class="container-sm">
            <h2 style="font-size:1.5rem;text-align:center;margin-bottom:2rem;">Pertanyaan Umum</h2>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                @foreach([
                    ['Apakah bisa ganti paket?','Ya! Jika Anda ingin berganti paket, batalkan langganan aktif terlebih dahulu, lalu pilih paket baru. Tidak ada penalti pembatalan.'],
                    ['Apakah bisa perpanjang paket yang sama?','Tentu. Langganan paket yang sama akan otomatis diperpanjang tanpa perlu cancel dulu. Sisa hari akan ditambahkan.'],
                    ['Bagaimana jika langganan habis?','Anda akan mendapat email reminder H-7 dan H-3 sebelum habis. Setelah habis, akses download dinonaktifkan hingga perpanjangan.'],
                    ['Apakah ada refund?','Trial gratis tidak memerlukan pembayaran, jadi tidak ada refund yang diperlukan untuk tahap trial.'],
                ] as $faq)
                <div class="card" style="cursor:pointer;">
                    <div style="font-weight:600;font-size:0.9rem;margin-bottom:0.5rem;">{{ $faq[0] }}</div>
                    <div style="font-size:0.875rem;color:var(--color-text-secondary);line-height:1.6;">{{ $faq[1] }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

@endsection
