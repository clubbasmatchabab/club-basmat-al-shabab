<?php
// ملف: join.php
// نموذج طلب الانخراط في نادي بصمة الشباب
// يستخدم Supabase للمصادقة وقاعدة البيانات، و Appwrite لتخزين الملفات.
include 'config.php'; 
session_start();

$page_title = "انضم إلينا - نادي بصمة الشباب";
$error_message = '';
$success_message = '';
$user_id = null; 
$photo_url = null; 
$file_id = null; 

// -----------------------------------------------------------
// 1. معالجة طلب الانخراط
// -----------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // تنظيف المدخلات
    $full_name = filter_var($_POST['full_name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $city = filter_var($_POST['city'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $birth_date = $_POST['birth_date'] ?? null;
    $photo_file = $_FILES['personal_photo'] ?? null;

    if (!$email || empty($password) || empty($full_name)) {
        $error_message = "الرجاء تعبئة جميع الحقول المطلوبة بشكل صحيح.";
    } else {
        
        // -----------------------------------------------------------
        // 2. إنشاء المستخدم في Supabase Auth (الخطوة الأولى)
        // -----------------------------------------------------------
        
        $auth_url = SUPABASE_URL . '/auth/v1/signup';
        $auth_data = json_encode([
            'email' => $email,
            'password' => $password,
            'data' => ['full_name' => $full_name]
        ]);

        $ch_auth = curl_init($auth_url);
        curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_auth, CURLOPT_POST, true);
        curl_setopt($ch_auth, CURLOPT_POSTFIELDS, $auth_data);
        curl_setopt($ch_auth, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . SUPABASE_ANON_KEY,
            'Authorization: Bearer ' . SUPABASE_ANON_KEY
        ]);

        $auth_response = curl_exec($ch_auth);
        $auth_http_code = curl_getinfo($ch_auth, CURLINFO_HTTP_CODE);
        curl_close($ch_auth);
        $auth_data_decoded = json_decode($auth_response, true);
        
        if ($auth_http_code === 200 || $auth_http_code === 201) {
            $user_id = $auth_data_decoded['user']['id'];
            
            // -----------------------------------------------------------
            // 3. رفع الصورة الشخصية إلى Appwrite Storage (الخطوة الثانية)
            // -----------------------------------------------------------
            
            if ($photo_file && $photo_file['error'] === UPLOAD_ERR_OK) {
                
                $extension = strtolower(pathinfo($photo_file['name'], PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png'];

                if (in_array($extension, $allowed_types)) {
                    
                    $storage_url = APPWRITE_ENDPOINT . '/storage/buckets/' . MEMBER_PHOTOS_BUCKET . '/files';
                    
                    if (class_exists('CURLFile')) {
                        $cfile = new CURLFile($photo_file['tmp_name'], $photo_file['type'], $photo_file['name']);
                    } else {
                        $cfile = '@' . $photo_file['tmp_name'] . ';filename=' . $photo_file['name'] . ';type=' . $photo_file['type'];
                    }
                    
                    // *** الحل الجذري لمشكلة 400 ***
                    $permissions_array = ['read(\'role:any\')'];
                    $permissions_json = json_encode($permissions_array); 

                    $post_data = [
                        'file' => $cfile,
                        'fileId' => 'unique()', 
                        'permissions' => $permissions_json 
                    ];
                    // *******************************
                    
                    $ch_storage = curl_init($storage_url);
                    curl_setopt($ch_storage, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_storage, CURLOPT_POST, true);
                    curl_setopt($ch_storage, CURLOPT_POSTFIELDS, $post_data);
                    curl_setopt($ch_storage, CURLOPT_HTTPHEADER, [
                        'X-Appwrite-Project: ' . APPWRITE_PROJECT_ID,
                        'X-Appwrite-Key: ' . APPWRITE_API_KEY, 
                    ]);
                    
                    $storage_response = curl_exec($ch_storage);
                    $storage_http_code = curl_getinfo($ch_storage, CURLINFO_HTTP_CODE);
                    curl_close($ch_storage);
                    
                    $storage_data_decoded = json_decode($storage_response, true);

                    if ($storage_http_code === 201) {
                        $file_id = $storage_data_decoded['$id'];
                        $photo_url = APPWRITE_ENDPOINT . '/storage/buckets/' . MEMBER_PHOTOS_BUCKET . '/files/' . $file_id . '/view';

                    } else {
                        $error_message = "فشل تحميل الصورة الشخصية عبر Appwrite. رمز الخطأ: " . $storage_http_code . ". الرد: " . ($storage_data_decoded['message'] ?? $storage_response);
                    }

                } else {
                    $error_message = "صيغة الصورة غير مدعومة. الرجاء استخدام JPG أو PNG.";
                }
            } else {
                 $error_message = "لم يتم تحديد ملف أو حدث خطأ أثناء الرفع المؤقت.";
            }
            
            // -----------------------------------------------------------
            // 4. إدراج بيانات العضو في جدول 'members' (الخطوة الثالثة)
            // -----------------------------------------------------------
            
            if (empty($error_message) && $photo_url) {

                $db_url = SUPABASE_URL . '/rest/v1/members';
                $db_data = json_encode([
                    'user_id' => $user_id,
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => $city,
                    'birth_date' => $birth_date,
                    'photo_url' => $photo_url,
                    'membership_status' => 'Pending' 
                ]);
                
                $ch_db = curl_init($db_url);
                curl_setopt($ch_db, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_db, CURLOPT_POST, true);
                curl_setopt($ch_db, CURLOPT_POSTFIELDS, $db_data);
                curl_setopt($ch_db, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'apikey: ' . SUPABASE_ANON_KEY,
                    'Authorization: Bearer ' . SUPABASE_ANON_KEY,
                    'Prefer: return=representation' 
                ]);

                $db_response = curl_exec($ch_db);
                $db_http_code = curl_getinfo($ch_db, CURLINFO_HTTP_CODE);
                curl_close($ch_db);

                if ($db_http_code === 201) {
                    header('Location: ' . BASE_DIR . 'email-confirmation.php?status=success');
                    exit;
                } else {
                    $error_message = "فشل في تسجيل بيانات العضو في قاعدة البيانات. رمز الخطأ: " . $db_http_code;
                }
            }
            
        } else {
            if (isset($auth_data_decoded['msg'])) {
                $error_message = "خطأ في المصادقة: " . $auth_data_decoded['msg'];
            } elseif ($auth_http_code !== 200 && $auth_http_code !== 201) {
                $error_message = "خطأ في الاتصال بخدمة المصادقة. رمز HTTP: " . $auth_http_code . ".";
            } else {
                $error_message = "خطأ غير متوقع في المصادقة. الرد: " . $auth_response;
            }
        }
        
        // -----------------------------------------------------------
        // 5. عملية التراجع (Rollback) 
        // -----------------------------------------------------------
        
        if (!empty($error_message) && $user_id) {
            
            // أ. حذف المستخدم من Supabase Auth
            $delete_auth_url = SUPABASE_URL . '/auth/v1/admin/users/' . $user_id;
            
            $ch_del_auth = curl_init($delete_auth_url);
            curl_setopt($ch_del_auth, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_del_auth, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch_del_auth, CURLOPT_HTTPHEADER, [
                'apikey: ' . SUPABASE_SERVICE_KEY, 
                'Authorization: Bearer ' . SUPABASE_SERVICE_KEY
            ]);
            curl_exec($ch_del_auth);
            curl_close($ch_del_auth);
            
            // ب. حذف الصورة المرفوعة من Appwrite
            if ($file_id) {
                $delete_storage_url = APPWRITE_ENDPOINT . '/storage/buckets/' . MEMBER_PHOTOS_BUCKET . '/files/' . $file_id;
                
                $ch_del_storage = curl_init($delete_storage_url);
                curl_setopt($ch_del_storage, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_del_storage, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch_del_storage, CURLOPT_HTTPHEADER, [
                    'X-Appwrite-Project: ' . APPWRITE_PROJECT_ID,
                    'X-Appwrite-Key: ' . APPWRITE_API_KEY, 
                ]);
                curl_exec($ch_del_storage);
                curl_close($ch_del_storage);
            }
            
            $error_message .= " (تم التراجع عن العملية وحذف الحساب والصورة)";
        }
    }
}

// -----------------------------------------------------------
// 6. عرض نموذج HTML
// -----------------------------------------------------------
include 'header.php';
?>

<div class="container mx-auto px-4 py-16">
    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 rounded-xl shadow-2xl">
        <header class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-primary-green mb-2">انضم إلى نادي بصمة الشباب</h1>
            <p class="text-lg text-gray-600">املأ النموذج أدناه لبدء رحلتك كعضو.</p>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <p class="font-bold">خطأ في المعالجة:</p>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars(BASE_DIR . 'join.php'); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <h2 class="text-2xl font-semibold text-secondary-blue border-b pb-2 mb-4">بيانات التسجيل</h2>
            
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700">الاسم الكامل *</label>
                <input type="text" id="full_name" name="full_name" required 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:border-primary-green focus:ring-primary-green">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">البريد الإلكتروني *</label>
                <input type="email" id="email" name="email" required 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:border-primary-green focus:ring-primary-green">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور *</label>
                <input type="password" id="password" name="password" required 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:border-primary-green focus:ring-primary-green">
                <p class="mt-2 text-sm text-gray-500">يجب أن تحتوي على 6 أحرف على الأقل.</p>
            </div>
            
            <h2 class="text-2xl font-semibold text-secondary-blue border-b pb-2 mb-4 pt-6">معلومات شخصية</h2>

            <div>
                <label for="personal_photo" class="block text-sm font-medium text-gray-700">الصورة الشخصية *</label>
                <input type="file" id="personal_photo" name="personal_photo" accept="image/jpeg,image/png" required 
                       class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none p-2">
                <p class="mt-2 text-sm text-gray-500">صيغ مدعومة: JPG أو PNG.</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">رقم الهاتف</label>
                <input type="tel" id="phone" name="phone" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:border-primary-green focus:ring-primary-green">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">المدينة</label>
                    <input type="text" id="city" name="city" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:border-primary-green focus:ring-primary-green">
                </div>
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">تاريخ الميلاد</label>
                    <input type="date" id="birth_date" name="birth_date" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:border-primary-green focus:ring-primary-green">
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-primary-green hover:bg-dark-slate focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-green transition duration-300">
                    إرسال طلب الانخراط <i class="fas fa-paper-plane mr-2"></i>
                </button>
            </div>

            <p class="text-center text-sm text-gray-500 pt-4">
                لديك حساب بالفعل؟ <a href="<?php echo BASE_DIR . 'login.php'; ?>" class="font-medium text-secondary-blue hover:text-primary-green">تسجيل الدخول</a>
            </p>

        </form>
    </div>
</div>

<?php
include 'footer.php';
?>