<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản Lý Tài Khoản &amp; Đặt Lại Mật Khẩu</h1>
            <p class="text-gray-500 text-sm mt-0.5">Tìm kiếm tài khoản và đặt lại mật khẩu mới cho Giáo viên, Học sinh hoặc Quản trị viên</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary rounded-xl font-medium">
                <i class="fa-solid fa-arrow-left me-1"></i> Bảng điều khiển
            </a>
            <a href="<?= base_url('admin/schools') ?>" class="btn btn-outline-indigo border-indigo-200 text-indigo-700 hover:bg-indigo-50 rounded-xl font-medium">
                <i class="fa-solid fa-school me-1"></i> Trường Học
            </a>
            <a href="<?= base_url('admin/subjects') ?>" class="btn btn-outline-indigo border-indigo-200 text-indigo-700 hover:bg-indigo-50 rounded-xl font-medium">
                <i class="fa-solid fa-book me-1"></i> Môn Học
            </a>
        </div>
    </div>

    <!-- Pending Password Reset Requests (if any) -->
    <?php if (!empty($pending_reset_requests)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-100 bg-amber-50 flex items-center justify-between">
                <h3 class="font-bold text-amber-950 text-base flex items-center gap-2 mb-0">
                    <i class="fa-solid fa-key text-amber-600"></i>
                    Yêu Cầu Đặt Lại Mật Khẩu Đang Chờ Xử Lý Toàn Hệ Thống (<?= count($pending_reset_requests) ?>)
                </h3>
                <span class="text-xs text-amber-700 font-semibold">Phê duyệt &amp; đặt lại mật khẩu về 123456</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-amber-50/50 text-gray-700 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6">Họ Và Tên</th>
                            <th>Tên Đăng Nhập</th>
                            <th>Vai Trò</th>
                            <th>Trường Học</th>
                            <th>Thời Gian Yêu Cầu</th>
                            <th class="text-end pe-6">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($pending_reset_requests as $req): ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900"><?= esc($req['full_name']) ?></td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800">@<?= esc($req['username']) ?></code></td>
                                <td>
                                    <span class="badge <?= $req['role'] === 'student' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800' ?> font-semibold capitalize">
                                        <?= esc($req['role']) ?>
                                    </span>
                                </td>
                                <td><span class="text-gray-600 text-xs"><?= esc($req['school_name'] ?? 'Chưa xác định') ?></span></td>
                                <td class="text-gray-500 text-xs"><?= date('H:i d/m/Y', strtotime($req['created_at'])) ?></td>
                                <td class="text-end pe-6 space-x-2">
                                    <form method="POST" action="<?= base_url("admin/approve-password-request/{$req['id']}") ?>" style="display:inline;" onsubmit="return confirm('Xác nhận đặt lại mật khẩu cho <?= esc($req['full_name']) ?> thành 123456?');">
                                        <input type="hidden" name="new_password" value="123456">
                                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white btn-sm rounded-lg font-bold shadow-sm">
                                            <i class="fa-solid fa-key me-1"></i> Duyệt (123456)
                                        </button>
                                    </form>
                                    <a href="<?= base_url("admin/cancel-password-request/{$req['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-lg font-medium" onclick="return confirm('Hủy yêu cầu đặt lại mật khẩu này?')">
                                        <i class="fa-solid fa-xmark me-1"></i> Hủy
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="<?= base_url('admin/users') ?>" class="row g-3 items-end">
            <div class="col-12 col-md-4">
                <label class="form-label text-xs font-semibold text-gray-600 uppercase">Tìm kiếm tài khoản</label>
                <div class="input-group">
                    <span class="input-group-text bg-gray-50 border-gray-200 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="keyword" value="<?= esc($filters['keyword'] ?? '') ?>" class="form-control rounded-end-xl" placeholder="Tên đăng nhập, họ tên, email...">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label text-xs font-semibold text-gray-600 uppercase">Vai trò</label>
                <select name="role" class="form-select rounded-xl">
                    <option value="">-- Tất cả vai trò --</option>
                    <option value="student" <?= ($filters['role'] ?? '') === 'student' ? 'selected' : '' ?>>Học sinh</option>
                    <option value="teacher" <?= ($filters['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Giáo viên</option>
                    <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label text-xs font-semibold text-gray-600 uppercase">Trường học</label>
                <select name="school_id" class="form-select rounded-xl">
                    <option value="">-- Tất cả trường --</option>
                    <?php foreach ($schools as $sc): ?>
                        <option value="<?= $sc['id'] ?>" <?= (int)($filters['school_id'] ?? 0) === (int)$sc['id'] ? 'selected' : '' ?>>
                            <?= esc($sc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3 flex gap-2">
                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 flex-1">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary rounded-xl px-3" title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2 mb-0">
                <i class="fa-solid fa-users text-indigo-600"></i>
                Danh Sách Tài Khoản Người Dùng (<?= number_format($totalUsers) ?>)
            </h3>
            <span class="text-xs text-gray-500">Hiển thị tối đa 100 kết quả mới nhất</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Họ Và Tên</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Vai Trò</th>
                        <th>Trường Học</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-400">
                                <i class="fa-solid fa-user-slash text-3xl mb-2 text-gray-300"></i>
                                <p class="mb-0">Không tìm thấy tài khoản nào khớp với điều kiện lọc.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900">
                                    <?= esc($u['full_name']) ?>
                                    <?php if ((int)$u['id'] === (int)session()->get('user_id')): ?>
                                        <span class="badge bg-indigo-50 text-indigo-700 text-2xs ms-1 border border-indigo-200">Bạn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800 font-mono text-xs">@<?= esc($u['username']) ?></code>
                                </td>
                                <td class="text-gray-600 text-xs"><?= esc($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-red-100 text-red-800 font-semibold px-2.5 py-1 rounded-lg">Admin</span>
                                    <?php elseif ($u['role'] === 'teacher'): ?>
                                        <span class="badge bg-purple-100 text-purple-800 font-semibold px-2.5 py-1 rounded-lg">Giáo viên</span>
                                    <?php else: ?>
                                        <span class="badge bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-lg">Học sinh</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-gray-700 text-xs"><?= esc($u['school_name'] ?? 'Chưa gán') ?></span>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'approved' || $u['status'] === 'active'): ?>
                                        <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">Hoạt động</span>
                                    <?php elseif ($u['status'] === 'pending'): ?>
                                        <span class="badge bg-amber-50 text-amber-700 border border-amber-200 font-medium">Chờ duyệt</span>
                                    <?php elseif ($u['status'] === 'blocked'): ?>
                                        <span class="badge bg-red-50 text-red-700 border border-red-200 font-medium">Bị khóa</span>
                                    <?php else: ?>
                                        <span class="badge bg-gray-100 text-gray-600 font-medium"><?= esc($u['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-gray-500 text-xs"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                <td class="text-end pe-6">
                                    <?php if ((int)$u['id'] !== (int)session()->get('user_id')): ?>
                                        <button type="button" 
                                                class="btn btn-warning bg-amber-500 hover:bg-amber-600 text-white btn-sm rounded-lg font-bold shadow-sm"
                                                onclick="openResetModal(<?= $u['id'] ?>, '<?= esc(addslashes($u['full_name'])) ?>', '<?= esc(addslashes($u['username'])) ?>', '<?= esc($u['role']) ?>')">
                                            <i class="fa-solid fa-key me-1"></i> Đặt Lại Mật Khẩu
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= base_url('profile') ?>" class="btn btn-outline-secondary btn-sm rounded-lg text-xs">
                                            <i class="fa-solid fa-user-gear me-1"></i> Đổi tại Hồ Sơ
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Đặt Lại Mật Khẩu -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-gradient-to-r from-amber-500 to-amber-600 text-white border-0 px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2" id="resetPasswordModalLabel">
                    <i class="fa-solid fa-key"></i> Đặt Lại Mật Khẩu Người Dùng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetPasswordForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body p-6 space-y-4">
                    <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                        <div class="text-xs text-amber-800 font-semibold uppercase">Tài khoản được chọn</div>
                        <div class="font-bold text-gray-900 text-base mt-0.5" id="modalUserFullName">Nguyễn Văn A</div>
                        <div class="text-xs text-gray-600 mt-0.5">
                            Tên đăng nhập: <code class="bg-white px-1.5 py-0.5 rounded border border-amber-200 text-amber-900 font-bold" id="modalUsername">@user</code> | 
                            Vai trò: <span class="font-semibold capitalize text-gray-800" id="modalUserRole">Học sinh</span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-gray-700 text-sm">
                            Mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="new_password" id="modalNewPassword" class="form-control rounded-start-xl font-mono" value="123456" minlength="6" required placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)...">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPass()" title="Tạo mật khẩu ngẫu nhiên">
                                <i class="fa-solid fa-shuffle me-1"></i> Ngẫu nhiên
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 mb-0">
                            <i class="fa-solid fa-circle-info text-indigo-400 me-1"></i>
                            Mặc định là <code>123456</code>. Bạn có thể tự nhập mật khẩu mới hoặc bấm "Ngẫu nhiên".
                        </p>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 border-t border-gray-100 px-6 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-xl font-medium px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl px-5 shadow-md">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Đặt Lại Mật Khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetModal(userId, fullName, username, role) {
    document.getElementById('modalUserFullName').innerText = fullName;
    document.getElementById('modalUsername').innerText = '@' + username;
    document.getElementById('modalUserRole').innerText = role === 'student' ? 'Học sinh' : (role === 'teacher' ? 'Giáo viên' : 'Quản trị viên');
    document.getElementById('modalNewPassword').value = '123456';
    document.getElementById('resetPasswordForm').action = '<?= base_url("admin/users/reset-password/") ?>/' + userId;
    
    var modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

function generateRandomPass() {
    var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    var pass = '';
    for (var i = 0; i < 8; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('modalNewPassword').value = pass;
}
</script>
<?= $this->endSection() ?>
