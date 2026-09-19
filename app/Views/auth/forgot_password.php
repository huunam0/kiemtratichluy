<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto my-8">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-indigo-600 px-6 py-8 text-center text-white">
            <i class="fa-solid fa-user-shield text-4xl mb-3 text-yellow-300"></i>
            <h2 class="text-2xl font-bold">Gửi Yêu cầu Đặt lại Mật khẩu</h2>
            <p class="text-indigo-200 text-sm mt-1">Hệ thống Bảo mật Xác thực qua Giáo viên</p>
        </div>

        <form action="" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tên đăng nhập hoặc Email tài khoản</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required value="<?= esc(old('username')) ?>"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="Nhập tên đăng nhập hoặc email">
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 text-xs text-amber-900 space-y-1">
                <div class="font-bold flex items-center text-amber-950 mb-1">
                    <i class="fa-solid fa-shield-halved me-1 text-amber-600"></i> Hướng dẫn khôi phục mật khẩu:
                </div>
                <p class="mb-0">Sau khi bạn bấm <strong>"Gửi Yêu cầu"</strong>, yêu cầu của bạn sẽ được chuyển đến danh sách phê duyệt của <strong>Giáo viên trong trường</strong> của bạn. Hãy báo Giáo viên để được duyệt & nhận mật khẩu mới (Mật khẩu mặc định: <code>123456</code>).</p>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-200 transition touch-target">
                Gửi Yêu Cầu Cho Giáo Viên <i class="fa-solid fa-paper-plane ms-2"></i>
            </button>
        </form>

        <div class="bg-gray-50 border-t border-gray-100 p-4 text-center text-sm text-gray-600 flex justify-between items-center px-6">
            <a href="<?= base_url('auth/login') ?>" class="text-indigo-600 font-semibold hover:underline">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Đăng nhập
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
