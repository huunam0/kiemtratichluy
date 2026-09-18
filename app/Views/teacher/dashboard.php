<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-700 to-indigo-900 rounded-3xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">Chào mừng, <?= esc(session()->get('full_name')) ?>!</h1>
            <p class="text-indigo-200 text-sm mt-1 mb-0">Bảng điều khiển Giáo viên & Kiểm tra Tích luỹ trên lớp</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('teacher/sessions') ?>" class="btn btn-warning font-bold rounded-xl px-4 py-2 text-indigo-950 shadow-md">
                <i class="fa-solid fa-bolt me-1"></i> Mở Lượt Thi Mới
            </a>
            <a href="<?= base_url('teacher/questions/create') ?>" class="btn btn-light font-bold rounded-xl px-4 py-2 text-indigo-900 shadow-md">
                <i class="fa-solid fa-plus me-1"></i> Tạo Câu Hỏi
            </a>
        </div>
    </div>

    <!-- Quick Navigation Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
        <a href="<?= base_url('teacher/classes') ?>" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex items-center justify-between no-underline">
            <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Lớp Học</div>
                <div class="text-2xl font-black text-gray-900 mt-1"><?= count($classes) ?></div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </a>

        <a href="<?= base_url('teacher/questions') ?>" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex items-center justify-between no-underline">
            <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Ngân Hàng Trắc Nghiệm</div>
                <div class="text-2xl font-black text-gray-900 mt-1"><?= count($questions) ?></div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-database"></i>
            </div>
        </a>

        <a href="<?= base_url('teacher/fill-blank/quizzes') ?>" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex items-center justify-between no-underline">
            <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Điền Chỗ Trống</div>
                <div class="text-2xl font-black text-gray-900 mt-1">Đề Thi</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
        </a>

        <a href="<?= base_url('teacher/tests') ?>" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex items-center justify-between no-underline">
            <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Kho Tích Luỹ</div>
                <div class="text-2xl font-black text-gray-900 mt-1">Bài KT</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </a>

        <a href="<?= base_url('teacher/practice-quizzes') ?>" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex items-center justify-between no-underline">
            <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Luyện Tập Markdown</div>
                <div class="text-2xl font-black text-gray-900 mt-1">Tự Do</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-code"></i>
            </div>
        </a>
    </div>

    <!-- Pending Teacher Approvals Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user text-indigo-600"></i>
                Giáo Viên Mới Đăng Ký Chờ Duyệt Trong Trường (<?= count($pending_teachers ?? []) ?>)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Họ Và Tên</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Ngày Đăng Ký</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($pending_teachers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400">
                                Không có tài khoản Giáo viên nào đang chờ phê duyệt trong trường.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_teachers as $t): ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900"><?= esc($t['full_name']) ?></td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800">@<?= esc($t['username']) ?></code></td>
                                <td><?= esc($t['email']) ?></td>
                                <td class="text-gray-500 text-xs"><?= date('H:i d/m/Y', strtotime($t['created_at'])) ?></td>
                                <td class="text-end pe-6 space-x-2">
                                    <a href="<?= base_url("teacher/approve-teacher/{$t['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white btn-sm rounded-lg font-bold shadow-sm">
                                        <i class="fa-solid fa-check me-1"></i> Phê Duyệt Giáo Viên
                                    </a>
                                    <a href="<?= base_url("teacher/reject-teacher/{$t['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg font-medium" onclick="return confirm('Bạn có chắc muốn từ chối tài khoản giáo viên này?')">
                                        <i class="fa-solid fa-xmark me-1"></i> Từ Chối
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Student Approvals Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-user-clock text-amber-500"></i>
                Học Sinh Mới Đăng Ký Chờ Duyệt Trong Trường (<?= count($pending_students) ?>)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Họ Và Tên</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Lớp Đăng Ký</th>
                        <th>Ngày Đăng Ký</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($pending_students)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-400">
                                Không có tài khoản Học sinh nào đang chờ phê duyệt.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_students as $st): ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900"><?= esc($st['full_name']) ?></td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800">@<?= esc($st['username']) ?></code></td>
                                <td><?= esc($st['email']) ?></td>
                                <td><span class="badge bg-indigo-100 text-indigo-800 font-semibold">Lớp <?= esc($st['class_name'] ?? 'Chưa chọn') ?></span></td>
                                <td class="text-gray-500 text-xs"><?= date('H:i d/m/Y', strtotime($st['created_at'])) ?></td>
                                <td class="text-end pe-6 space-x-2">
                                    <a href="<?= base_url("teacher/approve-student/{$st['id']}") ?>" class="btn btn-success btn-sm rounded-lg font-bold shadow-sm">
                                        <i class="fa-solid fa-check me-1"></i> Duyệt
                                    </a>
                                    <a href="<?= base_url("teacher/reject-student/{$st['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg font-medium" onclick="return confirm('Bạn có chắc muốn từ chối tài khoản này?')">
                                        <i class="fa-solid fa-xmark me-1"></i> Từ Chối
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <!-- Pending Password Reset Requests Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-100 bg-amber-50 flex items-center justify-between">
            <h3 class="font-bold text-amber-950 text-lg flex items-center gap-2 mb-0">
                <i class="fa-solid fa-key text-amber-600"></i>
                Yêu Cầu Đặt Lại Mật Khẩu Trong Trường (<?= count($pending_reset_requests ?? []) ?>)
            </h3>
            <span class="text-xs text-amber-700 font-semibold">GV cùng trường phê duyệt &amp; reset mật khẩu về 123456</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-amber-50/50 text-gray-700 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Họ Và Tên</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Vai Trò</th>
                        <th>Thời Gian Gửi Yêu Cầu</th>
                        <th class="text-end pe-6">Phê Duyệt / Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($pending_reset_requests)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-gray-400">
                                Không có yêu cầu đặt lại mật khẩu nào đang chờ.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_reset_requests as $req): ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900"><?= esc($req['full_name']) ?></td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800">@<?= esc($req['username']) ?></code></td>
                                <td>
                                    <span class="badge <?= $req['role'] === 'student' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800' ?> font-semibold capitalize">
                                        <?= esc($req['role']) ?>
                                    </span>
                                </td>
                                <td class="text-gray-500 text-xs"><?= date('H:i d/m/Y', strtotime($req['created_at'])) ?></td>
                                <td class="text-end pe-6 space-x-2">
                                    <form method="POST" action="<?= base_url("teacher/reset-user-password/{$req['id']}") ?>" style="display:inline;" onsubmit="return confirm('Xác nhận đặt lại mật khẩu cho <?= esc($req['full_name']) ?> thành 123456?');">
                                        <input type="hidden" name="new_password" value="123456">
                                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white btn-sm rounded-lg font-bold shadow-sm">
                                            <i class="fa-solid fa-key me-1"></i> Đặt Lại Mật Khẩu (123456)
                                        </button>
                                    </form>
                                    <a href="<?= base_url("teacher/cancel-password-request/{$req['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-lg font-medium" onclick="return confirm('Bạn có chắc muốn hủy yêu cầu này?')">
                                        <i class="fa-solid fa-xmark me-1"></i> Hủy
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Sessions List -->
    <?php if (!empty($active_sessions)): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-bold text-amber-900 text-lg mb-3 flex items-center gap-2">
                <i class="fa-solid fa-tower-broadcast text-amber-600 animate-pulse"></i>
                Lượt Thi Đang Mở Cho Học Sinh Tham Gia
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($active_sessions as $sess): ?>
                    <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm flex items-center justify-between">
                        <div>
                            <div class="font-bold text-gray-900"><?= esc($sess['session_title']) ?></div>
                            <div class="text-xs text-gray-500">Mã lượt: <code class="bg-gray-100 px-2 py-0.5 rounded font-bold text-indigo-600"><?= esc($sess['session_code']) ?></code></div>
                        </div>
                        <a href="<?= base_url("teacher/sessions/control/{$sess['id']}") ?>" class="btn btn-warning btn-sm font-bold rounded-lg px-3">
                            <i class="fa-solid fa-gears me-1"></i> Điều Khiển
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
