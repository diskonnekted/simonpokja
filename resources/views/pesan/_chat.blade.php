{{--
    Panel diskusi Kepala LPSE (supervisor) <-> Pokja, per pekerjaan.
    Variabel: $paket (PaketPengadaan, OPSIONAL — tanpa $paket panel tidak aktif
    sampai diaktifkan lewat window.ChatPaket.attach(), mis. di dalam modal).
    Wajib menyertakan juga: @include('pesan._chat-script') (cukup sekali per halaman).
--}}
<div class="chat-paket d-flex flex-column" data-paket="{{ $paket->id ?? '' }}"
     @if (!empty($paket))
     data-url="{{ route('pesan.index', $paket) }}"
     data-store="{{ route('pesan.store', $paket) }}"
     @endif>
    <div class="chat-isi flex-grow-1 overflow-auto px-3 py-2" style="max-height: 360px; min-height: 160px;">
        <div class="text-center text-muted small py-4 chat-kosong">
            <i class="bi bi-chat-square-text d-block fs-4 mb-1"></i>
            Belum ada pesan. Mulai diskusi dengan {{ auth()->user()->isAdmin() ? 'Pokja pemilik pekerjaan' : 'Kepala LPSE' }}.
        </div>
    </div>
    <form class="chat-form border-top d-flex gap-2 p-2 bg-white">
        @csrf
        <textarea name="pesan" class="form-control form-control-sm" rows="1"
                  placeholder="Tulis pesan…" required maxlength="2000"
                  style="resize: none;"></textarea>
        <button class="btn btn-primary btn-sm px-3" type="submit" title="Kirim">
            <i class="bi bi-send"></i>
        </button>
    </form>
</div>
