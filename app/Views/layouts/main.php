<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($title ?? 'Hệ thống Kiểm tra Tích luỹ') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Quill.js Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Highlight.js CSS for Code Snippets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
    <style>
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }
        .touch-target {
            min-height: 56px;
        }
        .quill-editor-container {
            background-color: #ffffff;
            border-radius: 0.5rem;
        }
        .ql-editor {
            min-height: 150px;
            font-size: 1rem;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Header Navigation -->
    <nav class="bg-indigo-700 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="flex items-center gap-2 text-xl font-bold tracking-tight text-white hover:text-indigo-100 no-underline">
                <i class="fa-solid fa-graduation-cap text-yellow-400 text-2xl"></i>
                <span>TÍCH LUỸ</span>
            </a>
            
            <?php if (session()->get('isLoggedIn')): ?>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="font-semibold text-sm"><?= esc(session()->get('full_name')) ?></div>
                        <div class="text-xs text-indigo-200 capitalize">
                            <span class="badge bg-indigo-900/60 font-medium px-2 py-0.5 rounded">
                                <?= esc(session()->get('user_role')) ?>
                            </span>
                        </div>
                    </div>
                    <?php if (session()->get('user_role') === 'admin' || session()->get('role') === 'admin'): ?>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-warning btn-sm font-semibold rounded-lg text-indigo-950">
                            <i class="fa-solid fa-users-gear me-1"></i> Tài khoản
                        </a>
                        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-indigo-100 bg-indigo-800 hover:bg-indigo-900 btn-sm font-semibold rounded-lg text-white border border-indigo-500">
                            <i class="fa-solid fa-gauge me-1"></i> Quản trị
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('profile') ?>" class="btn btn-light btn-sm font-semibold rounded-lg text-indigo-900">
                        <i class="fa-solid fa-user-gear me-1"></i> Hồ sơ
                    </a>
                    <a href="<?= base_url('auth/logout') ?>" class="btn btn-outline-light btn-sm rounded-lg px-3">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Thoát
                    </a>
                </div>
            <?php else: ?>
                <div class="flex gap-2">
                    <a href="<?= base_url('auth/login') ?>" class="btn btn-light btn-sm font-medium rounded-lg">Đăng nhập</a>
                    <a href="<?= base_url('auth/register') ?>" class="btn btn-warning btn-sm font-medium rounded-lg">Đăng ký</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-xl shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-xl shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Body -->
    <main class="flex-grow max-w-7xl mx-auto px-4 py-4 w-full">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <p class="mb-0">&copy; <?= date('Y') ?> Hệ thống Kiểm tra Tích luỹ (Accumulated Testing System). CodeIgniter 4 MVC.</p>
        </div>
    </footer>

    <!-- jQuery CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Quill.js JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <!-- Highlight.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
</body>
</html>
