@extends('layouts.app')

@section('title', 'Dashboard — E-Journal')

@section('content')

<section style="padding:2.5rem 1.5rem 4rem;">
    <div class="container">

        {{-- Header --}}
        <div style="margin-bottom:2rem;" class="animate-fade-up">
            <h1 style="font-size:1.75rem;margin-bottom:0.25rem;">Selamat datang, {{ auth()->user()->name }} 👋</h1>
            <p style="color:var(--color-text-secondary);font-size:0.9rem;">{{ now()->format('l, d F Y') }}</p>
        </div>

        {{-- Subscription Status Card --}}
        <div style="margin-bottom:2rem;" class="animate-fade-up animate-delay-1">
            @if($activeSubscription)
                <div class="card card--highlight" style="display:flex;align-items:flex-start;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:200px;">
                        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                            <div class="badge badge-success">● Aktif</div>
                            <span style="font-size:0.8rem;color:var(--color-text-muted);text-transform:capitalize;font-weight:600;">
                                Paket {{ ucfirst($activeSubscription->plan) }}
                            </span>
                        </div>

                        <h2 style="font-size:1.1rem;margin-bottom:1rem;">Langganan Anda Aktif</h2>

                        <div style="margin-bottom:0.5rem;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
                                <span style="font-size:0.8rem;color:var(--color-text-secondary);">Sisa akses</span>
                                <span style="font-size:0.8rem;color:var(--color-text-secondary);">
                                    @if($activeSubscription->is_lifetime)
                                        ∞ Lifetime
                                    @else
                                        {{ $activeSubscription->remaining_days }} hari tersisa
                                    @endif
                                </span>
                            </div>
                            <div class="progress">
                                {{-- $progressPct dihitung di PageController::dashboard(), bukan di view --}}
                                <div class="progress__bar" style="width:{{ $progressPct }}%"></div>
                            </div>
                        </div>

                        <div style="display:flex;gap:1.5rem;margin-top:1rem;flex-wrap:wrap;">
                            <div>
                                <div style="font-size:0.75rem;color:var(--color-text-muted);margin-bottom:0.2rem;">Mulai</div>
                                {{-- started_at sudah di-cast ke Carbon oleh Model, tidak perlu Carbon::parse() --}}
                                <div style="font-size:0.875rem;font-weight:600;">{{ $activeSubscription->started_at->format('d M Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size:0.75rem;color:var(--color-text-muted);margin-bottom:0.2rem;">Berakhir</div>
                                <div style="font-size:0.875rem;font-weight:600;">
                                    @if($activeSubscription->is_lifetime)
                                        Tidak Terbatas
                                    @else
                                        {{ $activeSubscription->expires_at->format('d M Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex;flex-direction:column;gap:0.75rem;min-width:160px;">
                        <a href="#" class="btn btn-primary btn-sm" style="justify-content:center;">
                            📥 Download Jurnal
                        </a>
                        <form action="{{ route('subscription.cancel', $activeSubscription->id) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin membatalkan langganan?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;color:var(--color-danger);border-color:rgba(239,68,68,0.3);">
                                Batalkan Langganan
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- No subscription --}}
                <div class="card" style="text-align:center;padding:3rem 2rem;">
                    <div style="font-size:3rem;margin-bottom:1rem;">🔒</div>
                    <h2 style="font-size:1.25rem;margin-bottom:0.75rem;">Belum Berlangganan</h2>
                    <p style="color:var(--color-text-secondary);margin-bottom:1.5rem;font-size:0.9rem;">
                        Pilih paket untuk mendapatkan akses penuh ke seluruh koleksi jurnal ilmiah.
                    </p>
                    <a href="{{ route('plans') }}" class="btn btn-primary" style="display:inline-flex;">
                        Lihat Paket Berlangganan →
                    </a>
                </div>
            @endif
        </div>

        {{-- Quick Access / Services --}}
        <div style="margin-bottom:2rem;" class="animate-fade-up animate-delay-2">
            <h3 style="font-size:1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                📚 Layanan Jurnal
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem;">
                <a href="{{ route('module1') }}" class="card card--interactive" style="text-decoration:none;display:flex;align-items:center;gap:1rem;padding:1.25rem;">
                    <div style="font-size:1.5rem;background:rgba(59,130,246,0.1);padding:0.75rem;border-radius:0.75rem;">🔍</div>
                    <div>
                        <h4 style="font-size:0.95rem;margin-bottom:0.2rem;color:var(--color-text-primary);">Pencarian Katalog</h4>
                        <p style="font-size:0.75rem;color:var(--color-text-secondary);">Cari jurnal berdasarkan judul, penulis, atau tahun.</p>
                    </div>
                </a>
                <a href="{{ route('module2') }}" class="card card--interactive" style="text-decoration:none;display:flex;align-items:center;gap:1rem;padding:1.25rem;">
                    <div style="font-size:1.5rem;background:rgba(16,185,129,0.1);padding:0.75rem;border-radius:0.75rem;">🧪</div>
                    <div>
                        <h4 style="font-size:0.95rem;margin-bottom:0.2rem;color:var(--color-text-primary);">Pencarian Lanjutan</h4>
                        <p style="font-size:0.75rem;color:var(--color-text-secondary);">Filter spesifik menggunakan abstrak dan kategori.</p>
                    </div>
                </a>
                <div class="card" style="display:flex;align-items:center;gap:1rem;padding:1.25rem;opacity:0.7;">
                    <div style="font-size:1.5rem;background:rgba(245,158,11,0.1);padding:0.75rem;border-radius:0.75rem;">💡</div>
                    <div>
                        <h4 style="font-size:0.95rem;margin-bottom:0.2rem;color:var(--color-text-primary);">Rekomendasi Pintar</h4>
                        <p style="font-size:0.75rem;color:var(--color-text-secondary);">Jurnal yang relevan dengan minat baca Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;" class="animate-fade-up animate-delay-2">
            <div class="stat-card">
                <div class="stat-card__label">Total Langganan</div>
                <div class="stat-card__value">{{ $subscriptionCount }}</div>
                <div class="stat-card__sub">sepanjang waktu</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Status</div>
                <div class="stat-card__value" style="font-size:1.25rem;margin-top:0.25rem;">
                    @if($activeSubscription)
                        <span style="color:var(--color-success);">● Aktif</span>
                    @else
                        <span style="color:var(--color-text-muted);">● Tidak Aktif</span>
                    @endif
                </div>
                <div class="stat-card__sub">status saat ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Paket Aktif</div>
                <div class="stat-card__value" style="font-size:1.25rem;margin-top:0.25rem;text-transform:capitalize;">
                    {{ $activeSubscription ? ucfirst($activeSubscription->plan) : '—' }}
                </div>
                <div class="stat-card__sub">paket berlangganan</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Sisa Hari</div>
                <div class="stat-card__value">
                    @if($activeSubscription && $activeSubscription->is_lifetime)
                        ∞
                    @elseif($activeSubscription)
                        {{ $activeSubscription->remaining_days }}
                    @else
                        0
                    @endif
                </div>
                <div class="stat-card__sub">hari tersisa</div>
            </div>
        </div>

        {{-- Subscription History --}}
        <div class="card animate-fade-up animate-delay-3" style="padding:0;overflow:hidden;">
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between;">
                <h3 style="font-size:1rem;margin:0;">Riwayat Langganan</h3>
                <span style="font-size:0.8rem;color:var(--color-text-muted);">{{ $subscriptions->count() }} total</span>
            </div>

            @if($subscriptions->isEmpty())
                <div style="padding:3rem;text-align:center;color:var(--color-text-muted);">
                    Belum ada riwayat langganan
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Paket</th>
                                <th>Mulai</th>
                                <th>Berakhir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $sub)
                            <tr>
                                <td style="font-weight:600;text-transform:capitalize;color:var(--color-text-primary);">
                                    {{ ucfirst($sub->plan) }}
                                </td>
                                <td>{{ $sub->started_at->format('d M Y') }}</td>
                                <td>
                                    @if($sub->is_lifetime)
                                        <span style="color:var(--color-brand-light);">Lifetime</span>
                                    @else
                                        {{ $sub->expires_at->format('d M Y') }}
                                    @endif
                                </td>
                                <td>
                                    {{-- status sudah di-cast ke SubscriptionStatus Enum oleh Model --}}
                                    @switch($sub->status->value)
                                        @case('active')
                                            <span class="badge badge-success">Aktif</span>
                                            @break
                                        @case('expired')
                                            <span class="badge badge-warning">Kadaluarsa</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-danger">Dibatalkan</span>
                                            @break
                                        @default
                                            <span class="badge badge-muted">{{ $sub->status->value }}</span>
                                    @endswitch
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</section>

@endsection
