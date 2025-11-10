<?php
// process-login.php - يقوم بمعالجة التوكن المرسل من check-profile.php
include 'config.php'; 

$access_token = $_GET['token'] ?? '';

if (empty($access_token)) {
    header('Location: login.php?error=missing_token');
    exit;
}

// =======================================================
// 1. استخدام التوكن لجلب بيانات المستخدم من Supabase
// =======================================================
// يجب أن نستخدم Supabase Auth API أو JWT Decoder لقراءة البريد، لكن
// سنستخدم هنا الطريقة الأسهل عبر Supabase REST API (أقل أماناً لكن يعمل)

$url = SUPABASE_URL . '/auth/v1/user'; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . SUPABASE_ANON_KEY,
    // نستخدم التوكن كـ Bearer Token للوصول لبيانات المستخدم
    'Authorization: Bearer ' . $access_token 
));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$user_auth_data = json_decode($response, true);

if ($http_code != 200 || !isset($user_auth_data['email'])) {
    header('Location: login.php?error=invalid_token');
    exit;
}

$user_email = $user_auth_data['email'];
// =======================================================
// 2. التحقق من اكتمال بيانات المستخدم في member_applications
// =======================================================
$url_db = SUPABASE_URL . '/rest/v1/member_applications?select=national_id_number,application_status&email=eq.' . urlencode($user_email);

$ch_db = curl_init();
// ... (تنفيذ cURL لجلب البيانات من member_applications) ...
// ... (نفس كود check-profile.php السابق) ...

$user_data = json_decode($response, true);

if (empty($user_data)) {
    // مستخدم جديد يسجل لأول مرة عبر Google
    header('Location: complete-profile.php?email=' . urlencode($user_email) . '&is_new=true');
    exit;
} else {
    $member = $user_data[0];
    if (empty($member['national_id_number']) || $member['application_status'] == 'Incomplete_Profile') {
        // الملف ناقص
        header('Location: complete-profile.php?email=' . urlencode($user_email) . '&is_new=false');
        exit;
    } else {
        // الملف مكتمل - توجيه للوحة المستخدم
        header('Location: member-dashboard.php'); 
        exit;
    }
}
?>