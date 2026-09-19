<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto my-8">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-indigo-600 px-6 py-8 text-center text-white">
            <i class="fa-solid fa-user-lock text-4xl mb-3 text-yellow-300"></i>
            <h2 class="text-2xl font-bold">Đăng nhập hệ thống</h2>
            <p class="text-indigo-200 text-sm mt-1">Hệ thống Kiểm tra Tích luỹ Học sinh</p>
        </div>

        <form action="" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tên đăng nhập hoặc Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required 
                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="Nhập username hoặc email">
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-0">Mật khẩu</label>
                    <a href="<?= base_url('forgot-password') ?>" class="text-xs text-indigo-600 font-semibold hover:underline">Quên mật khẩu?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <input type="password" name="password" required 
                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="Nhập mật khẩu">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-200 transition touch-target">
                Đăng Nhập <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="bg-gray-50 border-t border-gray-100 p-4 text-center text-sm text-gray-600">
            Chưa có tài khoản? 
            <a href="<?= base_url('auth/register') ?>" class="text-indigo-600 font-semibold hover:underline">Đăng ký tại đây</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
