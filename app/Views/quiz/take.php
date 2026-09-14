<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
.traloi {
    background-color: #fef08a;
    padding: 2px 6px;
    border-radius: 4px;
}
.causai {
    border: 2px solid #ef4444 !important;
    background-color: #ffe4e6 !important;
}
</style>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <span class="badge bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-lg text-xs mb-2 inline-block">
                <?= esc($quiz['subject']) ?> - Khối <?= esc($quiz['grade_level']) ?>
            </span>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($quiz['title']) ?></h1>
            <p class="text-sm text-gray-500 mt-1 mb-0">Tác giả: <strong><?= esc($quiz['teacher_name'] ?? 'Giáo viên') ?></strong></p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép đường dẫn bài luyện tập!');" class="btn btn-outline-indigo border-indigo-500 text-indigo-600 hover:bg-indigo-50 font-semibold rounded-xl px-3 py-2 text-sm">
                <i class="fa-solid fa-share-nodes me-1"></i> Chia sẻ
            </button>
        </div>
    </div>

    <!-- Student Info & Quiz Area -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
        <div class="row g-3 align-items-center pb-3 border-b border-gray-100">
            <div class="col-md-6">
                <span class="text-sm text-gray-700">
                    <i class="fa-solid fa-user-check me-1 text-emerald-500"></i> Thí sinh: <strong class="text-gray-900 text-base"><?= esc(session()->get('full_name')) ?></strong>
                </span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-xs text-gray-400">Kết quả làm bài sẽ được tự động lưu lại</span>
            </div>
        </div>

        <!-- Rendered Quiz Content -->
        <div id="quiz-detail" class="prose max-w-none text-gray-800 leading-relaxed py-2">
            <?= $rendered_html ?>
        </div>

        <hr class="my-4">

        <div class="d-flex gap-3 items-center">
            <button type="button" id="submit-btn" onclick="submitQuiz()" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-5 py-2.5 shadow-md">
                <i class="fa-solid fa-paper-plane me-1"></i> Nộp Bài & Kiểm Tra Kết Quả
            </button>
            <button type="button" id="reset-btn" onclick="location.reload()" class="btn btn-outline-secondary rounded-xl px-4 py-2.5" style="display:none;">
                <i class="fa-solid fa-rotate-right me-1"></i> Làm Lại
            </button>
        </div>
    </div>

    <!-- Leaderboard / History of Submissions -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-500"></i> Lịch Sử Luyện Tập Dành Cho Bài Này (<?= count($results) ?> lượt làm)
        </h3>
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0 text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-4">Họ và Tên</th>
                        <th>Số Câu Đúng</th>
                        <th>Tổng Số Câu</th>
                        <th>Tỷ Lệ</th>
                        <th class="text-end pe-4">Thời Gian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-400">
                                Chưa có ai hoàn thành bài luyện tập này. Hãy là người đầu tiên!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $res): ?>
                            <?php 
                                $percent = $res['total_questions'] > 0 ? round(($res['score_correct'] / $res['total_questions']) * 100) : 0;
                            ?>
                            <tr>
                                <td class="ps-4 font-semibold text-gray-900"><?= esc($res['student_name']) ?></td>
                                <td><span class="badge bg-emerald-100 text-emerald-800 font-bold"><?= $res['score_correct'] ?> câu</span></td>
                                <td><?= $res['total_questions'] ?> câu</td>
                                <td>
                                    <span class="badge <?= $percent >= 80 ? 'bg-emerald-600' : ($percent >= 50 ? 'bg-amber-500' : 'bg-rose-500') ?> text-white font-bold">
                                        <?= $percent ?>%
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-gray-500 text-xs"><?= date('H:i d/m/Y', strtotime($res['completed_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.5/MathJax.js?config=TeX-MML-AM_CHTML" async></script>
<script>
window.addEventListener('DOMContentLoaded', function() {
    if (window.MathJax) {
        MathJax.Hub.Config({
            tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
        });
    }
});

function submitQuiz() {
    var totalQuestions = 0;
    var causai = [];
    var lstVideos = [];

    // Track group correctness
    var groupStatus = {};
    $("input.cauhoi[type='radio'], input.cauhoi[type='checkbox']").each(function() {
        var groupName = $(this).attr("name");
        if (groupStatus[groupName] === undefined) {
            groupStatus[groupName] = true;
        }
        var isChecked = $(this).prop("checked");
        var isCorrect = ($(this).attr("dung") == "1");
        if (isChecked !== isCorrect) {
            groupStatus[groupName] = false;
        }
    });

    var distinctGroups = Object.keys(groupStatus);
    distinctGroups.forEach(function(gName) {
        if (!groupStatus[gName]) {
            causai.push(gName);
            $("input.cauhoi[name='" + gName + "']").each(function() {
                $(this).parent().addClass("causai");
            });
        }
    });

    // Evaluate text fill-in questions
    var textCount = 0;
    $("input.cauhoi[type='text']").each(function() {
        textCount++;
        var dapan = "|" + $(this).attr("dung") + "|";
        var traloi = $(this).val().trim();
        traloi = traloi.replace(/\s\s+/g, ' ');
        var sokhop = $(this).attr("khop") || "00";
        if (sokhop.substr(0,1) == "0") {
            dapan = dapan.toLowerCase();
            traloi = traloi.toLowerCase();
        }
        if (sokhop.substr(1,1) == "0") {
            dapan = dapan.replace(/\s+/g, '');
            traloi = traloi.replace(/\s+/g, '');
        }

        if (dapan.indexOf("|" + traloi + "|") < 0) {
            $(this).attr("title", "Đáp án đúng: " + $(this).attr("dung"));
            $(this).addClass("causai");
            causai.push($(this).attr("name"));
            if ($(this).attr("vid")) lstVideos.push($(this).attr("vid"));
        }
    });

    totalQuestions = distinctGroups.length + textCount;
    var wrongCount = causai.length;
    var scoreCorrect = Math.max(0, totalQuestions - wrongCount);

    $("input.cauhoi").prop("disabled", true);
    $("#submit-btn").hide();
    $("#reset-btn").show();

    // Send AJAX to record result
    $.ajax({
        url: '<?= base_url("practice/{$quiz['slug']}/submit") ?>',
        method: 'POST',
        data: {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
            score_correct: scoreCorrect,
            total_questions: totalQuestions,
            wrong_count: wrongCount,
            wrong_questions_json: JSON.stringify(causai)
        },
        success: function(res) {
            if (wrongCount > 0) {
                alert("Bạn đã làm đúng " + scoreCorrect + "/" + totalQuestions + " câu. Số câu sai: " + wrongCount);
            } else {
                alert("Xuất sắc! Bạn đã làm đúng tất cả " + totalQuestions + "/" + totalQuestions + " câu!");
            }
            location.reload();
        },
        error: function() {
            alert("Đã hoàn thành bài kiểm tra!");
            location.reload();
        }
    });
}
</script>
<?= $this->endSection() ?>
