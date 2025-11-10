<?php
// ملف: complete-profile.php
// لإكمال البيانات الناقصة بعد التسجيل الأولي أو تسجيل الدخول عبر Google

// =======================================================
// 1. التضمينات والمتغيرات الأساسية والإعدادات
// =======================================================
// تأكد من تحديث هذا الملف بالمتغيرات الصحيحة
include 'config.php'; 
session_start();

$user_data = $_SESSION['user_data'] ?? null;
$error_message = '';

// دالة وهمية: إرسال بريد إلكتروني (للتجربة المحلية)
function send_confirmation_email($recipient_email, $token) {
    error_log("Simulated confirmation email sent to: $recipient_email");
    return true; 
}

// =======================================================
// دالة: رفع ملف إلى Supabase Storage (يجب أن تكون موجودة في config.php أو مضمنة هنا)
// =======================================================
function upload_file_to_supabase_storage($file_data) {
    // ... (تضمين كود الدالة لرفع الملف كما هو في الرد السابق) ...
    global $error_message;
    if ($file_data['error'] !== UPLOAD_ERR_OK) return null;

    $extension = pathinfo($file_data['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('photo_') . '.' . $extension;
    $url = SUPABASE_URL . '/storage/v1/object/' . MEMBER_PHOTOS_BUCKET . '/' . $file_name;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file_data['tmp_name']));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY,
        'Content-Type: ' . $file_data['type'],
        'X-Upsert: true'
    ));
    
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 || $http_code === 201) {
        return SUPABASE_URL . '/storage/v1/object/public/' . MEMBER_PHOTOS_BUCKET . '/' . $file_name;
    } else {
        $error_message = "خطأ في رفع الصورة (رمز $http_code).";
        return null;
    }
}

// 2. التحقق من وجود بيانات المستخدم في الجلسة (إذا كان غير موجود يوجه لصفحة الانضمام)
if (!$user_data || empty($user_data['email']) || isset($user_data['is_profile_complete']) && $user_data['is_profile_complete'] === true) {
    header('Location: ' . BASE_DIR . 'join.php');
    exit;
}

// =======================================================
// 3. منطق معالجة النموذج عند الإرسال
// =======================================================
if (isset($_POST['submit_completion'])) {
    
    // جمع البيانات من النموذج
    $birth_date = $_POST['birth_date'] ?? '2000-01-01';
    $national_id_number = trim($_POST['national_id_number'] ?? '');
    
    // 1. التحقق من الصحة
    // (بقية التحقق من الصحة هنا...)
    if (empty($national_id_number) || !is_numeric($national_id_number) || strlen($national_id_number) < 8) {
        $error_message = 'الرجاء إدخال رقم بطاقة وطنية صحيح.';
    } elseif (!isset($_POST['terms_agreed'])) {
        $error_message = 'يجب الموافقة على الشروط والأحكام.';
    }

    // 2. منطق القاصرين والولي
    $is_minor = (strtotime($birth_date) > strtotime('-18 years'));
    $parent_consent_agreed = isset($_POST['parent_consent_agreed']);
    
    if ($is_minor && !$parent_consent_agreed) {
        $error_message = 'بصفتك قاصراً، يجب أن يوافق ولي أمرك.';
    }
    
    // 3. معالجة الرفع والتخزين
    $photo_url = $user_data['personal_photo_url'] ?? null; 
    
    if (!$error_message) {
        // رفع الصورة إذا تم إرسالها (تتم إضافة required في الـ HTML)
        if (isset($_FILES['personal_photo']) && $_FILES['personal_photo']['error'] === UPLOAD_ERR_OK) {
            $photo_url = upload_file_to_supabase_storage($_FILES['personal_photo']);
            if (!$photo_url) $error_message = $error_message ?: "فشل تحميل الصورة الشخصية.";
        } else if (empty($user_data['personal_photo_url'])) {
            // التحقق من أن الصورة مطلوبة إذا لم يتم رفعها مسبقًا (مثل Google profile pic)
            $error_message = "الرجاء إرفاق صورة شخصية لازمة لبطاقة العضوية.";
        }
        
        if (!$error_message) {
            $committee_choices = json_encode($_POST['committee_choices'] ?? []);

            // تجهيز البيانات للتحديث (تم حذف is_google_login الذي كان يسبب الخطأ)
            $update_data = [
                'application_status' => 'Pending_Approval', 
                'first_name_ar' => trim($_POST['first_name_ar'] ?? $user_data['first_name_ar']),
                'last_name_ar' => trim($_POST['last_name_ar'] ?? $user_data['last_name_ar']),
                'national_id_number' => $national_id_number,
                'birth_date' => $birth_date,
                'birth_place' => trim($_POST['birth_place'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'full_address' => trim($_POST['full_address'] ?? ''),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'profession_or_level' => trim($_POST['profession_or_level'] ?? ''),
                'personal_photo_url' => $photo_url, 
                'has_special_health_needs' => isset($_POST['has_special_health_needs']),
                'health_condition_description' => trim($_POST['health_condition_description'] ?? ''),
                'committee_choices' => $committee_choices,
                'parent_full_name' => $is_minor ? trim($_POST['parent_full_name'] ?? '') : null,
                'parent_national_id_number' => $is_minor ? trim($_POST['parent_national_id_number'] ?? '') : null,
                'parent_relationship' => $is_minor ? trim($_POST['parent_relationship'] ?? '') : null,
                'parent_consent_agreed' => $is_minor ? $parent_consent_agreed : true,
                'terms_agreed' => true,
                'is_profile_complete' => true 
            ];
            
            // 4. الإرسال إلى Supabase (PATCH) لتحديث السجل
            // نستخدم PATCH لتحديث سجل المستخدم الحالي بناءً على البريد الإلكتروني
            $url = SUPABASE_URL . '/rest/v1/member_applications?email=eq.' . urlencode($user_data['email']);
            
            $ch_db = curl_init();
            curl_setopt($ch_db, CURLOPT_URL, $url);
            curl_setopt($ch_db, CURLOPT_CUSTOMREQUEST, 'PATCH'); // PATCH للتحديث
            curl_setopt($ch_db, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch_db, CURLOPT_POSTFIELDS, json_encode($update_data));
            curl_setopt($ch_db, CURLOPT_HTTPHEADER, array(
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ));
            
            $response = curl_exec($ch_db);
            $http_code = curl_getinfo($ch_db, CURLINFO_HTTP_CODE);
            curl_close($ch_db);
            
            // 5. التوجيه
            if ($http_code == 200) {
                // إزالة بيانات المستخدم المؤقتة من الجلسة
                unset($_SESSION['user_data']);
                unset($_SESSION['is_google_login']);
                
                header('Location: ' . BASE_DIR . 'thank-you.php');
                exit;
            } else {
                $error_message = 'عذراً، حدث خطأ أثناء تحديث الملف الشخصي. قد تكون بعض البيانات مفقودة.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> 
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { /* الألوان بناءً على طلبك: الأخضر، الأزرق، الأصفر */
            --primary-green: #38a169; 
            --secondary-blue: #4299e1;
            --accent-yellow: #f6e05e;
            --dark-slate: #2d3748;
            --neutral-gray: #f7fafc;
        }
        .text-primary-green { color: var(--primary-green); }
        .bg-secondary-blue { background-color: var(--secondary-blue); }
        .bg-primary-green { background-color: var(--primary-green); }
        .step-indicator.active {
            border-bottom: 2px solid var(--secondary-blue);
            padding-bottom: 4px;
        }
    </style>
    <title>إكمال الملف الشخصي - نادي بصمة الشباب</title>
</head>
<body class="bg-neutral-gray font-sans">
    
<header class="bg-secondary-blue shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
        <div class="text-xl font-bold text-white">نادي بصمة الشباب</div>
        <nav class="hidden md:flex space-x-4 space-x-reverse">
            <a href="index.php" class="text-white hover:text-accent-yellow px-3 py-2 rounded-md text-sm font-medium">الرئيسية</a>
            <a href="media-center.php" class="text-white hover:text-accent-yellow px-3 py-2 rounded-md text-sm font-medium">المركز الإعلامي</a>
            <a href="activities.php" class="text-white hover:text-accent-yellow px-3 py-2 rounded-md text-sm font-medium">الأنشطة</a>
            <a href="contact.php" class="text-white hover:text-accent-yellow px-3 py-2 rounded-md text-sm font-medium">تواصل معنا</a>
        </nav>
        <div class="flex items-center space-x-4 space-x-reverse">
            <a href="join.php" class="px-4 py-2 bg-primary-green text-white font-bold rounded-lg hover:bg-green-700 transition duration-300">انضم إلينا الآن</a>
            <a href="login.php" class="px-4 py-2 text-primary-green bg-white font-bold rounded-lg hover:bg-gray-100 transition duration-300">تسجيل الدخول</a>
        </div>
    </div>
</header>
<main class="py-20">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-4xl font-extrabold text-dark-slate text-center mb-2">✅ إكمال ملفك الشخصي</h1>
            <p class="text-center text-lg text-gray-600 mb-10">تبقت خطوة واحدة لإرسال طلب الانخراط.</p>

            <div class="bg-white p-8 rounded-xl shadow-2xl border-t-8 border-secondary-blue/70">
                
                <div class="flex justify-between items-center mb-10 text-sm">
                    <div id="step-indicator-1" class="step-indicator active w-1/3 text-center text-secondary-blue font-bold">1. البيانات الإضافية</div>
                    <div id="step-indicator-2" class="step-indicator w-1/3 text-center text-gray-400">2. اللجان</div>
                    <div id="step-indicator-3" class="step-indicator w-1/3 text-center text-gray-400">3. الموافقة النهائية</div>
                </div>

                <form id="complete-profile-form" method="POST" action="complete-profile.php" enctype="multipart/form-data">
                    <input type="hidden" id="current-step" value="1">

                    <?php if ($error_message): ?>
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border-r-4 border-red-500 shadow-md"><i class="fas fa-times-circle ml-2"></i> <?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <div id="step-1" class="step-content space-y-6">
                        <h2 class="text-2xl font-bold text-secondary-blue mb-4 border-r-4 border-accent-yellow pr-2">أ. بيانات التعريف الناقصة</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><label class="block text-md font-medium text-gray-700 mb-1">البريد الإلكتروني (غير قابل للتعديل)</label><input type="email" disabled value="<?php echo htmlspecialchars($user_data['email']); ?>" class="w-full px-4 py-2 border rounded-lg bg-gray-100"></div>
                            
                            <div><label for="first_name_ar" class="block text-md font-medium text-gray-700 mb-1">الاسم الأول (عربي) <span class="text-red-500">*</span></label><input type="text" id="first_name_ar" name="first_name_ar" required value="<?php echo htmlspecialchars($user_data['first_name_ar'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg"></div>
                            <div><label for="last_name_ar" class="block text-md font-medium text-gray-700 mb-1">الاسم الأخير (عربي) <span class="text-red-500">*</span></label><input type="text" id="last_name_ar" name="last_name_ar" required value="<?php echo htmlspecialchars($user_data['last_name_ar'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg"></div>
                            
                            <div><label for="national_id_number" class="block text-md font-medium text-gray-700 mb-1">رقم البطاقة الوطنية <span class="text-red-500">*</span></label><input type="text" id="national_id_number" name="national_id_number" required class="w-full px-4 py-2 border rounded-lg"></div>
                            <div><label for="birth_date" class="block text-md font-medium text-gray-700 mb-1">تاريخ الميلاد <span class="text-red-500">*</span></label><input type="date" id="birth_date" name="birth_date" required class="w-full px-4 py-2 border rounded-lg" onchange="checkMinorStatus()"></div>
                            <div><label for="birth_place" class="block text-md font-medium text-gray-700 mb-1">مكان الميلاد</label><input type="text" id="birth_place" name="birth_place" class="w-full px-4 py-2 border rounded-lg"></div>
                            
                            <div>
                                <label for="city" class="block text-md font-medium text-gray-700 mb-1">المدينة الحالية <span class="text-red-500">*</span></label>
                                <input type="text" id="city" name="city" required class="w-full px-4 py-2 border rounded-lg" list="cities">
                                <datalist id="cities">
                                    <option value="الرباط">
                                    <option value="الدار البيضاء">
                                    <option value="أغادير">
                                    <option value="طنجة">
                                </datalist>
                            </div>
                            
                            <div><label for="phone_number" class="block text-md font-medium text-gray-700 mb-1">رقم الهاتف <span class="text-red-500">*</span></label><input type="tel" id="phone_number" name="phone_number" required class="w-full px-4 py-2 border rounded-lg"></div>
                            
                            <div>
                                <label for="profession_or_level" class="block text-md font-medium text-gray-700 mb-1">المهنة/المستوى الدراسي <span class="text-red-500">*</span></label>
                                <input type="text" id="profession_or_level" name="profession_or_level" required class="w-full px-4 py-2 border rounded-lg" list="professions">
                                <datalist id="professions">
                                    <option value="طالب بكالوريا">
                                    <option value="طالب جامعي">
                                    <option value="موظف">
                                    <option value="ريادي أعمال">
                                </datalist>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="full_address" class="block text-md font-medium text-gray-700 mb-1">العنوان الكامل <span class="text-red-500">*</span></label>
                                <textarea id="full_address" name="full_address" rows="2" required class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label for="personal_photo" class="block text-md font-medium text-gray-700 mb-1">صورة شخصية (للبطاقة العضوية) <span class="text-red-500">*</span></label>
                                <input type="file" id="personal_photo" name="personal_photo" accept="image/*" class="w-full border p-2 rounded-lg" <?php echo empty($user_data['personal_photo_url']) ? 'required' : ''; ?>>
                            </div>
                        </div>

                        <h2 class="text-2xl font-bold text-secondary-blue mt-8 mb-4 border-r-4 border-accent-yellow pr-2">ب. الوضع الصحي</h2>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="has_special_health_needs" name="has_special_health_needs" class="h-5 w-5 text-primary-green rounded" onchange="toggleHealthDescription()">
                                <label for="has_special_health_needs" class="ml-2 block text-md text-gray-700">هل لديك احتياجات صحية خاصة أو حالة طبية يجب أن نعلم بها؟</label>
                            </div>
                            <div id="health-description-container" class="mt-2 hidden">
                                <label for="health_condition_description" class="block text-md font-medium text-gray-700 mb-1">وصف الحالة الطبية (سري وغاية في الأهمية)</label>
                                <textarea id="health_condition_description" name="health_condition_description" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>
                        </div>

                        <button type="button" onclick="nextStep(1)" class="w-full md:w-auto mt-6 px-8 py-3 bg-secondary-blue text-white font-bold rounded-lg hover:bg-primary-green transition duration-300">
                            التالي: اختيار اللجان <i class="fas fa-arrow-left mr-2"></i>
                        </button>
                    </div>
                    
                    <div id="step-2" class="step-content hidden space-y-6">
                        <h2 class="text-2xl font-bold text-secondary-blue mb-4 border-r-4 border-accent-yellow pr-2">اختيار اللجان والمهارات</h2>
                        <p class="text-gray-600">يرجى اختيار 3 لجان كحد أقصى (تفضيل أول، ثانٍ، وثالث).</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php 
                            $committees = [
                                "لجنة التنظيم", "لجنة الإعلام والتواصل", "لجنة الموارد البشرية", 
                                "لجنة المالية والدعم", "لجنة المشاريع والابتكار"
                            ];
                            foreach ($committees as $index => $committee):
                            ?>
                            <div class="committee-choice p-4 border rounded-lg hover:bg-gray-50 transition duration-150">
                                <label class="inline-flex items-center w-full cursor-pointer">
                                    <input type="checkbox" name="committee_choices[]" value="<?php echo $committee; ?>" class="form-checkbox h-5 w-5 text-primary-green rounded" onchange="updateCommitteeSelection(this)">
                                    <span class="ml-2 text-md font-medium text-gray-700"><?php echo $committee; ?></span>
                                </label>
                                <div id="priority-<?php echo $index; ?>" class="hidden mt-2 text-sm font-semibold text-secondary-blue"></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex justify-between mt-6">
                            <button type="button" onclick="prevStep(2)" class="px-6 py-3 border border-gray-400 text-gray-700 font-bold rounded-lg hover:bg-gray-100 transition duration-300">
                                <i class="fas fa-arrow-right mr-2"></i> السابق
                            </button>
                            <button type="button" onclick="nextStep(2)" class="px-6 py-3 bg-secondary-blue text-white font-bold rounded-lg hover:bg-primary-green transition duration-300">
                                التالي: الموافقة النهائية <i class="fas fa-arrow-left mr-2"></i>
                            </button>
                        </div>
                    </div>

                    <div id="step-3" class="step-content hidden space-y-6">
                        <h2 class="text-2xl font-bold text-secondary-blue mb-4 border-r-4 border-accent-yellow pr-2">الموافقة على الشروط</h2>
                        
                        <div class="bg-gray-100 p-4 rounded-lg space-y-3">
                            <div class="flex items-start">
                                <input type="checkbox" id="terms_agreed" name="terms_agreed" required class="h-5 w-5 text-primary-green rounded mt-1">
                                <label for="terms_agreed" class="ml-2 block text-md text-gray-700">أوافق على الشروط والأحكام و سياسة الخصوصية. <span class="text-red-500">*</span></label>
                            </div>
                            <div class="flex items-start">
                                <input type="checkbox" id="subscribe_newsletter" name="subscribe_newsletter" class="h-5 w-5 text-primary-green rounded mt-1">
                                <label for="subscribe_newsletter" class="ml-2 block text-md text-gray-700">أوافق على استلام النشرة الإخبارية الدورية من النادي.</label>
                            </div>
                        </div>

                        <div id="parent-consent-section" class="space-y-4 p-6 border-2 border-dashed border-red-300 rounded-lg hidden">
                            <h3 class="text-xl font-bold text-red-700"><i class="fas fa-exclamation-triangle ml-2"></i> إقرار ولي الأمر (أنت قاصر)</h3>
                            <p class="text-gray-600">لأنك دون سن 18 عامًا، يجب أن يوافق ولي أمرك/الوصي القانوني.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div><label for="parent_full_name" class="block text-md font-medium text-gray-700 mb-1">اسم الولي الكامل <span class="text-red-500">*</span></label><input type="text" id="parent_full_name" name="parent_full_name" class="w-full px-4 py-2 border rounded-lg"></div>
                                <div><label for="parent_national_id_number" class="block text-md font-medium text-gray-700 mb-1">رقم بطاقة الولي الوطنية <span class="text-red-500">*</span></label><input type="text" id="parent_national_id_number" name="parent_national_id_number" class="w-full px-4 py-2 border rounded-lg"></div>
                                <div class="md:col-span-2"><label for="parent_relationship" class="block text-md font-medium text-gray-700 mb-1">الصلة بك (أب، أم، وصي) <span class="text-red-500">*</span></label><input type="text" id="parent_relationship" name="parent_relationship" class="w-full px-4 py-2 border rounded-lg"></div>
                            </div>
                            
                            <div class="flex items-start mt-4">
                                <input type="checkbox" id="parent_consent_agreed" name="parent_consent_agreed" class="h-5 w-5 text-red-700 rounded mt-1">
                                <label for="parent_consent_agreed" class="ml-2 block text-md font-bold text-red-700">أقر بأنني ولي أمر الطالب وأوافق على انضمامه للنادي. <span class="text-red-500">*</span></label>
                            </div>
                        </div>


                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="prevStep(3)" class="px-6 py-3 border border-gray-400 text-gray-700 font-bold rounded-lg hover:bg-gray-100 transition duration-300">
                                <i class="fas fa-arrow-right mr-2"></i> السابق
                            </button>
                            <button type="submit" name="submit_completion" class="px-8 py-3 bg-primary-green text-white font-extrabold rounded-lg hover:bg-secondary-blue transition duration-300 shadow-lg transform hover:scale-105">
                                إرسال طلب الانخراط النهائي <i class="fas fa-check-circle ml-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

<footer class="bg-dark-slate text-white mt-20 pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 border-b border-gray-700 pb-10">
            
            <div class="col-span-2 md:col-span-1">
                <h3 class="text-xl font-bold text-primary-green mb-4">نادي بصمة الشباب</h3>
                <p class="text-sm text-gray-400">نظام متكامل لإدارة الأعضاء والأنشطة والمالية، يدعم رؤيتنا في بناء قيادات شابة واعية ومؤثرة.</p>
            </div>

            <div>
                <h4 class="text-md font-semibold text-accent-yellow mb-4">روابط سريعة</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="about.php" class="text-gray-300 hover:text-white transition duration-200">من نحن</a></li>
                    <li><a href="laws.php" class="text-gray-300 hover:text-white transition duration-200">قوانين النادي</a></li>
                    <li><a href="news.php" class="text-gray-300 hover:text-white transition duration-200">آخر أخبار النادي</a></li>
                    <li><a href="blog.php" class="text-gray-300 hover:text-white transition duration-200">المدونة</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-md font-semibold text-accent-yellow mb-4">الدعم والمساعدة</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="privacy.php" class="text-gray-300 hover:text-white transition duration-200">سياسة الخصوصية</a></li>
                    <li><a href="terms.php" class="text-gray-300 hover:text-white transition duration-200">شروط الاستخدام</a></li>
                    <li><a href="faq.php" class="text-gray-300 hover:text-white transition duration-200">الأسئلة الشائعة</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-md font-semibold text-accent-yellow mb-4">تواصل معنا</h4>
                <ul class="space-y-2 text-sm">
                    <li><span class="text-gray-400">البريد:</span> info@basmatyouth.org</li>
                    <li><span class="text-gray-400">الهاتف:</span> +212 600 000 000</li>
                    <li class="flex space-x-3 space-x-reverse mt-2">
                        <a href="#" class="text-gray-400 hover:text-secondary-blue transition duration-200"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-secondary-blue transition duration-200"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-secondary-blue transition duration-200"><i class="fab fa-instagram"></i></a>
                    </li>
                </ul>
            </div>

             <div class="col-span-2 md:col-span-1">
                <h4 class="text-md font-semibold text-accent-yellow mb-4">النشرة الإخبارية</h4>
                <p class="text-sm text-gray-400 mb-3">اشترك لتصلك آخر أخبار النادي والأنشطة والفعاليات أولاً بأول.</p>
                 <form class="flex flex-col space-y-3">
                    <input type="email" placeholder="أدخل بريدك الإلكتروني" class="p-2 rounded-lg border-none bg-gray-700 text-white placeholder-gray-400 focus:ring-primary-green focus:border-primary-green">
                    <button type="submit" class="px-4 py-2 bg-primary-green text-white font-bold rounded-lg hover:bg-secondary-blue transition duration-300">اشترك الآن</button>
                </form>
            </div>

        </div>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            &copy; 2025 نادي بصمة الشباب. جميع الحقوق محفوظة.
        </div>
    </div>
</footer>
<script>
        // المتغيرات العامة لـ JavaScript
        let selectedCommittees = [];
        const maxCommittees = 3;
        const stepContents = document.querySelectorAll('.step-content');
        const stepIndicators = document.querySelectorAll('.step-indicator');
        
        // 1. وظيفة التبديل بين الحقول الصحية
        function toggleHealthDescription() {
            document.getElementById('health-description-container').classList.toggle('hidden');
        }

        // 2. وظيفة تحديث أولوية اللجان
        function updateCommitteeSelection(checkbox) {
            const committeeName = checkbox.value;
            const committeeContainer = checkbox.closest('.committee-choice');
            const priorityElement = committeeContainer.querySelector('div');

            if (checkbox.checked) {
                if (selectedCommittees.length >= maxCommittees) {
                    checkbox.checked = false;
                    alert("يمكنك اختيار 3 لجان كحد أقصى.");
                    return;
                }
                selectedCommittees.push(committeeName);
                priorityElement.textContent = `التفضيل رقم ${selectedCommittees.length}`;
                priorityElement.classList.remove('hidden');
            } else {
                const index = selectedCommittees.indexOf(committeeName);
                if (index > -1) {
                    selectedCommittees.splice(index, 1);
                }
                priorityElement.classList.add('hidden');
                
                // تحديث أرقام الأولوية المتبقية
                document.querySelectorAll('.committee-choice').forEach((choice) => {
                    const name = choice.querySelector('input[type="checkbox"]').value;
                    const newIndex = selectedCommittees.indexOf(name);
                    const priorityEl = choice.querySelector('div');
                    if (newIndex > -1) {
                        priorityEl.textContent = `التفضيل رقم ${newIndex + 1}`;
                        priorityEl.classList.remove('hidden');
                    } else {
                        priorityEl.classList.add('hidden');
                    }
                });
            }
        }
        
        // 3. وظيفة التحقق من القاصرين وعرض قسم الولي
        function checkMinorStatus() {
            const birthDateInput = document.getElementById('birth_date');
            const parentSection = document.getElementById('parent-consent-section');
            const requiredParentFields = parentSection.querySelectorAll('input:not([type="checkbox"])');

            if (!birthDateInput.value) return;

            const birthDate = new Date(birthDateInput.value);
            const eighteenYearsAgo = new Date();
            eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);

            if (birthDate > eighteenYearsAgo) {
                parentSection.classList.remove('hidden');
                requiredParentFields.forEach(field => field.setAttribute('required', 'required'));
                document.getElementById('parent_consent_agreed').setAttribute('required', 'required');
            } else {
                parentSection.classList.add('hidden');
                requiredParentFields.forEach(field => field.removeAttribute('required'));
                document.getElementById('parent_consent_agreed').removeAttribute('required');
            }
        }
        
        document.addEventListener('DOMContentLoaded', checkMinorStatus);


        // 4. وظيفة الانتقال للخطوة التالية
        function nextStep(currentStep) {
            const currentStepElement = document.getElementById(`step-${currentStep}`);
            
            const requiredInputs = currentStepElement.querySelectorAll('[required]');
            let isValid = true;
            requiredInputs.forEach(input => {
                if (input.type === 'file') {
                    // التحقق من ملف الصورة: إذا كان مطلوباً ولم يتم اختيار ملف
                    // Note: نستخدم PHP لتحديد إذا كان required، لكن نكرر هنا للتأكد
                    if (input.required && input.files.length === 0) {
                         // إذا كان حقل الصورة مطلوباً ولم يتم رفعه مسبقاً (كما هو محدد بالـ PHP)
                         input.reportValidity();
                         isValid = false;
                    }
                } else if (!input.value || (input.type === 'checkbox' && !input.checked)) {
                    input.reportValidity();
                    isValid = false;
                }
            });
            if (!isValid) return;

            if (currentStep === 2 && selectedCommittees.length === 0) {
                 alert("الرجاء اختيار لجنة واحدة على الأقل.");
                 return;
            }

            const nextStepNum = currentStep + 1;
            if (nextStepNum <= stepContents.length) {
                currentStepElement.classList.add('hidden');
                document.getElementById(`step-${nextStepNum}`).classList.remove('hidden');
                document.getElementById('current-step').value = nextStepNum;

                // تحديث مؤشرات التقدم
                stepIndicators.forEach((indicator, index) => {
                    indicator.classList.remove('active', 'text-secondary-blue', 'text-gray-400', 'text-primary-green');
                    if (index + 1 === nextStepNum) {
                        indicator.classList.add('active', 'text-secondary-blue');
                    } else if (index + 1 < nextStepNum) {
                        indicator.classList.add('text-primary-green');
                    } else {
                        indicator.classList.add('text-gray-400');
                    }
                });
            }
        }

        // 5. وظيفة الانتقال للخطوة السابقة
        function prevStep(currentStep) {
            const prevStepNum = currentStep - 1;
            if (prevStepNum >= 1) {
                document.getElementById(`step-${currentStep}`).classList.add('hidden');
                document.getElementById(`step-${prevStepNum}`).classList.remove('hidden');
                document.getElementById('current-step').value = prevStepNum;
                
                 stepIndicators.forEach((indicator, index) => {
                    indicator.classList.remove('active', 'text-secondary-blue', 'text-gray-400', 'text-primary-green');
                    if (index + 1 === prevStepNum) {
                        indicator.classList.add('active', 'text-secondary-blue');
                    } else if (index + 1 < prevStepNum) {
                        indicator.classList.add('text-primary-green');
                    } else {
                        indicator.classList.add('text-gray-400');
                    }
                });
            }
        }
    </script>
</body>
</html>