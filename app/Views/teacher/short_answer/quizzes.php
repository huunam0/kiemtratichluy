<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Đề Kiểm Tra Trả Lời Ngắn</h1>
            <p class="text-gray-500 text-sm mt-0.5">Quản lý bài kiểm tra, mở/đóng bài trực tiếp bằng nút bấm, cấu hình hệ số phạt nộp chậm và đảo câu hỏi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('teacher/short-answer/questions') ?>" class="btn btn-outline-secondary rounded-xl font-semibold">
                <i class="fa-solid fa-database me-1"></i> Ngân hàng Câu hỏi
            </a>
            <a href="<?= base_url('teacher/short-answer/quizzes/create') ?>" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-4 py-2">
                <i class="fa-solid fa-plus me-1"></i> Tạo Đề kiểm tra mới
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Tên bài kiểm tra</th>
                        <th>Lớp học</th>
                        <th>Thời gian</th>
                        <th>Số câu / Tổng điểm</th>
                        <th>Đảo câu</th>
                        <th>Hệ số phạt</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($quizzes)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-gray-400">Chưa có đề kiểm tra trả lời ngắn nào. Hãy bấm "Tạo Đề kiểm tra mới" để bắt đầu!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quizzes as $qz): ?>
                            <tr>
                                <td class="ps-4 font-mono text-sm text-gray-500">#<?= $qz['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-gray-900"><?= esc($qz['title']) ?></div>
                                    <?php if ((int)($qz['allow_mock'] ?? 1) === 1): ?>
                                        <span class="badge bg-teal-50 text-teal-700 border border-teal-200 text-xs">
                                            <i class="fa-solid fa-gamepad me-1"></i> Cho phép thi thử
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-gray-100 text-gray-500 text-xs">Khóa thi thử</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1">
                                        <?= esc($qz['class_name'] ?? 'Chưa gán') ?>
                                    </span>
                                </td>
                                <td class="text-gray-700 font-semibold"><?= (int)$qz['time_limit'] ?> phút</td>
                                <td>
                                    <div class="font-semibold text-gray-900"><?= (int)$qz['question_count'] ?> câu</div>
                                    <div class="text-xs text-gray-500"><?= number_format((float)$qz['total_points'], 2) ?> điểm</div>
                                </td>
                                <td>
                                    <?php if ((int)$qz['shuffle_questions'] === 1): ?>
                                        <span class="text-success text-xs font-semibold"><i class="fa-solid fa-shuffle me-1"></i> Có</span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs"><i class="fa-solid fa-arrow-down-1-9 me-1"></i> Thứ tự cố định</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((float)$qz['penalty_coefficient'] > 0): ?>
                                        <span class="badge bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                                            -<?= (float)$qz['penalty_coefficient'] ?> đ/bạn
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Không phạt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)$qz['is_open'] === 1): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 animate-pulse">
                                            <i class="fa-solid fa-door-open me-1"></i> Đang mở
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-gray-100 text-gray-600 rounded-pill px-3 py-1.5">
                                            <i class="fa-solid fa-lock me-1"></i> Đang đóng
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= base_url('teacher/short-answer/quizzes/control/' . $qz['id']) ?>" class="btn btn-sm <?= (int)$qz['is_open'] === 1 ? 'btn-success' : 'btn-outline-primary' ?> rounded-lg font-semibold">
                                            <i class="fa-solid fa-sliders me-1"></i> Điều khiển & Điểm
                                        </a>
                                        <a href="<?= base_url('teacher/short-answer/quizzes/edit/' . $qz['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-lg" title="Chỉnh sửa cấu hình & câu hỏi">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= base_url('teacher/short-answer/quizzes/delete/' . $qz['id']) ?>" class="btn btn-sm btn-outline-danger rounded-lg" onclick="return confirm('Bạn có chắc chắn muốn xóa bài kiểm tra này?');" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
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