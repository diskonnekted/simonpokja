<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pokja LPSE Banjarnegara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #2563eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #fff;
            margin: 0 auto;
        }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(37,99,235,0.15); border-color: #2563eb; }
    </style>
</head>
<body>
    <div class="login-card bg-white p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon mb-3"><i class="bi bi-shield-check"></i></div>
            <h4 class="fw-bold mb-1">Monitoring Pokja LPSE</h4>
            <p class="text-secondary mb-0">Kabupaten Banjarnegara</p>
        </div>

        @if (session('warning'))
            <div class="alert alert-warning py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i>{{ session('warning') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success py-2 small">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email atau Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="email" id="loginInput" value="{{ old('email') }}" class="form-control" placeholder="admin / pokja1 / email resmi" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Password" required>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        @if (app()->isLocal())
        <div class="mt-4 p-3 bg-light rounded-3 border">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold small text-dark"><i class="bi bi-key-fill text-primary me-1"></i>Pilihan Cepat Masuk (Mode Dev):</span>
                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">Password: password</span>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fill-btn" data-login="admin@banjarnegara.go.id" style="font-size: 0.75rem;">
                    Admin
                </button>
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fill-btn" data-login="pokja{{ $i }}@banjarnegara.go.id" style="font-size: 0.75rem;">
                        Pokja {{ $i }}
                    </button>
                @endfor
            </div>
            <div class="text-secondary" style="font-size: 0.72rem;">
                * Hanya aktif pada mode pengembangan (local). Di production wajib menggunakan email resmi terdaftar.
            </div>
        </div>
        @endif

        <hr class="my-4">
        <p class="text-center text-secondary small mb-0">
            <i class="bi bi-shield-lock me-1"></i>
            Sistem Monitoring Pengadaan Barang/Jasa
        </p>
    </div>

    @if (app()->isLocal())
    <script>
        document.querySelectorAll('.fill-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('loginInput').value = this.getAttribute('data-login');
                document.getElementById('passwordInput').value = 'password';
                document.getElementById('loginInput').focus();
            });
        });
    </script>
    @endif
</body>
</html>
