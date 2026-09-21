<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý Đề kiểm tra Điền vào chỗ trống</h1>
            <p class="text-gray-500 text-sm mt-0.5">Tạo bài kiểm tra từ ngân hàng câu hỏi, thiết lập thời gian và mở lượt thi thật cho lớp.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('teacher/fill-blank/questions') ?>" class="btn btn-outline-secondary rounded-xl font-semibold">
                <i class="fa-solid fa-folder-open me-1"></i> Ngân hàng Câu hỏi
            </a>
            <a href="<?= base_url('teacher/fill-blank/quizzes/create') ?>" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-4 py-2">
                <i class="fa-solid fa-plus me-1"></i> Tạo Đề kiểm tra Mới
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="ps-4">Tên bài kiểm tra</th>
                        <th>Lớp được gán</th>
                        <th>Thời gian làm bài</th>
                        <th>Số dạng bài</th>
                        <th>Thi thử</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($quizzes)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-400">Chưa có bài kiểm tra điền chỗ trống nào được tạo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quizzes as $q): ?>
                            <?php $qCount = count(json_decode($q['selected_question_ids'] ?: '[]', true)); ?>
                            <tr>
                                <td class="ps-4 fw-bold text-gray-900">
                                    <?= esc($q['title']) ?>
                                </td>
                                <td>
                                    <?php if ($q['class_name']): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-users me-1"></i> <?= esc($q['class_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 font-normal">Chưa gán</span>
                                    <?php endif; ?>
                                </td>
                                <td><i class="fa-regular fa-clock me-1 text-amber-500"></i> <?= $q['time_limit'] ?> phút</td>
                                <td><span class="badge bg-gray-100 text-gray-800 rounded-pill px-3 py-1"><?= $qCount ?> câu</span></td>
                                <td>
                                    <?php if ((int)($q['allow_mock'] ?? 1) === 1): ?>
                                        <span class="badge bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1">
                                            <i class="fa-solid fa-check me-1"></i> Cho phép
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-red-100 text-red-700 font-semibold px-2.5 py-1">
                                            <i class="fa-solid fa-lock me-1"></i> Đã khóa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success-subtle text-success">Hoạt động</span></td>
                                <td class="text-end pe-4 space-x-1">
                                    <?php if (!empty($q['class_id'])): ?>
                                        <form method="POST" action="<?= base_url('teacher/fill-blank/sessions/create/' . $q['id']) ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-sm bg-emerald-600 text-white hover:bg-emerald-700 font-semibold rounded-lg">
                                                <i class="fa-solid fa-play me-1"></i> Mở Lượt Thi Thật
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <a href="<?= base_url('teacher/fill-blank/quizzes/edit/' . $q['id']) ?>" class="btn btn-sm btn-outline-primary rounded-lg">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?= base_url('teacher/fill-blank/quizzes/delete/' . $q['id']) ?>" class="btn btn-sm btn-outline-danger rounded-lg" onclick="return confirm('Bạn chắc chắn muốn xoá bài kiểm tra này?');">
                                        <i class="fa-solid fa-trash"></i>
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
