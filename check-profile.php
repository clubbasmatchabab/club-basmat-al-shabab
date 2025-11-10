<?php
// ملف: check-profile.php
// يقوم بمعالجة رمز المصادقة (token) من Supabase بعد تسجيل الدخول بـ Google
// ويقرر ما إذا كان يجب التوجيه لإكمال الملف الشخصي أو إلى لوحة التحكم.

include 'config.php';
session_start();

// 1. التحقق من وجود رمز المصادقة (token) في الرابط
if (isset($_GET['access_token']) && isset($_GET['refresh_token'])) {
    
    $access_token = $_GET['access_token'];
    
    // 2. استخدام الـ token للحصول على بيانات المستخدم من Supabase
    $user_url = SUPABASE_URL . '/auth/v1/user';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'apikey: ' . SUPABASE_ANON_KEY,
        // استخدام Access Token للمصادقة على المستخدم
        'Authorization: Bearer ' . $access_token 
    ));
    
    $user_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $supabase_user = json_decode($user_response, true);
        $user_email = $supabase_user['email'];

        // 3. البحث في جدول 'member_applications' باستخدام البريد الإلكتروني
        $db_url = SUPABASE_URL . '/rest/v1/member_applications?email=eq.' . urlencode($user_email) . '&select=*';

        $ch_db = curl_init();
        curl_setopt($ch_db, CURLOPT_URL, $db_url);
        curl_setopt($ch_db, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_db, CURLOPT_HTTPHEADER, array(
            'apikey: ' . SUPABASE_ANON_KEY,
            'Authorization: Bearer ' . SUPABASE_ANON_KEY
        ));
        
        $db_response = curl_exec($ch_db);
        $http_code_db = curl_getinfo($ch_db, CURLINFO_HTTP_CODE);
        curl_close($ch_db);

        $member_applications = json_decode($db_response, true);

        if ($http_code_db == 200 && !empty($member_applications)) {
            $member_data = $member_applications[0];
            
            // تم العثور على المستخدم في قاعدة البيانات
            
            // أ. تخزين بيانات الجلسة
            $_SESSION['user_auth_token'] = $access_token;
            $_SESSION['user_refresh_token'] = $_GET['refresh_token'];
            $_SESSION['user_data'] = $member_data;
            $_SESSION['is_logged_in'] = true;
            
            // ب. منطق التوجيه بناءً على حالة الملف الشخصي
            if (!isset($member_data['is_profile_complete']) || $member_data['is_profile_complete'] === false) {
                // إذا كان الملف غير مكتمل، وجّه إلى إكمال الملف
                $_SESSION['is_google_login'] = true; // علامة لنموذج الإكمال
                header('Location: ' . BASE_DIR . 'complete-profile.php');
                exit;
            } else {
                // إذا كان مكتمل، وجّه إلى لوحة تحكم الأعضاء
                header('Location: ' . BASE_DIR . 'member-dashboard.php');
                exit;
            }

        } else {
            // حالة: تسجيل دخول Google جديد تماماً ولم يتم إنشاء صف في DB بعد
            // يجب إنشاء سجل مبدئي في جدول member_applications
            
            $first_name = $supabase_user['user_metadata']['full_name'] ?? $supabase_user['email'];
            $last_name = '';

            // محاولة تقسيم الاسم إذا كان اسم كامل متوفر
            $name_parts = explode(' ', $first_name);
            if (count($name_parts) > 1) {
                $first_name = $name_parts[0];
                $last_name = end($name_parts);
            }

            $initial_data = [
                'email' => $user_email,
                'first_name_ar' => $first_name,
                'last_name_ar' => $last_name,
                'is_google_login' => true,
                'application_status' => 'Pending_Completion',
                'is_profile_complete' => false
            ];
            
            $insert_url = SUPABASE_URL . '/rest/v1/member_applications';
            $ch_insert = curl_init();
            curl_setopt($ch_insert, CURLOPT_URL, $insert_url);
            curl_setopt($ch_insert, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch_insert, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch_insert, CURLOPT_POSTFIELDS, json_encode($initial_data));
            curl_setopt($ch_insert, CURLOPT_HTTPHEADER, array(
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ));
            
            $insert_response = curl_exec($ch_insert);
            $http_code_insert = curl_getinfo($ch_insert, CURLINFO_HTTP_CODE);
            curl_close($ch_insert);

            if ($http_code_insert == 201) {
                // تخزين البيانات الأولية في الجلسة والتوجيه للإكمال
                $_SESSION['user_data'] = json_decode($insert_response, true)[0];
                $_SESSION['is_google_login'] = true;
                header('Location: ' . BASE_DIR . 'complete-profile.php');
                exit;
            } else {
                // فشل إنشاء السجل - توجيه إلى صفحة خطأ أو تسجيل الدخول
                error_log("Supabase insert failed: " . $insert_response);
                header('Location: ' . BASE_DIR . 'join.php?error=db_fail');
                exit;
            }
        }
    } else {
        // فشل الحصول على بيانات المستخدم من Supabase
        header('Location: ' . BASE_DIR . 'join.php?error=auth_fail');
        exit;
    }
} else {
    // لم يتم العثور على رمز مميز (token) في الرابط (فشل Google OAuth)
    header('Location: ' . BASE_DIR . 'join.php?error=token_missing');
    exit;
}
?>