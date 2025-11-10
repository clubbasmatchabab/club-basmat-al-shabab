<?php
// تضمين ملف الإعدادات
include 'config.php';

// تعيين الصفحة الافتراضية لإعادة التوجيه
$redirect_to = 'index.php';

// تحديد الصفحة التي أتى منها المستخدم (لإعادة التوجيه إليها)
if (isset($_SERVER['HTTP_REFERER'])) {
    // محاولة استخلاص اسم الملف من الرابط المرجعي (مثلاً: activities.php)
    $path_info = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    $redirect_to = basename($path_info);
}

// =======================================================
// 1. معالجة إرسال نموذج الاشتراك
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // تنظيف وتأمين البريد الإلكتروني
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));

    // التحقق من صحة البريد الإلكتروني
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // إعادة التوجيه مع رسالة خطأ
        header('Location: ' . $redirect_to . '?sub_status=error&msg=' . urlencode('الرجاء إدخال بريد إلكتروني صحيح.'));
        exit;
    } 

    // إعداد البيانات للإرسال إلى Supabase
    $data = [
        'email' => $email,
        'source' => 'Footer Subscription', // تحديد المصدر
        'is_active' => true // تفعيل الاشتراك مباشرة
    ];

    $json_data = json_encode($data);

    // إعداد اتصال cURL لإضافة مشترك جديد
    $url = SUPABASE_URL . '/rest/v1/newsletter_subscribers';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY,
        'Content-Type: application/json',
        'Prefer: return=minimal'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // =======================================================
    // 2. التحقق من حالة الرد
    // =======================================================
    if ($http_code == 201) {
        // نجاح الإضافة
        header('Location: ' . $redirect_to . '?sub_status=success&msg=' . urlencode('شكراً لاشتراكك! ستصلك آخر التحديثات قريباً.'));
        exit;
    } elseif ($http_code == 409) {
        // رمز 409 يعني غالباً تعارض (Conflict)، وهو ما يحدث عند محاولة إدخال بريد مكرر (UNIQUE constraint)
        header('Location: ' . $redirect_to . '?sub_status=warning&msg=' . urlencode('أنت مشترك بالفعل في نشرتنا البريدية!'));
        exit;
    } else {
        // فشل الإضافة لأي سبب آخر
        // يمكن تحليل $response لرسالة خطأ أكثر تفصيلاً، لكن سنستخدم رسالة عامة
        header('Location: ' . $redirect_to . '?sub_status=error&msg=' . urlencode('عذراً، حدث خطأ أثناء محاولة الاشتراك. يرجى المحاولة لاحقاً.'));
        exit;
    }
} else {
    // توجيه في حال محاولة الوصول المباشر للصفحة بدون POST
    header('Location: ' . $redirect_to);
    exit;
}
?>