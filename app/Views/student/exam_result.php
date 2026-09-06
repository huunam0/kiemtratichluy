<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Score Summary Card -->
    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-8 text-center space-y-4">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-4xl font-bold">
            <i class="fa-solid fa-award"></i>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kết Quả Bài Thi Tích Luỹ</h1>
            <p class="text-gray-500 text-sm">Lượt thi: <span class="font-semibold text-gray-800"><?= esc($session['session_title']) ?></span></p>
        </div>

        <!-- Session Result Box (Only show correct / total questions for single session) -->
        <div class="py-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 max-w-sm mx-auto">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kết Quả Lượt Thi</div>
            <div class="text-4xl font-black text-emerald-600">
                <i class="fa-solid fa-circle-check me-1"></i> <?= $participant['score_correct'] ?> / <?= $participant['score_total'] ?> <span class="text-2xl font-bold text-gray-700">câu đúng</span>
            </div>
        </div>

        <div class="pt-2">
            <a href="<?= base_url('student/dashboard') ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-6 py-3 shadow-md">
                <i class="fa-solid fa-house me-1"></i> Trở Về Trang Chủ
            </a>
        </div>
    </div>

    <!-- Detailed Question Feedback -->
    <div class="space-y-4">
        <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
            <i class="fa-solid fa-list-check text-indigo-600"></i>
            Chi Tiết Từng Câu Hỏi
        </h3>

        <?php foreach ($questions as $index => $item): ?>
            <?php 
                $sqa = $item['sqa'];
                $isCorrect = ($sqa['is_correct'] == 1);
            ?>
            <div class="bg-white rounded-2xl p-6 shadow-sm border <?= $isCorrect ? 'border-emerald-200' : 'border-red-200' ?> space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <span class="font-bold text-gray-900">Câu <?= $index + 1 ?></span>
                    <?php if ($isCorrect): ?>
                        <span class="badge bg-emerald-100 text-emerald-800 font-bold px-3 py-1 text-xs">
                            <i class="fa-solid fa-check me-1"></i> ĐÚNG
                        </span>
                    <?php else: ?>
                        <span class="badge bg-red-100 text-red-800 font-bold px-3 py-1 text-xs">
                            <i class="fa-solid fa-xmark me-1"></i> SAI
                        </span>
                    <?php endif; ?>
                </div>

                <div class="text-gray-900 font-semibold text-base">
                    <?= $sqa['content'] ?>
                </div>

                <div class="space-y-2">
                    <?php foreach ($item['display_options'] as $opt): ?>
                        <?php 
                            $isUserPick = ($sqa['selected_option'] === $opt['label']);
                            $isCorrectOpt = $opt['is_correct'];

                            $optionStyle = 'border-gray-200 bg-white text-gray-700';
                            $badgeStyle = 'bg-gray-400 text-white';

                            if ($isCorrectOpt) {
                                $optionStyle = 'border-emerald-500 bg-emerald-50 text-emerald-950 font-bold';
                                $badgeStyle = 'bg-emerald-600 text-white';
                            } elseif ($isUserPick && !$isCorrectOpt) {
                                $optionStyle = 'border-red-500 bg-red-50 text-red-950 line-through';
                                $badgeStyle = 'bg-red-600 text-white';
                            }
                        ?>
                        <div class="p-3 rounded-xl border-2 flex items-center gap-3 text-sm <?= $optionStyle ?>">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 <?= $badgeStyle ?>">
                                <?= $opt['label'] ?>
                            </span>
                            <div class="flex-1"><?= $opt['content'] ?></div>
                            <?php if ($isCorrectOpt): ?>
                                <span class="text-xs text-emerald-700 font-bold"><i class="fa-solid fa-circle-check"></i> Đáp án đúng</span>
                            <?php elseif ($isUserPick): ?>
                                <span class="text-xs text-red-700 font-bold"><i class="fa-solid fa-circle-xmark"></i> Lựa chọn của bạn</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($sqa['explanation'])): ?>
                    <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-900">
                        <strong class="font-bold"><i class="fa-solid fa-lightbulb me-1 text-yellow-500"></i> Lời giải chi tiết:</strong> <?= esc($sqa['explanation']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>
<?= $this->endSection() ?>
