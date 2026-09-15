<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Welcome Card -->
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-3xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <span class="badge bg-white/20 text-white font-semibold text-xs px-3 py-1 rounded-full mb-2">Học Sinh</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold mb-1">Xin chào, <?= esc(session()->get('full_name')) ?>!</h1>
                <p class="text-indigo-100 text-sm mb-0">Hệ thống Kiểm tra Tích luỹ & Luyện tập Tự do</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-yellow-300 text-3xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
    </div>

    <!-- Active Real Test Sessions (Live Now) -->
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-500 animate-bounce"></i>
            Lượt Thi Đang Mở (Tham Gia Ngay Trên Lớp)
        </h2>

        <?php if (empty($active_sessions)): ?>
            <div class="bg-white rounded-2xl p-6 text-center text-gray-400 border border-gray-100 shadow-sm">
                <i class="fa-solid fa-clock text-3xl mb-2 text-indigo-300"></i>
                <p class="mb-0 text-sm">Hiện tại chưa có Lượt thi thực tế nào mở trong Lớp của bạn.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($active_sessions as $sess): ?>
                    <?php $myPart = $sess['my_participant'] ?? null; ?>
                    <div class="bg-white p-5 rounded-2xl border-2 border-indigo-200 shadow-md hover:shadow-lg transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <div class="font-bold text-gray-900 text-lg mb-1"><?= esc($sess['session_title']) ?></div>
                            <div class="text-xs text-gray-500 mb-2">
                                GV: <span class="font-semibold text-gray-700"><?= esc($sess['teacher_name']) ?></span> | 
                                Mã: <code class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-bold"><?= esc($sess['session_code']) ?></code>
                            </div>
                            <span class="badge bg-indigo-50 text-indigo-700 font-semibold me-1"><i class="fa-solid fa-layer-group me-1"></i> <?= esc($sess['test_title']) ?></span>
                        </div>

                        <div>
                            <?php if ($myPart && ($myPart['test_status'] === 'submitted' || $myPart['test_status'] === 'timed_out')): ?>
                                <div class="text-right">
                                    <span class="badge bg-emerald-100 text-emerald-800 font-bold px-3 py-1.5 text-xs mb-2 block">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đã nộp bài (<?= number_format($myPart['score_base10'], 1) ?>đ)
                                    </span>
                                    <a href="<?= base_url("student/exam-result/{$myPart['id']}") ?>" class="btn btn-outline-emerald border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold rounded-xl btn-sm px-3 py-1.5 no-underline">
                                        <i class="fa-solid fa-eye me-1"></i> Xem Kết Quả
                                    </a>
                                </div>
                            <?php elseif ($myPart && $myPart['approval_status'] === 'absent'): ?>
                                <span class="badge bg-red-600 text-white font-bold px-3 py-2 rounded-xl text-xs">
                                    <i class="fa-solid fa-user-xmark me-1"></i> ĐÃ BỊ ĐÁNH VẮNG
                                </span>
                            <?php elseif ($myPart && $myPart['test_status'] === 'in_test'): ?>
                                <a href="<?= base_url("student/exam/{$myPart['id']}") ?>" class="btn btn-warning bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl px-4 py-3 shadow-md no-underline">
                                    <i class="fa-solid fa-play me-1"></i> Tiếp Tục Làm Bài
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url("student/join-session/{$sess['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 py-3 shadow-md no-underline">
                                    Tham Gia <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Accumulated Scores & Mock Practice Tests -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-indigo-600"></i>
                Bảng Điểm Tích Luỹ & Luyện Tập Thử
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Bài Kiểm Tra Tích Luỹ</th>
                        <th>Lớp Học</th>
                        <th>Tổng Câu Đúng</th>
                        <th>Tổng Câu Đã Làm</th>
                        <th>ĐIỂM TÍCH LUỸ (HỆ 10)</th>
                        <th class="text-end pe-6">Luyện Tập</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($accumulated_scores)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                Bạn chưa đăng ký lớp học nào hoặc chưa có bài kiểm tra tích luỹ.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accumulated_scores as $t): ?>
                            <?php $acc = $t['accumulated']; ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900 text-base"><?= esc($t['title']) ?></td>
                                <td><span class="badge bg-indigo-50 text-indigo-700 font-semibold">Lớp <?= esc($t['class_name']) ?></span></td>
                                <td class="font-semibold text-emerald-600"><?= $acc ? $acc['total_correct'] : 0 ?> câu đúng</td>
                                <td class="font-semibold text-gray-700"><?= $acc ? $acc['total_attempted'] : 0 ?> câu làm</td>
                                <td>
                                    <?php if ($acc && $acc['total_attempted'] > 0): ?>
                                        <span class="text-xl font-black text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-100">
                                            <?= number_format($acc['accumulated_gpa'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Chưa có điểm tích luỹ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-6">
                                    <a href="<?= base_url("student/mock-test/{$t['id']}") ?>" class="btn btn-outline-indigo btn-sm rounded-xl font-bold">
                                        <i class="fa-solid fa-gamepad me-1"></i> Thi Thử Ngay
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <!-- Active Real Test Sessions for Fill-in-the-Blanks -->
    <?php if (!empty($fill_blank_active_sessions)): ?>
        <div class="space-y-3">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-teal-600 animate-bounce"></i>
                Lượt Thi Thật Điền Vào Chỗ Trống (Đang Mở Cần Phê Duyệt)
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($fill_blank_active_sessions as $fbSess): ?>
                    <?php $myFbPart = $fbSess['my_participant'] ?? null; ?>
                    <div class="bg-white p-5 rounded-2xl border-2 border-teal-200 shadow-md hover:shadow-lg transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <div class="font-bold text-gray-900 text-lg mb-1"><?= esc($fbSess['quiz_title']) ?></div>
                            <div class="text-xs text-gray-500 mb-2">
                                GV: <span class="font-semibold text-gray-700"><?= esc($fbSess['teacher_name']) ?></span> | 
                                Lớp: <span class="font-semibold text-gray-700"><?= esc($fbSess['class_name']) ?></span> | 
                                Mã: <code class="bg-teal-50 text-teal-700 px-2 py-0.5 rounded font-bold"><?= esc($fbSess['session_code']) ?></code>
                            </div>
                            <span class="badge bg-teal-50 text-teal-700 font-semibold"><i class="fa-regular fa-clock me-1"></i> <?= esc($fbSess['time_limit']) ?> phút</span>
                        </div>

                        <div>
                            <?php if ($myFbPart && ($myFbPart['test_status'] === 'submitted' || $myFbPart['test_status'] === 'timed_out')): ?>
                                <span class="badge bg-emerald-100 text-emerald-800 font-bold px-3 py-2 rounded-xl text-xs">
                                    <i class="fa-solid fa-circle-check me-1"></i> Đã nộp bài (<?= number_format($myFbPart['score_base10'], 1) ?>đ)
                                </span>
                            <?php elseif ($myFbPart && $myFbPart['approval_status'] === 'approved'): ?>
                                <a href="<?= base_url("student/fill-blank/exam/{$myFbPart['id']}") ?>" class="btn bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md no-underline">
                                    <i class="fa-solid fa-play me-1"></i> Vào Làm Bài Ngay
                                </a>
                            <?php elseif ($myFbPart && $myFbPart['approval_status'] === 'pending'): ?>
                                <a href="<?= base_url("student/fill-blank/waiting-room/{$myFbPart['id']}") ?>" class="btn bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl px-4 py-2.5 shadow-md no-underline">
                                    <i class="fa-solid fa-hourglass-half me-1"></i> Vào Phòng Chờ Duyệt
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url("student/fill-blank/join-session/{$fbSess['id']}") ?>" class="btn bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md no-underline">
                                    Đăng Ký Thi <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Fill-in-the-blank Free Practice Quizzes -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-pen-nib text-teal-600"></i>
                Luyện Tập Trắc Nghiệm Điền Vào Chỗ Trống (Tự Do)
            </h3>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (empty($fill_blank_quizzes)): ?>
                <div class="col-span-2 text-center py-6 text-gray-400">
                    Chưa có đề trắc nghiệm điền chỗ trống nào trong trường của bạn.
                </div>
            <?php else: ?>
                <?php foreach ($fill_blank_quizzes as $fbq): ?>
                    <div class="border border-gray-200 rounded-2xl p-4 hover:border-teal-300 hover:shadow-md transition flex flex-col justify-between space-y-3">
                        <div>
                            <span class="badge bg-teal-100 text-teal-800 font-semibold px-2.5 py-1 rounded-lg text-xs mb-1">
                                <?= esc($fbq['subject'] ?: 'Tin học') ?> - <?= $fbq['time_limit'] ?> phút
                            </span>
                            <h4 class="font-bold text-gray-900 text-base mb-1"><?= esc($fbq['title']) ?></h4>
                            <p class="text-xs text-gray-500 mb-0">Giáo viên: <?= esc($fbq['teacher_name'] ?? 'Giáo viên') ?></p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-t border-gray-100">
                            <span class="text-xs text-gray-400"><i class="fa-solid fa-infinity text-teal-500 me-1"></i> Thi thử tự do</span>
                            <a href="<?= base_url("student/fill-blank/mock/{$fbq['id']}") ?>" class="btn bg-teal-600 hover:bg-teal-700 text-white font-bold btn-sm rounded-xl px-3 py-1.5 no-underline">
                                <i class="fa-solid fa-gamepad me-1"></i> Làm Bài Thử
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Markdown Practice Quizzes Available -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-file-code text-purple-600"></i>
                Đề Trắc Nghiệm Luyện Tập Tự Do (Markdown)
            </h3>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (empty($practice_quizzes)): ?>
                <div class="col-span-2 text-center py-6 text-gray-400">
                    Chưa có đề trắc nghiệm luyện tập tự do nào trong trường của bạn.
                </div>
            <?php else: ?>
                <?php foreach ($practice_quizzes as $pq): ?>
                    <div class="border border-gray-200 rounded-2xl p-4 hover:border-purple-300 hover:shadow-md transition flex flex-col justify-between space-y-3">
                        <div>
                            <span class="badge bg-purple-100 text-purple-700 font-semibold px-2.5 py-1 rounded-lg text-xs mb-1">
                                <?= esc($pq['subject']) ?> - Khối <?= esc($pq['grade_level']) ?>
                            </span>
                            <h4 class="font-bold text-gray-900 text-base mb-1"><?= esc($pq['title']) ?></h4>
                            <p class="text-xs text-gray-500 mb-0">Giáo viên: <?= esc($pq['teacher_name'] ?? 'Giáo viên') ?></p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-t border-gray-100">
                            <span class="text-xs text-gray-400"><i class="fa-solid fa-infinity text-purple-500 me-1"></i> Làm bài tự do</span>
                            <a href="<?= base_url("practice/{$pq['slug']}") ?>" target="_blank" class="btn bg-purple-600 hover:bg-purple-700 text-white font-bold btn-sm rounded-xl px-3 py-1.5 no-underline">
                                <i class="fa-solid fa-pen-nib me-1"></i> Vào Luyện Tập
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
