<?php 
// تضمين ملف الإعدادات
include 'config.php';

// =======================================================
// 1. استلام وتأمين الـ Slug
// =======================================================
$slug = isset($_GET['slug']) ? htmlspecialchars($_GET['slug']) : '';

if (empty($slug)) {
    // توجيه المستخدم إلى صفحة الأنشطة إذا لم يكن هناك slug
    header('Location: activities.php');
    exit;
}

// =======================================================
// 2. جلب بيانات النشاط والفئة المرتبطة به من Supabase
// =======================================================

$url = SUPABASE_URL . '/rest/v1/' . SUPABASE_ACTIVITIES_TABLE . 
       '?slug=eq.' . urlencode($slug) . 
       '&select=*,category:categories(name_ar)'; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
));
$response = curl_exec($ch);
curl_close($ch);

$activity_data = json_decode($response, true);
$activity = (!empty($activity_data) && is_array($activity_data)) ? $activity_data[0] : null;

// التحقق من وجود النشاط
if (!$activity) {
    header('Location: activities.php');
    exit;
}

// تعيين متغيرات النشاط
$title = htmlspecialchars($activity['title']);
$full_content = $activity['full_content']; 
$image_url = htmlspecialchars($activity['image_url'] ?: 'https://placehold.co/1200x600/F9FAFB/3B82F6?text=Club+Activity');
$activity_date = date('Y-m-d', strtotime($activity['activity_date']));
$category_name = $activity['category']['name_ar'] ?? 'عام';
$summary = htmlspecialchars($activity['summary']);

// =======================================================
// 3. إعداد بيانات المشاركة (ديناميكي)
// =======================================================

// الحصول على رابط الصفحة الحالي لضمان المشاركة الصحيحة
$share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// الرسالة الافتراضية للمشاركة
$share_text = urlencode("شاهدوا نشاط نادي بصمة الشباب الجديد: " . $title . " #نادي_بصمة_الشباب");
$encoded_url = urlencode($share_url);


// روابط المشاركة للمنصات (نستخدم أيقونات افتراضية هنا)
$social_links = [
    'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}",
    'twitter' => "https://twitter.com/intent/tweet?text={$share_text}&url={$encoded_url}",
    'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encoded_url}",
    'whatsapp' => "whatsapp://send?text={$share_text}%20{$encoded_url}", // يعمل فقط على الموبايل
];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | نادي بصمة الشباب</title>
    <meta name="description" content="<?php echo $summary; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* أنماط خاصة للمحتوى لضمان قراءة احترافية (مثل المدونات الكبرى) */
        .content-body h2 {
            font-size: 1.75rem; /* text-3xl */
            font-weight: 800; /* font-extrabold */
            color: #1E293B; /* dark-slate */
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-right: 4px solid #FBBF24; /* accent-yellow */
            padding-right: 0.75rem;
            line-height: 1.5;
        }
        .content-body p {
            font-size: 1.125rem; /* text-lg */
            line-height: 1.8;
            color: #4B5563; /* gray-600 */
            margin-bottom: 1.5rem;
        }
        .content-body ul, .content-body ol {
            padding-right: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .content-body ul li {
            list-style-type: disc;
            color: #10B981; /* primary-green */
            margin-bottom: 0.5rem;
        }
        .content-body ul li span {
            color: #4B5563;
        }
    </style>
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                'primary-green': '#10B981', 
                'secondary-blue': '#3B82F6',
                'accent-yellow': '#FBBF24', 
                'neutral-gray': '#F9FAFB', 
                'dark-slate': '#1E293B',
              },
              fontFamily: {
                  sans: ['"Noto Kufi Arabic"', 'sans-serif'],
              }
            }
          }
        }
    </script>
</head>
<body class="bg-neutral-gray font-sans">
    <?php include 'header.php'; ?>

    <main>
        
        <section class="relative">
            <div class="h-[50vh] md:h-[70vh] overflow-hidden">
                <img 
                    src="<?php echo $image_url; ?>" 
                    alt="<?php echo $title; ?>" 
                    class="w-full h-full object-cover shadow-xl"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-neutral-gray/90 to-transparent"></div> 
            </div>

            <div class="absolute bottom-0 left-0 right-0 max-w-5xl mx-auto px-4 lg:px-0 py-8 z-10">
                <div class="flex items-center space-x-4 space-x-reverse text-sm font-semibold mb-4">
                    <span class="px-3 py-1 bg-accent-yellow text-dark-slate rounded-full shadow-md">
                        # <?php echo $category_name; ?>
                    </span>
                    <span class="text-gray-700">
                        📅 تاريخ النشاط: **<?php echo $activity_date; ?>**
                    </span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-extrabold text-dark-slate mb-4 leading-tight drop-shadow-lg">
                    <?php echo $title; ?>
                </h1>
                <p class="text-lg text-gray-600 max-w-3xl">
                    <?php echo $summary; ?>
                </p>
            </div>
        </section>


        <section class="py-12 md:py-20">
            <div class="max-w-5xl mx-auto px-4 lg:px-0 bg-white p-6 md:p-12 rounded-xl shadow-2xl border-t-8 border-primary-green/50">
                
                <div class="content-body">
                    <?php 
                        echo $full_content; 
                    ?>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <h3 class="text-xl font-bold text-dark-slate mb-4">شارك هذا النشاط:</h3>
                    
                    <div class="flex space-x-4 space-x-reverse justify-start">
                        <a href="<?php echo $social_links['facebook']; ?>" target="_blank" title="شارك على فيسبوك" class="text-white w-12 h-12 flex items-center justify-center rounded-full bg-[#1877F2] hover:opacity-80 transition duration-300 shadow-lg">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="<?php echo $social_links['twitter']; ?>" target="_blank" title="شارك على تويتر (X)" class="text-white w-12 h-12 flex items-center justify-center rounded-full bg-[#1DA1F2] hover:opacity-80 transition duration-300 shadow-lg">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="<?php echo $social_links['whatsapp']; ?>" target="_blank" title="شارك عبر واتساب" class="text-white w-12 h-12 flex items-center justify-center rounded-full bg-[#25D366] hover:opacity-80 transition duration-300 shadow-lg">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                        <a href="<?php echo $social_links['linkedin']; ?>" target="_blank" title="شارك على لينكدإن" class="text-white w-12 h-12 flex items-center justify-center rounded-full bg-[#0A66C2] hover:opacity-80 transition duration-300 shadow-lg">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <hr class="my-10 border-gray-200">
                
                <div class="text-center">
                    <a href="activities.php" class="inline-flex items-center px-8 py-3 text-lg font-bold rounded-full bg-secondary-blue text-white hover:bg-primary-green transition duration-300 transform hover:scale-105 shadow-lg">
                        &rarr; العودة إلى سجل الأنشطة 
                    </a>
                </div>

            </div>
        </section>
        
    </main>

    <?php include 'footer.php'; ?>
    <script src="main.js"></script>
    
</body>
</html>