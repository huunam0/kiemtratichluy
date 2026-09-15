<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-xl mx-auto text-center py-10 space-y-6">
    <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 space-y-6">
        <div class="inline-flex items-center justify-center w-24 h-24 <?= $score_base10 >= 5 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' ?> rounded-full text-4xl shadow-inner">
            <i class="fa-solid <?= $score_base10 >= 5 ? 'fa-trophy' : 'fa-graduation-cap' ?>"></i>
        </div>

        <div>
            <span class="badge bg-indigo-100 text-indigo-800 me-2"><?= $is_mock ? 'KẾT QUẢ THI THỬ' : 'KẾT QUẢ THI THẬT' ?></span>
            <h1 class="text-2xl font-black text-gray-900 mt-2 mb-1"><?= esc($quiz['title']) ?></h1>
            <p class="text-gray-500 text-sm">Hoàn thành lúc <?= date('H:i:s d/m/Y') ?></p>
        </div>

        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 text-center space-y-3">
            <div class="text-xs uppercase tracking-wider font-bold text-gray-500">Điểm số (Thang điểm 10)</div>
            <div class="text-5xl font-black text-indigo-600 font-mono"><?= number_format($score_base10, 1) ?></div>
            <div class="text-sm font-semibold text-gray-700">
                Đúng <strong class="text-emerald-600"><?= $score_correct ?></strong> / <strong><?= $total_blanks ?></strong> ô điền
            </div>
        </div>

        <?php if (!$is_mock): ?>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-800 text-sm flex items-center justify-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <span>Điểm số thi thật <strong>+<?= number_format($score_base10, 1) ?></strong> đã được tự động cộng vào bảng <strong>Điểm Tích Lũy</strong> của bạn!</span>
            </div>
        <?php endif; ?>

        <div>
            <a href="<?= base_url('student/dashboard') ?>" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-extrabold rounded-2xl px-6 py-2.5 shadow-md">
                <i class="fa-solid fa-house me-1"></i> Quay về Trang chủ
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
