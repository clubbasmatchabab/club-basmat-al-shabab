<?php 
// تضمين ملف الإعدادات
include 'config.php';

// =======================================================
// 1. جلب الفئات (Categories)
// =======================================================
$categories_url = SUPABASE_URL . '/rest/v1/' . 'categories' . '?select=id,name_ar,slug&order=name_ar.asc';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $categories_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
));
$categories_response = curl_exec($ch);
curl_close($ch);
$categories = json_decode($categories_response, true);
if (!is_array($categories)) $categories = [];

// =======================================================
// 2. معالجة الفلترة والبحث
// =======================================================
$search_query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
$selected_category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// بناء رابط جلب الأنشطة مع الفلترة والبحث
$activities_url = SUPABASE_URL . '/rest/v1/' . SUPABASE_ACTIVITIES_TABLE . '?select=slug,title,summary,image_url,activity_date,id,category_id';

// إضافة فلترة الفئة
if ($selected_category_id > 0) {
    // يجب أن يكون لديك حقل category_id في جدول activities
    $activities_url .= '&category_id=eq.' . $selected_category_id;
}

// إضافة البحث الذكي (يتم البحث في العنوان والملخص)
if (!empty($search_query)) {
    // Supabase يستخدم فلترة ILIKE للبحث الجزئي في النصوص
    $activities_url .= '&or=(title.ilike.*' . urlencode($search_query) . '*,summary.ilike.*' . urlencode($search_query) . '*)';
}

// الترتيب (الأحدث أولاً)
$activities_url .= '&order=activity_date.desc';

// =======================================================
// 3. جلب الأنشطة
// =======================================================

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $activities_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
));
$response = curl_exec($ch);
curl_close($ch);

$activities = json_decode($response, true);
$is_error = !is_array($activities) || (isset($activities['code']) && $activities['code'] == 404);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أنشطة النادي | نادي بصمة الشباب</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-white font-sans">
    <?php include 'header.php'; ?>

    <main>
        
        <section class="py-16 bg-neutral-gray border-b-4 border-primary-green/50">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-extrabold text-dark-slate mb-4">📖 سجل أنشطتنا</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    تصفح جميع الفعاليات والأنشطة التي قام بها نادي بصمة الشباب.
                </p>
            </div>
        </section>

        <section class="py-10 bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4">
                <form method="GET" action="activities.php" class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-grow">
                        <input 
                            type="search" 
                            name="q" 
                            placeholder="ابحث عن نشاط بالاسم أو المحتوى..." 
                            value="<?php echo $search_query; ?>"
                            class="w-full py-3 pr-4 pl-12 border-2 border-gray-300 rounded-full focus:outline-none focus:border-secondary-blue transition duration-300 shadow-inner"
                        >
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <select 
                        name="category" 
                        onchange="this.form.submit()"
                        class="w-full md:w-56 py-3 px-4 border-2 border-gray-300 rounded-full focus:outline-none focus:border-secondary-blue appearance-none bg-white shadow-inner"
                    >
                        <option value="0">جميع الفئات (الكتالوج)</option>
                        <?php foreach ($categories as $cat): ?>
                            <option 
                                value="<?php echo $cat['id']; ?>" 
                                <?php echo ($selected_category_id == $cat['id']) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($cat['name_ar']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="w-full md:w-auto px-8 py-3 font-bold text-white rounded-full bg-primary-green hover:bg-green-700 transition duration-300 shadow-md transform hover:scale-[1.02]">
                        بحث
                    </button>
                    
                    <?php if (!empty($search_query) || $selected_category_id > 0): ?>
                        <a href="activities.php" class="w-full md:w-auto px-8 py-3 text-center font-bold text-dark-slate rounded-full border border-gray-300 hover:bg-neutral-gray transition duration-300 shadow-md">
                            إلغاء الفلترة
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4">
                
                <?php if ($is_error || empty($activities)): ?>
                    <div class="text-center p-16 bg-red-50 border-r-8 border-red-500 rounded-xl shadow-lg">
                        <p class="text-2xl font-bold text-dark-slate mb-4">
                            <?php 
                                if ($is_error) echo '⚠️ عذراً، خطأ في جلب البيانات.';
                                elseif (!empty($search_query)) echo "🔎 لا توجد نتائج للبحث: **$search_query**";
                                else echo 'لا توجد أنشطة حالية للعرض.';
                            ?>
                        </p>
                        <p class="text-lg text-gray-700">يرجى تغيير مصطلح البحث أو اختيار فئة أخرى.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                        <?php foreach ($activities as $activity): ?>
                        <article class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100 transition duration-300 hover:shadow-primary-green/30 hover:-translate-y-1">
                            <div class="h-56 overflow-hidden">
                                <img 
                                    src="<?php echo htmlspecialchars($activity['image_url'] ?: 'https://placehold.co/600x400/F9FAFB/3B82F6?text=Club+Activity'); ?>" 
                                    alt="<?php echo htmlspecialchars($activity['title']); ?>" 
                                    class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
                                >
                            </div>
                            <div class="p-6">
                                <span class="text-xs font-semibold uppercase text-secondary-blue block mb-2">
                                    📅 <?php echo date('Y-m-d', strtotime($activity['activity_date'])); ?>
                                </span>
                                <h3 class="text-2xl font-extrabold text-dark-slate mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars($activity['title']); ?>
                                </h3>
                                <p class="text-gray-600 text-base mb-6 line-clamp-3">
                                    <?php echo htmlspecialchars($activity['summary']); ?>
                                </p>
                                <a href="activity-details.php?slug=<?php echo htmlspecialchars($activity['slug']); ?>" class="inline-flex items-center text-primary-green font-bold hover:text-accent-yellow transition duration-300 group">
                                    قراءة التفاصيل كاملة 
                                    <span class="mr-2 text-xl transform group-hover:mr-3 transition-all">&larr;</span> 
                                </a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <script src="main.js"></script> 

</body>
</html>