<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Top Bar Control -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="badge bg-indigo-600 text-white font-mono text-sm px-3 py-1 rounded-lg">MÃ: <?= esc($session['session_code']) ?></span>
                <h1 class="text-2xl font-bold text-gray-900 mb-0"><?= esc($session['session_title']) ?></h1>
            </div>
            <p class="text-gray-500 text-sm mt-1 mb-0">
                Bài kiểm tra: <span class="font-semibold text-gray-800"><?= esc($test['title']) ?></span> | 
                Số câu bốc: <span class="font-bold text-indigo-600"><?= $session['num_questions'] ?> câu</span> (<?= $session['total_duration_sec'] ?> giây)
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <!-- Start / Close / Cancel session buttons -->
            <?php if ($session['status'] === 'waiting' || $session['status'] === 'in_progress'): ?>
                <button id="btnEndSession" onclick="endSession()" class="btn btn-danger bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md">
                    <i class="fa-solid fa-lock me-1"></i> ĐÓNG LƯỢT THI THẬT
                </button>
            <?php elseif ($session['status'] === 'completed'): ?>
                <span class="badge bg-gray-200 text-gray-800 font-bold text-sm px-3 py-2.5 rounded-xl flex items-center gap-1">
                    <i class="fa-solid fa-lock me-1"></i> LƯỢT THI ĐÃ ĐÓNG
                </span>
            <?php elseif ($session['status'] === 'cancelled'): ?>
                <span class="badge bg-red-100 text-red-800 border border-red-300 font-bold text-sm px-3 py-2.5 rounded-xl flex items-center gap-1">
                    <i class="fa-solid fa-ban me-1"></i> LƯỢT THI ĐÃ HỦY
                </span>
            <?php endif; ?>

            <?php if ($session['status'] === 'cancelled'): ?>
                <button onclick="restoreSession()" class="btn btn-outline-success font-bold rounded-xl px-4 py-2.5 shadow-sm">
                    <i class="fa-solid fa-rotate-left me-1"></i> Khôi Phục Lượt Thi
                </button>
            <?php else: ?>
                <button onclick="cancelSession()" class="btn btn-outline-danger font-bold rounded-xl px-3 py-2.5 shadow-sm" title="Hủy lượt thi (kết quả sẽ không tính vào điểm tích luỹ)">
                    <i class="fa-solid fa-ban me-1"></i> Hủy Lượt Thi
                </button>
            <?php endif; ?>

            <button type="button" class="btn btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#randomPickModal">
                <i class="fa-solid fa-dice text-yellow-300 text-lg"></i> Gọi Ngẫu Nhiên
            </button>

            <a href="<?= base_url("teacher/tests/accumulated-scores/{$test['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md flex items-center gap-2">
                <i class="fa-solid fa-chart-simple"></i> Bảng Điểm
            </a>
        </div>
    </div>

    <!-- Participants & Real-time Approval Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Real-time Student Approval Table (Polling) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-users-viewfinder text-indigo-600"></i>
                        Học Sinh Đang Tham Gia Lượt Thi (Duyệt Real-time)
                    </h3>
                    <span class="text-xs text-gray-500 font-mono"><i class="fa-solid fa-sync fa-spin me-1"></i> Tự động làm mới mỗi 2s</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="ps-6">Học Sinh</th>
                                <th>Duyệt Tham Gia</th>
                                <th>Trạng Thái Bài Làm</th>
                                <th>Điểm Lượt Thi</th>
                                <th class="text-end pe-6">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="participantsTableBody" class="divide-y divide-gray-100 text-sm">
                            <!-- Dynamically populated via AJAX polling -->
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400">
                                    Đang kết nối danh sách học sinh...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Class Roster & Mark Absent Panel (+0 / +3 Penalty) -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Default Class Roster Panel -->
            <div id="defaultRosterPanel" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-900 text-base flex items-center gap-2 mb-0">
                        <i class="fa-solid fa-user-xmark text-red-500"></i>
                        Danh Sách Lớp & Đánh Vắng
                    </h3>
                    <div class="flex items-center gap-1">
                        <button type="button" id="btnSwitchToRandom" onclick="showRandomRoster()" class="hidden btn btn-outline-emerald text-emerald-700 border-emerald-300 hover:bg-emerald-50 btn-sm rounded-lg text-xs font-bold py-1 px-2" title="Xem lại danh sách gọi ngẫu nhiên">
                            <i class="fa-solid fa-dice me-1"></i> DS Gọi
                        </button>
                        <button type="button" class="btn btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white btn-sm rounded-lg text-xs font-bold py-1 px-2.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#randomPickModal">
                            <i class="fa-solid fa-dice me-1"></i> Gọi
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-4">
                    Nếu học sinh không tham gia, bấm <strong class="text-red-600">"Đánh vắng"</strong>. Hệ thống sẽ cộng phạt: <span class="badge bg-red-100 text-red-800 font-bold">+0 đúng / +3 tổng làm</span> vào kho điểm tích luỹ.
                </p>

                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    <?php foreach ($class_students as $st): ?>
                        <div class="p-3 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-900 text-sm"><?= esc($st['full_name']) ?></div>
                                <div class="text-xs text-gray-500">@<?= esc($st['username']) ?></div>
                            </div>

                            <button type="button" onclick="markAbsent(<?= $session['id'] ?>, <?= $st['id'] ?>)" class="btn btn-outline-danger btn-sm rounded-lg font-bold text-xs">
                                <i class="fa-solid fa-user-slash me-1"></i> Đánh vắng
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Replaced Random Picked Roster Panel -->
            <div id="randomPickedPanel" class="hidden bg-white rounded-2xl shadow-sm border-2 border-emerald-500/40 p-5 ring-4 ring-emerald-500/10">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-900 text-base flex items-center gap-2 mb-0">
                        <i class="fa-solid fa-dice text-emerald-600 text-lg"></i>
                        <span id="randomPickedTitle">Danh Sách Gọi Ngẫu Nhiên</span>
                    </h3>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="openRandomModalAgain()" class="btn btn-outline-secondary btn-sm rounded-lg text-xs font-semibold py-1 px-2" title="Bốc lại danh sách mới">
                            <i class="fa-solid fa-rotate-right me-1"></i> Bốc lại
                        </button>
                        <button type="button" onclick="restoreDefaultRoster()" class="btn btn-outline-secondary btn-sm rounded-lg text-xs font-semibold py-1 px-2" title="Xem danh sách cả lớp">
                            <i class="fa-solid fa-users me-1"></i> Cả lớp
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-3">
                    Học sinh được gọi cho lượt thi này. Nếu em nào không tham gia, bấm <strong class="text-red-600">"Đánh vắng"</strong> (+0/+3).
                </p>

                <div id="randomPickedList" class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Random Pick Modal -->
<div class="modal fade" id="randomPickModal" tabindex="-1" aria-labelledby="randomPickModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-xl overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-to-r from-emerald-600 to-teal-600 text-white border-0 px-6 py-4">
                <h5 class="modal-title font-bold text-lg flex items-center gap-2" id="randomPickModalLabel">
                    <i class="fa-solid fa-dice text-yellow-300 text-xl"></i>
                    Gọi Ngẫu Nhiên Học Sinh Cho Lượt Thi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-6 py-5">
                <!-- Input Section -->
                <div id="pickInputSection">
                    <p class="text-gray-600 text-sm mb-4">
                        <i class="fa-solid fa-circle-info text-indigo-500 me-1"></i>
                        Hệ thống sẽ <strong>lấy dần các nhóm học sinh có ít lượt kiểm tra thật nhất</strong> 
                        (đảm bảo danh sách chọn có ít nhất <code>Số lượng + 3</code> học sinh), sau đó 
                        <strong>chọn ngẫu nhiên 3 học sinh bên ngoài</strong> (nếu còn) vào danh sách chọn.
                    </p>

                    <div class="flex items-end gap-3">
                        <div class="flex-grow">
                            <label for="pickQuantity" class="block text-sm font-semibold text-gray-700 mb-1">
                                Số lượng học sinh muốn gọi <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="pickQuantity" min="1" max="<?= count($class_students) ?>" value="1" 
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-lg font-bold text-center"
                                   placeholder="Nhập số lượng...">
                        </div>
                        <button type="button" id="btnRandomPick" onclick="doRandomPick()" 
                                class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2 whitespace-nowrap">
                            <i class="fa-solid fa-shuffle"></i> Bốc Ngẫu Nhiên
                        </button>
                    </div>
                </div>

                <!-- Loading spinner -->
                <div id="pickLoading" class="hidden text-center py-8">
                    <div class="inline-flex items-center gap-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-4 border-emerald-200 border-t-emerald-600"></div>
                        <span class="text-gray-600 font-semibold">Đang bốc ngẫu nhiên...</span>
                    </div>
                </div>

                <!-- Results Section -->
                <div id="pickResults" class="hidden mt-5">
                    <!-- Stats Bar -->
                    <div id="pickStats" class="grid grid-cols-3 gap-3 mb-5">
                        <div class="bg-indigo-50 rounded-xl p-3 text-center border border-indigo-100">
                            <div class="text-xs text-indigo-500 font-semibold uppercase">HS Ngoài Thêm Vào</div>
                            <div class="text-2xl font-black text-indigo-700 mt-1" id="statExtra">-</div>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3 text-center border border-emerald-100">
                            <div class="text-xs text-emerald-500 font-semibold uppercase">Tổng DS Chọn</div>
                            <div class="text-2xl font-black text-emerald-700 mt-1" id="statPool">-</div>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-3 text-center border border-amber-100">
                            <div class="text-xs text-amber-500 font-semibold uppercase">Tổng HS Lớp</div>
                            <div class="text-2xl font-black text-amber-700 mt-1" id="statTotal">-</div>
                        </div>
                    </div>

                    <!-- Picked Students List -->
                    <h6 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-users text-emerald-600"></i>
                        Danh sách học sinh được gọi ngẫu nhiên:
                    </h6>
                    <div id="pickedList" class="space-y-2 max-h-[300px] overflow-y-auto pr-1"></div>

                    <!-- Action buttons -->
                    <div class="mt-5 pt-4 border-t border-gray-200 flex flex-wrap gap-2 justify-between items-center">
                        <button type="button" onclick="doRandomPick()" 
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl shadow-sm transition flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-rotate-right"></i> Bốc Lại
                        </button>
                        <button type="button" onclick="confirmPickedStudents()" 
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-check-circle"></i> Xác Nhận & Đưa Vào Lượt Thi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Polling JavaScript -->
<script>
const sessionId = <?= $session['id'] ?>;

function fetchParticipants() {
    fetch('<?= base_url("api/session-participants/") ?>' + sessionId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderParticipants(data.participants);
            }
        })
        .catch(err => console.error('Polling error:', err));
}

function renderParticipants(participants) {
    const tbody = document.getElementById('participantsTableBody');
    if (participants.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-gray-400">Chưa có học sinh nào bấm "Tham gia". Màn hình sẽ tự động cập nhật khi có học sinh vào.</td></tr>';
        return;
    }

    // Sort: pending students first, then newest joined first
    participants.sort((a, b) => {
        if (a.approval_status === 'pending' && b.approval_status !== 'pending') return -1;
        if (a.approval_status !== 'pending' && b.approval_status === 'pending') return 1;
        return b.id - a.id; // newest first (higher id = joined later)
    });

    let html = '';
    participants.forEach(p => {
        let approvalBadge = '';
        if (p.approval_status === 'pending') {
            approvalBadge = `<button onclick="approveStudent(${p.id})" class="btn btn-success btn-sm rounded-lg font-bold shadow-sm animate-bounce"><i class="fa-solid fa-check me-1"></i> PHÊ DUYỆT</button>`;
        } else if (p.approval_status === 'approved') {
            approvalBadge = '<span class="badge bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1"><i class="fa-solid fa-circle-check me-1"></i> Đã duyệt</span>';
        } else if (p.approval_status === 'rejected') {
            approvalBadge = '<span class="badge bg-red-100 text-red-700 font-bold px-2.5 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Đã từ chối</span>';
        } else if (p.approval_status === 'absent') {
            approvalBadge = '<span class="badge bg-red-600 text-white font-bold px-2.5 py-1"><i class="fa-solid fa-user-xmark me-1"></i> ĐÁNH VẮNG (+0/+3)</span>';
        }

        const isCancelled = (p.is_cancelled == 1);

        let testStatusBadge = '';
        if (isCancelled) {
            testStatusBadge = `<span class="badge bg-red-100 text-red-700 border border-red-200 font-bold px-2 py-1"><i class="fa-solid fa-ban me-1"></i> Đã hủy kết quả</span>`;
            if (p.cancelled_reason) {
                testStatusBadge += `<div class="text-[11px] text-red-500 italic mt-0.5">${p.cancelled_reason}</div>`;
            }
        } else if (p.test_status === 'waiting_approval') {
            testStatusBadge = '<span class="text-amber-600 font-medium">Chờ duyệt</span>';
        } else if (p.test_status === 'ready') {
            testStatusBadge = '<span class="text-indigo-600 font-medium">Sẵn sàng làm bài</span>';
        } else if (p.test_status === 'in_test') {
            testStatusBadge = '<span class="badge bg-indigo-600 text-white font-semibold">Đang làm bài</span>';
        } else if (p.test_status === 'submitted') {
            testStatusBadge = '<span class="badge bg-emerald-600 text-white font-bold">Đã nộp bài</span>';
        } else if (p.test_status === 'absent') {
            testStatusBadge = '<span class="badge bg-red-100 text-red-800">Vắng mặt</span>';
        }

        let scoreDisplay = '-';
        if (isCancelled) {
            if (p.score_total > 0) {
                scoreDisplay = `<span class="line-through text-gray-400 font-semibold text-sm">${p.score_correct} / ${p.score_total}</span> <div class="text-[11px] text-red-600 font-bold mt-0.5">Không tính tích luỹ</div>`;
            } else if (p.approval_status === 'absent') {
                scoreDisplay = `<span class="line-through text-gray-400 text-xs">Vắng (+0/+3)</span> <div class="text-[11px] text-red-600 font-bold mt-0.5">Đã hủy phạt</div>`;
            } else {
                scoreDisplay = `<span class="text-red-500 text-xs font-semibold">Đã hủy</span>`;
            }
        } else if (p.score_total > 0 && p.test_status === 'submitted') {
            scoreDisplay = `<span class="text-base font-black text-emerald-600">${p.score_correct} / ${p.score_total} câu đúng</span>`;
        }

        let actionButtons = '';
        if (isCancelled) {
            actionButtons = `<button onclick="restoreResult(${p.id})" class="btn btn-outline-success btn-sm rounded-lg text-xs font-bold"><i class="fa-solid fa-rotate-left me-1"></i> Khôi phục</button>`;
        } else {
            if (p.test_status === 'submitted' || p.approval_status === 'absent') {
                actionButtons += `<button onclick="cancelResult(${p.id})" class="btn btn-outline-danger btn-sm rounded-lg text-xs font-bold me-1" title="Hủy kết quả"><i class="fa-solid fa-ban me-1"></i> Hủy kết quả</button>`;
            }
            if (p.approval_status !== 'absent') {
                actionButtons += `<button onclick="markAbsent(${sessionId}, ${p.student_id})" class="btn btn-outline-danger btn-sm rounded-lg text-xs font-medium"><i class="fa-solid fa-user-slash me-1"></i> Đánh vắng</button>`;
            }
        }

        html += `
            <tr class="${isCancelled ? 'bg-red-50/40' : ''}">
                <td class="ps-6">
                    <div class="font-bold text-gray-900">${p.student_name}</div>
                    <div class="text-xs text-gray-500">@${p.username}</div>
                </td>
                <td>${approvalBadge}</td>
                <td>${testStatusBadge}</td>
                <td>${scoreDisplay}</td>
                <td class="text-end pe-6">
                    ${actionButtons}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function approveStudent(participantId) {
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/approve/") ?>' + participantId, {
        method: 'POST',
        body: formData
    }).then(() => fetchParticipants());
}

function rejectStudent(participantId) {
    if (!confirm('Bạn có chắc muốn TỪ CHỐI học sinh này không được vào thi?')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/reject/") ?>' + participantId, {
        method: 'POST',
        body: formData
    }).then(() => fetchParticipants());
}

function markAbsent(sessId, studentId) {
    if (!confirm('Bạn có chắc muốn ĐÁNH VẮNG học sinh này? Hệ thống sẽ cộng phạt (+0 đúng / +3 làm) vào điểm tích luỹ.')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/absent/") ?>' + sessId + '/' + studentId, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        alert(result.message);
        fetchParticipants();
    });
}

function cancelResult(participantId) {
    const reason = prompt('Bạn có chắc muốn HỦY BỎ kết quả lượt thi của học sinh này? Kết quả sẽ KHÔNG được tính vào điểm tích luỹ.\n\nNhập lý do hủy (tùy chọn):', 'Lỗi thiết bị / Làm lại');
    if (reason === null) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('reason', reason);

    fetch('<?= base_url("teacher/sessions/cancel-result/") ?>' + participantId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        alert(result.message);
        fetchParticipants();
    });
}

function restoreResult(participantId) {
    if (!confirm('Bạn có chắc muốn KHÔI PHỤC kết quả lượt thi của học sinh này? Kết quả sẽ được tính lại vào điểm tích luỹ.')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/restore-result/") ?>' + participantId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        alert(result.message);
        fetchParticipants();
    });
}

function cancelSession() {
    if (!confirm('Bạn có chắc muốn HỦY TOÀN BỘ lượt thi này? Tất cả kết quả của học sinh trong lượt thi sẽ KHÔNG được tính vào điểm tích luỹ.')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/cancel/") ?>' + sessionId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        alert(result.message);
        location.reload();
    });
}

function restoreSession() {
    if (!confirm('Bạn có chắc muốn KHÔI PHỤC lượt thi này? Tất cả kết quả hợp lệ sẽ được tính lại vào điểm tích luỹ.')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/restore/") ?>' + sessionId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        alert(result.message);
        location.reload();
    });
}

function startSession() {
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/start/") ?>' + sessionId, {
        method: 'POST',
        body: formData
    }).then(() => location.reload());
}

function endSession() {
    if (!confirm('Bạn có chắc muốn KẾT THÚC lượt thi này?')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/end/") ?>' + sessionId, {
        method: 'POST',
        body: formData
    }).then(() => location.reload());
}

// Start AJAX polling every 2 seconds
setInterval(fetchParticipants, 2000);
fetchParticipants();

// ==========================================
// RANDOM STUDENT PICKER (GỌI NGẪU NHIÊN HS)
// ==========================================
let currentPickedStudents = [];

function doRandomPick() {
    const quantity = document.getElementById('pickQuantity').value;
    if (!quantity || quantity < 1) {
        alert('Vui lòng nhập số lượng học sinh hợp lệ!');
        return;
    }

    document.getElementById('pickLoading').classList.remove('hidden');
    document.getElementById('pickResults').classList.add('hidden');
    document.getElementById('btnRandomPick').disabled = true;

    fetch('<?= base_url("teacher/tests/random-pick/{$test['id']}") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            quantity: quantity,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('pickLoading').classList.add('hidden');
        document.getElementById('btnRandomPick').disabled = false;

        if (data.status === 'error') {
            alert(data.message);
            return;
        }

        currentPickedStudents = data.picked || [];

        // Fill stats
        const statExtraEl = document.getElementById('statExtra');
        if (statExtraEl) {
            statExtraEl.textContent = '+' + (data.outside_picked_count || 0) + ' HS';
        }
        document.getElementById('statPool').textContent = data.pool_size + ' HS';
        document.getElementById('statTotal').textContent = data.total_class + ' HS';

        // Build picked list
        const listEl = document.getElementById('pickedList');
        listEl.innerHTML = '';

        if (currentPickedStudents.length === 0) {
            listEl.innerHTML = '<div class="text-center text-gray-400 py-4">Không có học sinh nào trong danh sách.</div>';
        } else {
            currentPickedStudents.forEach(function(student, index) {
                const card = document.createElement('div');
                card.className = 'flex items-center gap-3 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl px-4 py-3 shadow-xs';

                const orderBadge = '<span class="w-7 h-7 rounded-full bg-emerald-600 text-white font-black inline-flex items-center justify-center shadow-xs text-xs flex-shrink-0">' + (index + 1) + '</span>';

                card.innerHTML = orderBadge +
                    '<div class="flex-grow">' +
                        '<div class="font-bold text-gray-900">' + escHtml(student.full_name) + '</div>' +
                        '<code class="bg-white/70 px-2 py-0.5 rounded text-emerald-700 font-mono text-xs">@' + escHtml(student.username) + '</code>' +
                    '</div>' +
                    '<div class="text-right flex-shrink-0">' +
                        '<span class="text-xs font-semibold text-gray-500">Đã thi</span>' +
                        '<div class="text-base font-black text-indigo-700">' + student.real_count + ' <span class="text-xs font-normal text-gray-400">lượt</span></div>' +
                    '</div>';

                listEl.appendChild(card);
            });

            if (data.picked.length < data.requested) {
                const note = document.createElement('div');
                note.className = 'text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2 mt-3 font-semibold';
                note.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Đã chọn toàn bộ ' + data.picked.length + '/' + data.requested + ' học sinh có trong lớp.';
                listEl.appendChild(note);
            }
        }

        document.getElementById('pickResults').classList.remove('hidden');
    })
    .catch(err => {
        document.getElementById('pickLoading').classList.add('hidden');
        document.getElementById('btnRandomPick').disabled = false;
        alert('Có lỗi xảy ra, vui lòng thử lại.');
        console.error(err);
    });
}

function confirmPickedStudents() {
    if (!currentPickedStudents || currentPickedStudents.length === 0) {
        alert('Vui lòng bốc ngẫu nhiên trước khi xác nhận!');
        return;
    }

    // Save to sessionStorage
    sessionStorage.setItem('random_picked_' + sessionId, JSON.stringify(currentPickedStudents));

    // Hide modal
    const modalEl = document.getElementById('randomPickModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }

    // Replace right panel
    applyRandomPickedRoster(currentPickedStudents);
}

function applyRandomPickedRoster(students) {
    const defaultPanel = document.getElementById('defaultRosterPanel');
    const randomPanel = document.getElementById('randomPickedPanel');
    const listEl = document.getElementById('randomPickedList');
    const titleEl = document.getElementById('randomPickedTitle');
    const btnSwitchToRandom = document.getElementById('btnSwitchToRandom');

    if (defaultPanel) defaultPanel.classList.add('hidden');
    if (randomPanel) randomPanel.classList.remove('hidden');
    if (btnSwitchToRandom) btnSwitchToRandom.classList.add('hidden');
    if (titleEl) titleEl.textContent = `Học Sinh Được Gọi (${students.length})`;

    if (!listEl) return;
    listEl.innerHTML = '';

    students.forEach((st, idx) => {
        const item = document.createElement('div');
        item.className = 'p-3 rounded-xl border border-emerald-100 bg-gradient-to-r from-emerald-50/70 to-teal-50/70 flex items-center justify-between gap-2 shadow-xs';

        item.innerHTML = `
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold inline-flex items-center justify-center text-xs flex-shrink-0 shadow-xs">
                    ${idx + 1}
                </span>
                <div class="min-w-0">
                    <div class="font-bold text-gray-900 text-sm truncate">
                        ${escHtml(st.full_name)}
                    </div>
                    <div class="text-[11px] text-gray-500 font-mono">
                        @${escHtml(st.username)} • <span class="text-indigo-600 font-semibold">${st.real_count} lượt</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="markAbsent(${sessionId}, ${st.id})" class="btn btn-outline-danger btn-sm rounded-lg font-bold text-xs flex-shrink-0 px-2 py-1" title="Đánh vắng (+0/+3)">
                <i class="fa-solid fa-user-slash me-1"></i> Đánh vắng
            </button>
        `;
        listEl.appendChild(item);
    });
}

function restoreDefaultRoster() {
    const defaultPanel = document.getElementById('defaultRosterPanel');
    const randomPanel = document.getElementById('randomPickedPanel');
    const btnSwitchToRandom = document.getElementById('btnSwitchToRandom');

    if (defaultPanel) defaultPanel.classList.remove('hidden');
    if (randomPanel) randomPanel.classList.add('hidden');
    if (btnSwitchToRandom && currentPickedStudents.length > 0) {
        btnSwitchToRandom.classList.remove('hidden');
    }
}

function showRandomRoster() {
    if (currentPickedStudents.length > 0) {
        applyRandomPickedRoster(currentPickedStudents);
    }
}

function openRandomModalAgain() {
    const modalEl = document.getElementById('randomPickModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// Restore saved picked list from sessionStorage on page load
document.addEventListener('DOMContentLoaded', () => {
    const saved = sessionStorage.getItem('random_picked_' + sessionId);
    if (saved) {
        try {
            const list = JSON.parse(saved);
            if (Array.isArray(list) && list.length > 0) {
                currentPickedStudents = list;
                applyRandomPickedRoster(list);
            }
        } catch (e) {
            console.error('Error restoring picked list:', e);
        }
    }
});
</script>
<?= $this->endSection() ?>
