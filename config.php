<?php
// ملف: config.php
// إعدادات وثوابت المشروع لنادي بصمة الشباب

// =========================================================
// 🔒 الإعدادات السرية (يتم قراءتها من متغيرات البيئة في Render)
// =========================================================

// إعدادات Supabase
define('SUPABASE_URL', getenv('SUPABASE_URL')); 
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY')); 
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY')); 

// إعدادات Appwrite
define('APPWRITE_ENDPOINT', getenv('APPWRITE_ENDPOINT'));
define('APPWRITE_PROJECT_ID', getenv('APPWRITE_PROJECT_ID'));
define('APPWRITE_API_KEY', getenv('APPWRITE_API_KEY'));       
define('MEMBER_PHOTOS_BUCKET', 'member_photos'); // معرف الدلو (Bucket ID)

// =========================================================
// 🌐 الإعدادات العامة (يمكنك تعيينها مباشرة أو قراءتها من Env Vars)
// =========================================================

// القاعدة الأساسية للموقع (ضرورية لـ cURL والتوجيهات)
// سنفترض قراءتها من متغير بيئة 'BASE_URL' لسهولة التغيير في Render
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/club-basmat-al-shabab-website/');

// المسار الأساسي (قد تحتاج لتغييره يدوياً إذا لم يتم استخدامه بشكل جيد في الكود)
define('BASE_DIR', '/club-basmat-al-shabab-website/'); 

// إعداد تقارير الأخطاء (للمرحلة التجريبية)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// وقت المنطقة الزمنية (المغرب/الدار البيضاء)
date_default_timezone_set('Africa/Casablanca');
?>