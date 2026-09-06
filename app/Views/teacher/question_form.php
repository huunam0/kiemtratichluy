<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $isEdit = isset($question); ?>

<div class="max-w-4xl mx-auto my-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fa-solid fa-pen-nib text-indigo-600 me-2"></i>
                <?= $isEdit ? 'Chỉnh sửa Câu Hỏi' : 'Soạn Câu Hỏi Mới' ?>
            </h1>
            <a href="<?= base_url('teacher/questions') ?>" class="btn btn-outline-secondary btn-sm rounded-lg">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form action="<?= $isEdit ? base_url("teacher/questions/edit/{$question['id']}") : base_url('teacher/questions/create') ?>" method="POST" id="questionForm" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Subject & Grade -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Môn học / Chủ đề</label>
                    <input type="text" name="subject" value="<?= esc($question['subject'] ?? 'Tin Học') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: Toán, Tin học, Vật Lý...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Khối Lớp</label>
                    <select name="grade_level" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="10" <?= isset($question) && $question['grade_level'] == 10 ? 'selected' : '' ?>>Khối 10</option>
                        <option value="11" <?= isset($question) && $question['grade_level'] == 11 ? 'selected' : '' ?>>Khối 11</option>
                        <option value="12" <?= isset($question) && $question['grade_level'] == 12 ? 'selected' : '' ?>>Khối 12</option>
                    </select>
                </div>
            </div>

            <!-- Question Content Rich Text Editor (Quill.js) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Nội dung Câu hỏi (WYSIWYG Rich Text, Chèn Ảnh & Code Snippets) <span class="text-red-500">*</span>
                </label>
                <div id="editor-container" class="quill-editor-container border rounded-xl">
                    <?= $question['content'] ?? '' ?>
                </div>
                <input type="hidden" name="content" id="hidden_content">
            </div>

            <!-- Options (A & B mandatory, C & D optional for True/False) -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-lg">Các Đáp Án Lựa Chọn</h3>
                    <p class="text-xs text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200 mb-0">
                        <i class="fa-solid fa-circle-info"></i> Để nguyên trống Đáp án C & D nếu soạn câu hỏi Đúng/Sai (2 đáp án).
                    </p>
                </div>

                <!-- Option A -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Đáp án A <span class="text-red-500">*</span></label>
                    <input type="text" name="option_a" required value="<?= esc($question['option_a'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Nội dung đáp án A">
                </div>

                <!-- Option B -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Đáp án B <span class="text-red-500">*</span></label>
                    <input type="text" name="option_b" required value="<?= esc($question['option_b'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Nội dung đáp án B">
                </div>

                <!-- Option C (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Đáp án C <span class="text-xs text-gray-400 font-normal">(Tuỳ chọn - Bỏ trống nếu là câu hỏi Đúng/Sai)</span></label>
                    <input type="text" name="option_c" value="<?= esc($question['option_c'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Nội dung đáp án C (để trống nếu không có)">
                </div>

                <!-- Option D (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Đáp án D <span class="text-xs text-gray-400 font-normal">(Tuỳ chọn - Bỏ trống nếu không có)</span></label>
                    <input type="text" name="option_d" value="<?= esc($question['option_d'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Nội dung đáp án D (để trống nếu không có)">
                </div>
            </div>

            <!-- Correct Option & Explanation -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Đáp Án Đúng Gốc <span class="text-red-500">*</span></label>
                    <select name="correct_option" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white font-bold text-indigo-700">
                        <option value="A" <?= isset($question) && $question['correct_option'] === 'A' ? 'selected' : '' ?>>Đáp án A</option>
                        <option value="B" <?= isset($question) && $question['correct_option'] === 'B' ? 'selected' : '' ?>>Đáp án B</option>
                        <option value="C" <?= isset($question) && $question['correct_option'] === 'C' ? 'selected' : '' ?>>Đáp án C</option>
                        <option value="D" <?= isset($question) && $question['correct_option'] === 'D' ? 'selected' : '' ?>>Đáp án D</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Giải thích chi tiết (Hiển thị sau khi học sinh nộp bài)</label>
                    <input type="text" name="explanation" value="<?= esc($question['explanation'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Lời giải chi tiết...">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="<?= base_url('teacher/questions') ?>" class="btn btn-light rounded-xl px-5 py-2.5 font-medium">Hủy</a>
                <button type="submit" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-6 py-2.5 shadow-md">
                    <?= $isEdit ? 'Cập Nhật Câu Hỏi' : 'Lưu Câu Hỏi Vô Bank' ?> <i class="fa-solid fa-floppy-disk ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quill.js Editor Setup & Image Handler -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Soạn nội dung câu hỏi tại đây... (Có thể chèn hình ảnh, công thức, định dạng code)',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                ['code-block', 'blockquote'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Custom Image Handler
    quill.getModule('toolbar').addHandler('image', function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = () => {
            const file = input.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                fetch('<?= base_url('teacher/questions/upload-image') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(result => {
                    if (result.url) {
                        const range = quill.getSelection();
                        quill.insertEmbed(range.index, 'image', result.url);
                    } else {
                        alert('Upload ảnh thất bại!');
                    }
                })
                .catch(() => alert('Lỗi kết nối máy chủ!'));
            }
        };
    });

    // Form submit bind
    document.getElementById('questionForm').onsubmit = function() {
        document.getElementById('hidden_content').value = quill.root.innerHTML;
    };
});
</script>
<?= $this->endSection() ?>
