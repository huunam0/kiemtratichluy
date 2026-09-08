<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="badge bg-indigo-100 text-indigo-800 font-bold">Lớp <?= esc($class['name'] ?? '') ?></span>
                <span class="badge bg-gray-100 text-gray-700">Khối <?= esc($class['grade_level'] ?? '') ?></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-0">Bảng Điểm Tích Luỹ Cộng Dồn Học Sinh</h1>
            <p class="text-gray-500 text-sm mt-1 mb-0">
                Bài kiểm tra: <span class="font-semibold text-indigo-600"><?= esc($test['title']) ?></span>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Random Pick Button -->
            <button type="button" class="btn btn-success btn-sm rounded-xl font-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#randomPickModal">
                <i class="fa-solid fa-dice me-1"></i> Gọi Ngẫu Nhiên
            </button>

            <a href="<?= base_url('teacher/tests') ?>" class="btn btn-outline-secondary btn-sm rounded-xl">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh sách Bài Tích Luỹ
            </a>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-ranking-star text-amber-500"></i>
                Bảng Xếp Hạng Điểm Tích Luỹ Thang 10 (Sắp xếp từ Cao xuống Thấp)
            </h3>
            <span class="text-xs text-gray-500 font-medium">Tổng số: <?= count($scores_list) ?> học sinh</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6" style="width: 70px;">Hạng</th>
                        <th>Họ Và Tên Học Sinh</th>
                        <th>Tên Đăng Nhập</th>
                        <th class="text-center">Tổng Câu Đúng</th>
                        <th class="text-center">Tổng Câu Đã Làm</th>
                        <th class="text-center">ĐIỂM TÍCH LUỸ (HỆ 10)</th>
                        <th class="text-end pe-6">Chi Tiết Từng Lượt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($scores_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-400">
                                Lớp học này chưa có học sinh nào đăng ký.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($scores_list as $index => $st): ?>
                            <?php 
                                $rank = $index + 1;
                                $rankBadge = "<span class='text-gray-400 font-bold'>#{$rank}</span>";
                                if ($rank === 1 && $st['has_score']) {
                                    $rankBadge = '<span class="w-8 h-8 rounded-full bg-yellow-400 text-yellow-950 font-black inline-flex items-center justify-center shadow-sm">1</span>';
                                } elseif ($rank === 2 && $st['has_score']) {
                                    $rankBadge = '<span class="w-8 h-8 rounded-full bg-slate-300 text-slate-900 font-black inline-flex items-center justify-center shadow-sm">2</span>';
                                } elseif ($rank === 3 && $st['has_score']) {
                                    $rankBadge = '<span class="w-8 h-8 rounded-full bg-amber-600 text-white font-black inline-flex items-center justify-center shadow-sm">3</span>';
                                }
                            ?>
                            <tr>
                                <td class="ps-6 font-bold text-center"><?= $rankBadge ?></td>
                                <td>
                                    <div class="font-bold text-gray-900 text-base"><?= esc($st['full_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= esc($st['email']) ?></div>
                                </td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800 font-mono">@<?= esc($st['username']) ?></code></td>
                                <td class="text-center font-bold text-emerald-600"><?= $st['total_correct'] ?> câu</td>
                                <td class="text-center font-semibold text-gray-700"><?= $st['total_attempted'] ?> câu</td>
                                <td class="text-center">
                                    <?php if ($st['has_score'] && $st['total_attempted'] > 0): ?>
                                        <span class="text-xl font-black text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-100">
                                            <?= number_format($st['accumulated_gpa'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Chưa tham gia lượt nào</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-6">
                                    <a href="<?= base_url("teacher/tests/student-breakdown/{$test['id']}/{$st['id']}") ?>" class="btn btn-outline-indigo btn-sm rounded-xl font-bold">
                                        <i class="fa-solid fa-list-ul me-1"></i> Xem Chi Tiết Lượt Thi
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

<!-- ============================================================ -->
<!-- RANDOM PICK MODAL -->
<!-- ============================================================ -->
<div class="modal fade" id="randomPickModal" tabindex="-1" aria-labelledby="randomPickModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-xl overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-to-r from-emerald-600 to-teal-600 text-white border-0 px-6 py-4">
                <h5 class="modal-title font-bold text-lg flex items-center gap-2" id="randomPickModalLabel">
                    <i class="fa-solid fa-dice text-yellow-300 text-xl"></i>
                    Gọi Ngẫu Nhiên Học Sinh Kiểm Tra Thật
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-6 py-5">
                <!-- Input Section -->
                <div id="pickInputSection">
                    <p class="text-gray-600 text-sm mb-4">
                        <i class="fa-solid fa-circle-info text-indigo-500 me-1"></i>
                        Hệ thống sẽ <strong>ưu tiên chọn</strong> các học sinh có <strong>ít lượt kiểm tra thật nhất</strong> 
                        (dựa trên trung vị số lượt thi).
                    </p>

                    <div class="flex items-end gap-3">
                        <div class="flex-grow">
                            <label for="pickQuantity" class="block text-sm font-semibold text-gray-700 mb-1">
                                Số lượng học sinh muốn gọi <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="pickQuantity" min="1" value="1" 
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
                            <div class="text-xs text-indigo-500 font-semibold uppercase">Trung Vị Lượt Thi</div>
                            <div class="text-2xl font-black text-indigo-700 mt-1" id="statMedian">-</div>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3 text-center border border-emerald-100">
                            <div class="text-xs text-emerald-500 font-semibold uppercase">HS Trong Pool</div>
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
                        Danh sách Học Sinh được gọi:
                    </h6>
                    <div id="pickedList" class="space-y-2"></div>

                    <!-- Re-pick Button -->
                    <div class="mt-5 pt-4 border-t border-gray-200 flex justify-between items-center">
                        <p class="text-xs text-gray-400 mb-0 italic">
                            <i class="fa-solid fa-lightbulb text-amber-400 me-1"></i>
                            Bấm "Bốc lại" để chọn ngẫu nhiên lần nữa với cùng số lượng.
                        </p>
                        <button type="button" onclick="doRandomPick()" 
                                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-rotate-right"></i> Bốc Lại
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(12px) scale(0.97);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    .pick-animate {
        animation: fadeSlideIn 0.4s ease-out both;
    }
</style>

<script>
function doRandomPick() {
    const quantity = document.getElementById('pickQuantity').value;
    if (!quantity || quantity < 1) {
        alert('Vui lòng nhập số lượng học sinh hợp lệ!');
        return;
    }

    // Show loading, hide results & input
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

        // Fill stats
        document.getElementById('statMedian').textContent = data.median;
        document.getElementById('statPool').textContent = data.pool_size + ' HS';
        document.getElementById('statTotal').textContent = data.total_class + ' HS';

        // Build picked list with staggered animation
        const listEl = document.getElementById('pickedList');
        listEl.innerHTML = '';

        if (data.picked.length === 0) {
            listEl.innerHTML = '<div class="text-center text-gray-400 py-4">Không có học sinh nào trong pool.</div>';
        } else {
            data.picked.forEach(function(student, index) {
                const card = document.createElement('div');
                card.className = 'pick-animate flex items-center gap-3 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl px-4 py-3 shadow-sm';
                card.style.animationDelay = (index * 0.12) + 's';

                const orderBadge = '<span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-black inline-flex items-center justify-center shadow text-sm flex-shrink-0">' + (index + 1) + '</span>';

                card.innerHTML = orderBadge +
                    '<div class="flex-grow">' +
                        '<div class="font-bold text-gray-900">' + escHtml(student.full_name) + '</div>' +
                        '<code class="bg-white/70 px-2 py-0.5 rounded text-emerald-700 font-mono text-xs">@' + escHtml(student.username) + '</code>' +
                    '</div>' +
                    '<div class="text-right flex-shrink-0">' +
                        '<span class="text-xs font-semibold text-gray-500">Đã thi</span>' +
                        '<div class="text-lg font-black text-indigo-700">' + student.real_count + ' <span class="text-xs font-normal text-gray-400">lượt</span></div>' +
                    '</div>';

                listEl.appendChild(card);
            });

            // Show note if fewer students picked than requested
            if (data.picked.length < data.requested) {
                const note = document.createElement('div');
                note.className = 'pick-animate text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2 mt-3 font-semibold';
                note.style.animationDelay = (data.picked.length * 0.12) + 's';
                note.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Chỉ có ' + data.picked.length + '/' + data.requested + ' HS trong pool đủ điều kiện (≤ trung vị ' + data.median + ' lượt).';
                listEl.appendChild(note);
            }
        }

        // Show results
        document.getElementById('pickResults').classList.remove('hidden');
    })
    .catch(err => {
        document.getElementById('pickLoading').classList.add('hidden');
        document.getElementById('btnRandomPick').disabled = false;
        alert('Có lỗi xảy ra, vui lòng thử lại.');
        console.error(err);
    });
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>

<?= $this->endSection() ?>
