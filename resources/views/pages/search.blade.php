@extends('layouts.app')

@section('title', 'Cari Jurnal — E-Journal')

@section('content')
<section style="padding:2.5rem 1.5rem 4rem;">
    <div class="container">
        <div style="margin-bottom:2rem;" class="animate-fade-up">
            <h1 style="font-size:1.75rem;margin-bottom:0.5rem;">E-Journal Catalog</h1>
            <p style="color:var(--color-text-secondary);font-size:0.9rem;">Temukan ribuan jurnal ilmiah berkualitas untuk referensi Anda.</p>
        </div>

        {{-- Search Box --}}
        <div class="card animate-fade-up animate-delay-1" style="margin-bottom:2rem;">
            <form action="{{ route('search') }}" method="GET" style="display:grid;gap:1.5rem;">
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:2;min-width:250px;">
                        <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.5rem;color:var(--color-text-muted);">Kata Kunci</label>
                        <input type="text" name="title" value="{{ request('title') }}" placeholder="Judul jurnal atau topik..." 
                               style="width:100%;padding:0.75rem;border:1px solid var(--color-border);border-radius:0.5rem;background:var(--color-bg-secondary);color:var(--color-text-primary);">
                    </div>
                    <div style="flex:1;min-width:150px;">
                        <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.5rem;color:var(--color-text-muted);">Tahun</label>
                        <input type="number" name="year" value="{{ request('year') }}" placeholder="Contoh: 2024" 
                               style="width:100%;padding:0.75rem;border:1px solid var(--color-border);border-radius:0.5rem;background:var(--color-bg-secondary);color:var(--color-text-primary);">
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                    <div style="display:flex;gap:1rem;">
                        <button type="submit" class="btn btn-primary">
                            🔍 Cari Sekarang
                        </button>
                        <a href="{{ route('search') }}" class="btn btn-ghost">Reset</a>
                    </div>
                    <div style="font-size:0.8rem;color:var(--color-text-muted);">
                        Menampilkan hasil untuk: <span style="color:var(--color-brand);font-weight:600;">"{{ request('title') ?: 'Semua Jurnal' }}"</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Results --}}
        <div class="animate-fade-up animate-delay-2">
            <h3 style="font-size:1.1rem;margin-bottom:1.5rem;">Hasil Pencarian</h3>
            
            {{-- Dummy Result - In real app, this would be @foreach($documents as $doc) --}}
            <div style="display:grid;gap:1rem;">
                <div class="card" style="padding:1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;">
                        <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                            <span class="badge badge-success" style="font-size:0.7rem;">Sains</span>
                            <span style="font-size:0.8rem;color:var(--color-text-muted);">Vol. 12 No. 4 (2024)</span>
                        </div>
                        <h4 style="font-size:1.1rem;margin-bottom:0.5rem;color:var(--color-text-primary);">Penerapan AI dalam Analisis Data Geospasial untuk Prediksi Cuaca</h4>
                        <p style="font-size:0.85rem;color:var(--color-text-secondary);margin-bottom:0.5rem;">Oleh: <span style="font-weight:600;">Dr. Ahmad Sulaiman</span></p>
                    </div>
                    <div>
                        @if(auth()->user()->activeSubscription)
                            <a href="#" class="btn btn-primary btn-sm">📥 Unduh PDF</a>
                        @else
                            <a href="{{ route('plans') }}" class="btn btn-ghost btn-sm" style="color:var(--color-warning);border-color:var(--color-warning);">🔒 Butuh Langganan</a>
                        @endif
                    </div>
                </div>

                <div class="card" style="padding:1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;">
                        <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                            <span class="badge badge-success" style="font-size:0.7rem;">Teknologi</span>
                            <span style="font-size:0.8rem;color:var(--color-text-muted);">Vol. 8 No. 2 (2023)</span>
                        </div>
                        <h4 style="font-size:1.1rem;margin-bottom:0.5rem;color:var(--color-text-primary);">Keamanan Jaringan pada Infrastruktur Cloud Computing di Era 5G</h4>
                        <p style="font-size:0.85rem;color:var(--color-text-secondary);margin-bottom:0.5rem;">Oleh: <span style="font-weight:600;">Prof. Budi Santoso</span></p>
                    </div>
                    <div>
                        @if(auth()->user()->activeSubscription)
                            <a href="#" class="btn btn-primary btn-sm">📥 Unduh PDF</a>
                        @else
                            <a href="{{ route('plans') }}" class="btn btn-ghost btn-sm" style="color:var(--color-warning);border-color:var(--color-warning);">🔒 Butuh Langganan</a>
                        @endif
                    </div>
                </div>

                <div style="text-align:center;padding:2rem;color:var(--color-text-muted);font-size:0.9rem;">
                    Gunakan filter pencarian di atas untuk hasil yang lebih spesifik.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
