<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bảng điều khiển Quản trị (Admin)</h1>
            <p class="text-gray-500 text-sm">Quản lý Trường học, Phê duyệt Giáo viên và Tổng quan Hệ thống</p>
        </div>
        <a href="<?= base_url('admin/schools') ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl px-4 py-2">
            <i class="fa-solid fa-school me-1"></i> Quản lý Trường Học
        </a>
    </div>

    <!-- Overview Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-school"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900"><?= $schools_count ?></div>
                <div class="text-xs text-gray-500">Trường Học</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900"><?= $teachers_count ?></div>
                <div class="text-xs text-gray-500">Giáo Viên</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900"><?= $students_count ?></div>
                <div class="text-xs text-gray-500">Học Sinh</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900"><?= $questions_count ?></div>
                <div class="text-xs text-gray-500">Câu Hỏi Global</div>
            </div>
        </div>
    </div>

    <!-- Pending Teacher Approvals Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-user-clock text-amber-500"></i>
                Danh sách Giáo viên Chờ Phê Duyệt (<?= count($pending_teachers) ?>)
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Họ và tên</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Trường Học</th>
                        <th>Ngày Đăng Ký</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($pending_teachers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-400"></i>
                                <p class="mb-0">Hiện tại không có yêu cầu phê duyệt Giáo viên nào.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_teachers as $t): ?>
                            <tr>
                                <td class="ps-6 font-semibold text-gray-900"><?= esc($t['full_name']) ?></td>
                                <td><code class="bg-gray-100 px-2 py-1 rounded text-gray-800"><?= esc($t['username']) ?></code></td>
                                <td><?= esc($t['email']) ?></td>
                                <td><span class="badge bg-indigo-50 text-indigo-700 font-medium"><?= esc($t['school_name'] ?? 'Chưa xác định') ?></span></td>
                                <td class="text-gray-500"><?= date('H:i d/m/Y', strtotime($t['created_at'])) ?></td>
                                <td class="text-end pe-6 space-x-2">
                                    <a href="<?= base_url("admin/approve-teacher/{$t['id']}") ?>" class="btn btn-success btn-sm rounded-lg font-medium">
                                        <i class="fa-solid fa-check me-1"></i> Duyệt
                                    </a>
                                    <a href="<?= base_url("admin/reject-teacher/{$t['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg font-medium" onclick="return confirm('Bạn có chắc muốn từ chối tài khoản này?')">
                                        <i class="fa-solid fa-xmark me-1"></i> Từ chối
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
