{{-- 
    Komponen Notifikasi Terpadu (In-App Dropdown + Web Push Browser API)
    SIMONPOKJA — Swiss Data-Dense Crisp Theme
--}}
<div class="dropdown me-1" id="notifDropdownWrapper">
    <button class="btn btn-outline-secondary btn-sm position-relative bg-white text-dark d-flex align-items-center justify-content-center" 
            id="notifBellBtn" 
            data-bs-toggle="dropdown" 
            data-bs-auto-close="outside"
            aria-expanded="false" 
            title="Pemberitahuan"
            style="border-radius: var(--radius-input); border-color: var(--border-panel); width: 34px; height: 34px;">
        <i class="bi bi-bell fs-6 text-secondary" id="notifBellIcon"></i>
        <span id="notifBadge" 
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
              style="font-size: 0.65rem; padding: 0.25em 0.45em; display: none;">
            0
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0 border" 
         id="notifDropdownMenu"
         style="width: 350px; max-width: 90vw; border-radius: var(--radius-panel); border-color: var(--border-panel); font-size: 13px;">
        
        {{-- Header Dropdown --}}
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light" 
             style="border-top-left-radius: var(--radius-panel); border-top-right-radius: var(--radius-panel);">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark" style="letter-spacing: 0.2px;">Pemberitahuan</span>
                <span id="notifHeaderBadge" class="badge text-bg-primary rounded-pill small" style="font-size: 0.7rem; display: none;">0 Baru</span>
            </div>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="btnBacaSemua" style="font-size: 12px;">
                Tandai semua dibaca
            </button>
        </div>

        {{-- Kontrol Saklar Push Browser --}}
        <div class="px-3 py-2 border-bottom bg-white d-flex align-items-center justify-content-between" style="font-size: 12px;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-broadcast text-primary"></i>
                <span class="text-secondary">Pop-up Browser (OS)</span>
            </div>
            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                <input class="form-check-input ms-0" type="checkbox" role="switch" id="pushToggleSwitch" style="cursor: pointer;">
            </div>
        </div>

        {{-- Daftar Notifikasi (Scrollable) --}}
        <div id="notifListContainer" class="overflow-auto" style="max-height: 340px;">
            <div class="text-center py-4 text-muted small" id="notifEmptyState">
                <i class="bi bi-bell-slash d-block fs-3 mb-1 text-secondary opacity-50"></i>
                Tidak ada pemberitahuan baru
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-2 border-top bg-light text-center" 
             style="border-bottom-left-radius: var(--radius-panel); border-bottom-right-radius: var(--radius-panel); font-size: 11.5px;">
            <span class="text-muted"><i class="bi bi-shield-check me-1 text-success"></i>SIMONPOKJA Notification Service</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const VAPID_KEY_URL = "{{ route('push.vapidKey') }}";
    const SUBSCRIBE_URL = "{{ route('push.subscribe') }}";
    const UNSUBSCRIBE_URL = "{{ route('push.unsubscribe') }}";
    const NOTIF_INDEX_URL = "{{ route('notifikasi.index') }}";
    const BACA_SEMUA_URL = "{{ route('notifikasi.bacaSemua') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    let lastUnreadCount = 0;
    let swRegistration = null;

    // Helper Konversi base64 VAPID Key ke Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Audio synthesizer ringkas untuk notifikasi (tanpa file audio eksternal)
    function playChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1); // A5
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.start();
            osc.stop(ctx.currentTime + 0.35);
        } catch(e) {}
    }

    // Inisialisasi Service Worker & Status Push
    async function initServiceWorker() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            const toggle = document.getElementById('pushToggleSwitch');
            if (toggle) {
                toggle.disabled = true;
                toggle.title = 'Browser tidak mendukung Push API';
            }
            return;
        }

        try {
            swRegistration = await navigator.serviceWorker.register('/sw.js');
            const sub = await swRegistration.pushManager.getSubscription();
            const toggle = document.getElementById('pushToggleSwitch');
            if (toggle) {
                toggle.checked = !!sub && Notification.permission === 'granted';
            }
        } catch (e) {
            console.warn('[Push] Gagal registrasi service worker:', e);
        }
    }

    // Toggle Aktif/Nonaktif Push Notification Browser
    async function handleTogglePush(e) {
        const toggle = e.target;
        if (toggle.checked) {
            if (Notification.permission === 'denied') {
                alert('Izin notifikasi telah diblokir di peramban Anda. Silakan aktifkan izin notifikasi pada ikon gembok di bilah alamat browser.');
                toggle.checked = false;
                return;
            }

            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                toggle.checked = false;
                return;
            }

            try {
                const resKey = await fetch(VAPID_KEY_URL);
                const dataKey = await resKey.json();
                if (!dataKey.publicKey) {
                    alert('Kunci VAPID belum dikonfigurasi di server.');
                    toggle.checked = false;
                    return;
                }

                const sub = await swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(dataKey.publicKey)
                });

                const subJson = sub.toJSON();
                await fetch(SUBSCRIBE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        endpoint: subJson.endpoint,
                        keys: {
                            p256dh: subJson.keys?.p256dh,
                            auth: subJson.keys?.auth
                        }
                    })
                });

                // Tampilkan konfirmasi
                if (swRegistration) {
                    swRegistration.showNotification('SIMONPOKJA', {
                        body: 'Push notifikasi berhasil diaktifkan pada peramban ini.',
                        icon: '/favicon.ico'
                    });
                }
            } catch (err) {
                console.error('[Push] Gagal subscribe:', err);
                toggle.checked = false;
                alert('Gagal mengaktifkan push notifikasi: ' + err.message);
            }
        } else {
            // Unsubscribe
            try {
                const sub = await swRegistration.pushManager.getSubscription();
                if (sub) {
                    const endpoint = sub.endpoint;
                    await sub.unsubscribe();
                    await fetch(UNSUBSCRIBE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify({ endpoint })
                    });
                }
            } catch (err) {
                console.error('[Push] Gagal unsubscribe:', err);
            }
        }
    }

    // Render daftar notifikasi ke HTML
    function renderNotifikasis(notifs, unreadCount) {
        const badge = document.getElementById('notifBadge');
        const headerBadge = document.getElementById('notifHeaderBadge');
        const listContainer = document.getElementById('notifListContainer');

        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = 'inline-block';
            headerBadge.textContent = `${unreadCount} Baru`;
            headerBadge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
            headerBadge.style.display = 'none';
        }

        if (!notifs || notifs.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-bell-slash d-block fs-3 mb-1 text-secondary opacity-50"></i>
                    Tidak ada pemberitahuan baru
                </div>`;
            return;
        }

        let html = '';
        notifs.forEach(n => {
            const bgUnread = n.dibaca ? 'bg-white' : 'bg-primary-subtle bg-opacity-25';
            const dotUnread = n.dibaca ? '' : '<span class="badge bg-primary p-1 rounded-circle me-1" style="width:7px; height:7px;"></span>';
            html += `
                <a href="${n.url}" 
                   class="d-flex align-items-start gap-2 p-2 px-3 border-bottom text-decoration-none text-dark notif-item ${bgUnread}" 
                   data-id="${n.id}"
                   style="transition: background-color 0.15s ease;">
                    <div class="mt-1">
                        <i class="bi ${n.icon} text-${n.color} fs-6"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-semibold text-truncate" style="font-size: 12.5px;">${n.judul}</span>
                            <span class="text-muted ms-2" style="font-size: 11px; white-space: nowrap;">${n.waktu}</span>
                        </div>
                        <p class="mb-0 text-secondary text-truncate" style="font-size: 12px;">${dotUnread}${n.pesan}</p>
                    </div>
                </a>`;
        });

        listContainer.innerHTML = html;

        // Pasang event klik tanda baca
        listContainer.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', function(ev) {
                const notifId = this.dataset.id;
                fetch(`/notifikasi/${notifId}/baca`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
                }).catch(() => {});
            });
        });
    }

    // ===== SISTEM TOAST NOTIFIKASI KONSISTEN & ANTI-AI-SLOP (design-system.md) =====
    window.SimonToast = {
        show: function({ title, message, url = null, category = 'sistem', level = 'info', duration = 4500 }) {
            const container = document.getElementById('simonToastContainer');
            if (!container) return;

            const categoryLabels = {
                pesan: 'PESAN BARU',
                anomali: 'PERINGATAN EWS',
                sanggahan: 'SANGGAHAN REKANAN',
                progres: 'UPDATE PROGRES',
                deadline: 'BATAS WAKTU',
                sukses: 'STATUS BERHASIL',
                sistem: 'PEMBERITAHUAN'
            };

            const icons = {
                pesan: 'bi-chat-dots-fill text-primary',
                anomali: 'bi-exclamation-octagon-fill text-danger',
                sanggahan: 'bi-shield-exclamation text-warning',
                progres: 'bi-graph-up-arrow text-info',
                deadline: 'bi-alarm-fill text-danger',
                sukses: 'bi-check-circle-fill text-success',
                sistem: 'bi-info-circle-fill text-secondary'
            };

            const toast = document.createElement('div');
            toast.className = `toast-gov toast-${level}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');

            const catLabel = categoryLabels[category] || 'PEMBERITAHUAN';
            const iconClass = icons[category] || 'bi-bell-fill text-secondary';

            toast.innerHTML = `
                <div class="toast-gov-header">
                    <div class="toast-gov-category">
                        <i class="bi ${iconClass}"></i>
                        <span>${catLabel}</span>
                    </div>
                    <button type="button" class="toast-gov-close" aria-label="Tutup" title="Tutup">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="toast-gov-body">
                    <div class="toast-gov-title">${title}</div>
                    <p class="toast-gov-message">${message}</p>
                </div>
            `;

            if (url && url !== '#' && url !== '/') {
                toast.querySelector('.toast-gov-body').addEventListener('click', () => {
                    window.location.href = url;
                });
            }

            const closeBtn = toast.querySelector('.toast-gov-close');
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismissToast(toast);
            });

            container.appendChild(toast);

            if (duration > 0) {
                setTimeout(() => {
                    dismissToast(toast);
                }, duration);
            }

            function dismissToast(el) {
                if (!el || !el.parentNode) return;
                el.style.animation = 'toastSlideOut 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                setTimeout(() => {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 200);
            }
        }
    };

    // Ambil data notifikasi via Polling
    async function muatNotifikasi() {
        try {
            const res = await fetch(NOTIF_INDEX_URL, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            
            // Jika ada notifikasi baru masuk saat user aktif
            if (data.unread_count > lastUnreadCount && lastUnreadCount !== 0) {
                playChime();
                // Munculkan toast krisp konsisten untuk notifikasi terbaru
                if (data.notifikasis && data.notifikasis.length > 0) {
                    const terbaru = data.notifikasis[0];
                    window.SimonToast.show({
                        title: terbaru.judul,
                        message: terbaru.pesan,
                        url: terbaru.url,
                        category: terbaru.kategori,
                        level: terbaru.tingkat
                    });
                }
            }
            lastUnreadCount = data.unread_count;

            renderNotifikasis(data.notifikasis, data.unread_count);
        } catch(e) {}
    }

    // Event Tandai Semua Dibaca
    document.getElementById('btnBacaSemua')?.addEventListener('click', async function() {
        try {
            await fetch(BACA_SEMUA_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
            });
            muatNotifikasi();
        } catch(e) {}
    });

    // Pasang listener saklar push
    document.getElementById('pushToggleSwitch')?.addEventListener('change', handleTogglePush);

    // Booting saat halaman siap
    document.addEventListener('DOMContentLoaded', () => {
        initServiceWorker();
        muatNotifikasi();
        // Polling setiap 15 detik
        setInterval(muatNotifikasi, 15000);

        // Flash message dari server ditampilkan via SimonToast konsisten
        @if (session('success'))
            window.SimonToast.show({
                title: 'Aksi Berhasil',
                message: {!! json_encode(session('success')) !!},
                category: 'sukses',
                level: 'success'
            });
        @endif
        @if (session('error'))
            window.SimonToast.show({
                title: 'Peringatan Sistem',
                message: {!! json_encode(session('error')) !!},
                category: 'anomali',
                level: 'danger'
            });
        @endif
    });
})();
</script>
@endpush
