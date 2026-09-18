<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fa-solid fa-user-gear me-2 text-indigo-600"></i>Quản lý Thông tin Tài khoản</h1>
            <p class="text-gray-500 text-sm mt-0.5">Cập nhật thông tin cá nhân và thay đổi mật khẩu đăng nhập.</p>
        </div>
    </div>

    <!-- UPDATE PERSONAL INFO -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 border-b pb-3"><i class="fa-solid fa-id-card me-2 text-indigo-500"></i>Thông tin cá nhân</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_info">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Tên đăng nhập (Username)</label>
                    <input type="text" class="form-control rounded-xl bg-gray-100 text-gray-500" value="<?= esc($user['username']) ?>" disabled>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Vai trò tài khoản</label>
                    <input type="text" class="form-control rounded-xl bg-gray-100 text-gray-500 capitalize" value="<?= esc($user['role']) ?>" disabled>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control rounded-xl" value="<?= esc($user['full_name']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Địa chỉ Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control rounded-xl" value="<?= esc($user['email']) ?>" required>
                </div>
                <div class="col-12 pt-2">
                    <button type="submit" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-5 py-2.5">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật Thông tin
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- CHANGE PASSWORD -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 border-b pb-3"><i class="fa-solid fa-key me-2 text-indigo-500"></i>Đổi mật khẩu</h3>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold text-gray-700">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" class="form-control rounded-xl" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Mật khẩu mới (Tối thiểu 6 ký tự) <span class="text-danger">*</span></label>
                    <input type="password" name="new_password" class="form-control rounded-xl" minlength="6" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control rounded-xl" minlength="6" required>
                </div>
                <div class="col-12 pt-2">
                    <button type="submit" class="btn bg-emerald-600 text-white hover:bg-emerald-700 font-bold rounded-xl px-5 py-2.5">
                        <i class="fa-solid fa-shield-halved me-1"></i> Đổi Mật Khẩu
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
