<?php
// ملف: config.php
// إعدادات وثوابت المشروع لنادي بصمة الشباب

// =========================================================
// 🚨 تحذير أمني: يتم قراءة هذه المفاتيح الآن من متغيرات البيئة في Vercel.
// لا تقم أبداً بوضع المفاتيح السرية (الأسرار) هنا مباشرة!
// =========================================================

// إعدادات Supabase
define('SUPABASE_URL', getenv('SUPABASE_URL')); 
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY')); 
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY')); 

// إعدادات Appwrite
define('APPWRITE_ENDPOINT', getenv('APPWRITE_ENDPOINT'));
define('APPWRITE_PROJECT_ID', getenv('APPWRITE_PROJECT_ID'));
define('APPWRITE_API_KEY', getenv('APPWRITE_API_KEY'));       
define('MEMBER_PHOTOS_BUCKET', getenv('MEMBER_PHOTOS_BUCKET') ?: 'member_photos');

// =========================================================
// 🌐 الإعدادات العامة
// =========================================================

// القاعدة الأساسية للموقع (سيتم قراءة رابط Vercel منها)
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/club-basmat-al-shabab-website/');

// المسار الأساسي
define('BASE_DIR', '/club-basmat-al-shabab-website/'); 

// إعداد تقارير الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// وقت المنطقة الزمنية 
date_default_timezone_set('Africa/Casablanca');
?>