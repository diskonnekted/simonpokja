{{-- Script global panel diskusi. Sertakan SEKALI per halaman yang memakai pesan._chat. --}}
@once
@push('scripts')
<script>
(function () {
    const INTERVAL = 8000; // polling 8 detik
    const state = new WeakMap(); // el -> { timer, signature }

    const esc = (s) => String(s).replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    function bubble(m) {
        const baca = m.milik_sendiri
            ? `<span class="ms-1" title="${m.dibaca ? 'Sudah dibaca' : 'Terkirim'}">${m.dibaca ? '<i class="bi bi-check2-all"></i>' : '<i class="bi bi-check2"></i>'}</span>`
            : '';
        return `
        <div class="d-flex mb-2 ${m.milik_sendiri ? 'justify-content-end' : ''}">
            <div class="p-2 rounded-3 shadow-sm ${m.milik_sendiri ? 'bg-primary text-white' : 'bg-light border'}"
                 style="max-width: 82%;">
                ${m.milik_sendiri ? '' : `<div class="fw-semibold small mb-1">${esc(m.pengirim)} <span class="badge text-bg-secondary fw-normal">${esc(m.peran)}</span></div>`}
                <div style="white-space: pre-wrap; word-break: break-word;">${esc(m.pesan)}</div>
                <div class="small mt-1 ${m.milik_sendiri ? 'text-white-50' : 'text-muted'}" style="font-size:.72rem;">${esc(m.waktu)}${baca}</div>
            </div>
        </div>`;
    }

    async function muat(el) {
        if (!el.dataset.url) return;
        const isi = el.querySelector('.chat-isi');
        try {
            const res = await fetch(el.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();
            const sig = data.pesans.map((p) => `${p.id}:${p.dibaca ? 1 : 0}`).join(',');
            const st = state.get(el);
            if (st && st.signature === sig) return; // tidak ada perubahan
            const dekatBawah = isi.scrollTop + isi.clientHeight >= isi.scrollHeight - 60;
            isi.innerHTML = data.pesans.length
                ? data.pesans.map(bubble).join('')
                : isi.querySelector('.chat-kosong')?.outerHTML
                    || '<div class="text-center text-muted small py-4">Belum ada pesan.</div>';
            if (st) st.signature = sig;
            if (dekatBawah || !st?.pernahMuat) isi.scrollTop = isi.scrollHeight;
            if (st) st.pernahMuat = true;
        } catch (e) { /* jaringan bermasalah: coba lagi pada siklus berikut */ }
    }

    function mulai(el) {
        if (state.has(el)) hentikan(el);
        state.set(el, { timer: setInterval(() => muat(el), INTERVAL), signature: null, pernahMuat: false });
        muat(el);
    }

    function hentikan(el) {
        const st = state.get(el);
        if (st?.timer) clearInterval(st.timer);
        state.delete(el);
    }

    // Kirim pesan (delegasi — berlaku untuk panel statis maupun di dalam modal)
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('.chat-form');
        if (!form) return;
        e.preventDefault();
        const el = form.closest('.chat-paket');
        const ta = form.querySelector('textarea[name="pesan"]');
        const teks = ta.value.trim();
        if (!teks) return;
        const tombol = form.querySelector('button[type="submit"]');
        tombol.disabled = true;
        try {
            const res = await fetch(el.dataset.store, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ pesan: teks }),
            });
            if (res.ok) {
                ta.value = '';
                const st = state.get(el);
                if (st) st.signature = null; // paksa render ulang
                await muat(el);
            }
        } finally {
            tombol.disabled = false;
            ta.focus();
        }
    });

    // Ctrl+Enter / Enter (tanpa Shift) untuk kirim
    document.addEventListener('keydown', (e) => {
        if (e.target.matches('.chat-form textarea') && e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            e.target.closest('form').requestSubmit();
        }
    });

    // Panel statis yang sudah punya data-url saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.chat-paket[data-url]').forEach((el) => {
            if (el.dataset.url) mulai(el);
        });
    });

    // API untuk panel dinamis (mis. di dalam modal)
    window.ChatPaket = {
        attach(el, urlIndex, urlStore) {
            el.dataset.url = urlIndex;
            el.dataset.store = urlStore;
            el.querySelector('.chat-isi').innerHTML =
                '<div class="text-center text-muted small py-4"><span class="spinner-border spinner-border-sm"></span> Memuat pesan…</div>';
            mulai(el);
        },
        detach: hentikan,
    };
})();
</script>
@endpush
@endonce
