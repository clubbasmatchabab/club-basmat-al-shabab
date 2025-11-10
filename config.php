<?php
// ملف: config.php
// إعدادات وثوابت المشروع لنادي بصمة الشباب

// إعدادات Supabase
define('SUPABASE_URL', 'https://phuzhwpnmgsgqvqjnppf.supabase.co'); 
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBodXpod3BubWdzZ3F2cWpucHBmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjIzNzMwMTEsImV4cCI6MjA3Nzk0OTAxMX0.N96LjI9Q7qZaWxjgWMIIIdb9TaVBEisTRfpitrKM7qY'); 

// 🚨 هام لعملية التراجع (Rollback) في join.php
// يجب استخدام مفتاح Service Role Key (مفتاح سري لا يجب الكشف عنه للعميل)
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBodXpod3BubWdzZ3F2cWpucHBmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MjM3MzAxMSwiZXhwIjoyMDc3OTQ5MDExfQ.hAza9eZCPHX4AHhy67MOU9RBnMC_zJhTqkS8j06pwgc'); 

// إعدادات التخزين
define('APPWRITE_ENDPOINT', 'https://fra.cloud.appwrite.io/v1'); // أو عنوان خادمك السحابي
define('APPWRITE_PROJECT_ID', '6911e309003df0b411fa'); // استبدل بـ Project ID
define('APPWRITE_API_KEY', 'standard_8340dde6dc0cb80bb46860722ff08003ec21318e0897bfe088d88f2ae195007e32dbd50a7fd949c26321cf649fe6ec012308f1f0d3bb9031f34e844e7898c77650fce88e9bfa92490915f0c924b5b72fe118e64e7d3d90fea3cc789007f7ad4a8283ecde47d581e0227e882bf371e5c559984181f19092b3e68459f1463847a8');       // استبدل بـ API Key الذي أنشأته
define('MEMBER_PHOTOS_BUCKET', 'member_photos');          // المعرف الذي اخترن
// إعدادات عامة
define('BASE_DIR', '/club-basmat-al-shabab-website/');
// إعداد تقارير الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// وقت المنطقة الزمنية (مثال: إفريقيا/الدار البيضاء)
date_default_timezone_set('Africa/Casablanca');
?>