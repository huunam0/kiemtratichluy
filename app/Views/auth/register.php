<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-xl mx-auto my-6">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-indigo-700 px-6 py-6 text-center text-white">
            <i class="fa-solid fa-user-plus text-3xl mb-2 text-yellow-300"></i>
            <h2 class="text-2xl font-bold">Đăng ký tài khoản</h2>
            <p class="text-indigo-200 text-sm">Hệ thống Kiểm tra Tích luỹ</p>
        </div>

        <form action="<?= base_url('auth/register') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Role Choice -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bạn là:</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 border-indigo-200 bg-indigo-50/50">
                        <input type="radio" name="role" value="student" checked onchange="toggleRoleFields('student')" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-semibold text-gray-800"><i class="fa-solid fa-user-graduate text-indigo-600 me-1"></i> Học sinh</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 border-gray-200">
                        <input type="radio" name="role" value="teacher" onchange="toggleRoleFields('teacher')" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-semibold text-gray-800"><i class="fa-solid fa-chalkboard-user text-indigo-600 me-1"></i> Giáo viên</span>
                    </label>
                </div>
            </div>

            <!-- Full Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" required value="<?= old('full_name') ?>"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Ví dụ: Nguyễn Văn A">
            </div>

            <!-- Username & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên đăng nhập <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required value="<?= old('username') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="username">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required value="<?= old('email') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="email@example.com">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Nhập mật khẩu">
            </div>

            <!-- School Select -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Trường học <span class="text-red-500">*</span></label>
                <select name="school_id" id="school_id" required onchange="loadClasses(this.value)"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">-- Chọn Trường học --</option>
                    <?php foreach ($schools as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?> (<?= esc($s['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Class Select (Student Only, Dropdown preventing typing errors) -->
            <div id="class_select_wrapper">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lớp học <span class="text-red-500">*</span></label>
                <select name="class_id" id="class_id" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">-- Vui lòng chọn Trường trước --</option>
                </select>
                <p class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-info-circle"></i> Danh sách Lớp học được Giáo viên tạo sẵn để chọn chính xác.</p>
            </div>

            <div id="approval_note" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs">
                <i class="fa-solid fa-shield-halved text-amber-600 me-1"></i> Tài khoản sau khi đăng ký sẽ cần được phê duyệt trước khi có thể đăng nhập (<strong id="approver_type">Giáo viên trong trường phê duyệt cho Học sinh</strong>).
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition touch-target">
                Tạo Tài Khoản <i class="fa-solid fa-user-check ms-2"></i>
            </button>
        </form>

        <div class="bg-gray-50 border-t border-gray-100 p-4 text-center text-sm text-gray-600">
            Đã có tài khoản? 
            <a href="<?= base_url('auth/login') ?>" class="text-indigo-600 font-semibold hover:underline">Đăng nhập</a>
        </div>
    </div>
</div>

<script>
function toggleRoleFields(role) {
    const classWrapper = document.getElementById('class_select_wrapper');
    const classSelect  = document.getElementById('class_id');
    const approverType = document.getElementById('approver_type');

    if (role === 'teacher') {
        classWrapper.classList.add('hidden');
        classSelect.removeAttribute('required');
        if (approverType) approverType.innerText = 'Admin phê duyệt cho Giáo viên';
    } else {
        classWrapper.classList.remove('hidden');
        classSelect.setAttribute('required', 'required');
        if (approverType) approverType.innerText = 'Giáo viên trong trường phê duyệt cho Học sinh';
    }
}

function loadClasses(schoolId) {
    const classSelect = document.getElementById('class_id');
    if (!schoolId) {
        classSelect.innerHTML = '<option value="">-- Vui lòng chọn Trường trước --</option>';
        return;
    }

    classSelect.innerHTML = '<option value="">Đang tải danh sách Lớp...</option>';

    fetch('<?= base_url('auth/get-classes/') ?>' + schoolId)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                classSelect.innerHTML = '<option value="">(Chưa có Lớp nào trong Trường này)</option>';
                return;
            }

            let html = '<option value="">-- Chọn Lớp học --</option>';
            data.forEach(cls => {
                html += `<option value="${cls.id}">${cls.name} (Khối ${cls.grade_level}) - GV: ${cls.creator_name || 'Hệ thống'}</option>`;
            });
            classSelect.innerHTML = html;
        })
        .catch(err => {
            classSelect.innerHTML = '<option value="">Lỗi tải danh sách Lớp!</option>';
        });
}
</script>
<?= $this->endSection() ?>
