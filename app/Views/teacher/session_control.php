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
            <!-- Start / Close session buttons -->
            <button id="btnStartSession" onclick="startSession()" class="btn btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md <?= $session['status'] !== 'waiting' ? 'hidden' : '' ?>">
                <i class="fa-solid fa-play me-1"></i> BẮT ĐẦU CHO THI
            </button>

            <?php if ($session['status'] !== 'completed'): ?>
                <button id="btnEndSession" onclick="endSession()" class="btn btn-danger bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md">
                    <i class="fa-solid fa-lock me-1"></i> ĐÓNG LƯỢT THI THẬT
                </button>
            <?php else: ?>
                <span class="badge bg-gray-200 text-gray-800 font-bold text-sm px-3 py-2.5 rounded-xl flex items-center gap-1">
                    <i class="fa-solid fa-lock me-1"></i> LƯỢT THI ĐÃ ĐÓNG
                </span>
            <?php endif; ?>

            <a href="<?= base_url("teacher/sessions/leaderboard/{$session['id']}") ?>" target="_blank" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 py-2.5 shadow-md">
                <i class="fa-solid fa-tv me-1"></i> Bảng Chiếu (Projector)
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
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 text-base mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-user-xmark text-red-500"></i>
                    Danh Sách Lớp & Đánh Vắng
                </h3>
                <p class="text-xs text-gray-500 mb-4">
                    Nếu học sinh không tham gia, bấm <strong class="text-red-600">"Đánh vắng"</strong>. Hệ thống sẽ cộng phạt: <span class="badge bg-red-100 text-red-800 font-bold">+0 đúng / +3 tổng làm</span> vào kho điểm tích luỹ.
                </p>

                <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1">
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

    let html = '';
    participants.forEach(p => {
        let approvalBadge = '';
        if (p.approval_status === 'pending') {
            approvalBadge = `<button onclick="approveStudent(${p.id})" class="btn btn-success btn-sm rounded-lg font-bold shadow-sm animate-bounce"><i class="fa-solid fa-check me-1"></i> PHÊ DUYỆT</button>
                             <button onclick="rejectStudent(${p.id})" class="btn btn-danger btn-sm rounded-lg font-bold ms-1"><i class="fa-solid fa-xmark me-1"></i> Từ chối</button>`;
        } else if (p.approval_status === 'approved') {
            approvalBadge = '<span class="badge bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1"><i class="fa-solid fa-circle-check me-1"></i> Đã duyệt</span>';
        } else if (p.approval_status === 'rejected') {
            approvalBadge = '<span class="badge bg-red-100 text-red-700 font-bold px-2.5 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Đã từ chối</span>';
        } else if (p.approval_status === 'absent') {
            approvalBadge = '<span class="badge bg-red-600 text-white font-bold px-2.5 py-1"><i class="fa-solid fa-user-xmark me-1"></i> ĐÁNH VẮNG (+0/+3)</span>';
        }

        let testStatusBadge = '';
        if (p.test_status === 'waiting_approval') {
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
        if (p.score_total > 0 && p.test_status === 'submitted') {
            scoreDisplay = `<span class="text-base font-black text-emerald-600">${p.score_correct} / ${p.score_total} câu đúng</span>`;
        }

        html += `
            <tr>
                <td class="ps-6">
                    <div class="font-bold text-gray-900">${p.student_name}</div>
                    <div class="text-xs text-gray-500">@${p.username}</div>
                </td>
                <td>${approvalBadge}</td>
                <td>${testStatusBadge}</td>
                <td>${scoreDisplay}</td>
                <td class="text-end pe-6">
                    ${p.approval_status !== 'absent' ? `<button onclick="markAbsent(${sessionId}, ${p.student_id})" class="btn btn-outline-danger btn-sm rounded-lg text-xs"><i class="fa-solid fa-user-slash me-1"></i> Đánh vắng</button>` : ''}
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
</script>
<?= $this->endSection() ?>
