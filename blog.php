<?php 
// تضمين ملف الإعدادات
include 'config.php';

// =======================================================
// 1. جلب مقالات المدونة المنشورة فقط
// =======================================================

// جلب المقالات من نوع 'blog' والمنشورة فقط، مع جلب اسم الفئة المرتبطة
$url = SUPABASE_URL . '/rest/v1/' . 'posts' . 
       '?post_type=eq.blog&is_published=eq.true' . // فلترة نوع ونشر المقال
       '&select=id,title_ar,summary_ar,slug,image_url,published_at,category:categories(name_ar)' . // جلب البيانات المطلوبة مع اسم الفئة
       '&order=published_at.desc'; // الأحدث أولاً

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
));
$response = curl_exec($ch);
curl_close($ch);

$posts = json_decode($response, true);
$is_error = !is_array($posts) || (isset($posts['code']) && $posts['code'] == 404);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدونة نادي بصمة الشباب - مقالات ورؤى</title>
    <meta name="description" content="اقرأ أحدث المقالات والرؤى والقصص الملهمة من مجتمع نادي بصمة الشباب.">
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
<body class="bg-neutral-gray font-sans">
    <?php include 'header.php'; ?>

    <main>
        
        <section class="py-16 bg-white border-b-4 border-accent-yellow/50">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-extrabold text-dark-slate mb-4">📝 مدونة بصمة الشباب</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    مقالات متخصصة في تطوير القيادة، التكنولوجيا، والعمل التطوعي، بقلم أعضاء النادي والخبراء.
                </p>
            </div>
        </section>

        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4">
                
                <?php if ($is_error || empty($posts)): ?>
                    <div class="text-center p-16 bg-white border-r-8 border-secondary-blue/50 rounded-xl shadow-lg">
                        <p class="text-2xl font-bold text-dark-slate mb-4">📖 عذراً، لا توجد مقالات مدونة حالياً.</p>
                        <p class="text-lg text-gray-700">ترقبوا مقالاتنا الجديدة قريباً!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                        <?php foreach ($posts as $post): ?>
                        <article class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100 transition duration-300 hover:shadow-secondary-blue/30 hover:-translate-y-1">
                            <div class="h-56 overflow-hidden">
                                <img 
                                    src="<?php echo htmlspecialchars($post['image_url'] ?: 'https://placehold.co/600x400/F9FAFB/1E293B?text=Basma+Blog'); ?>" 
                                    alt="<?php echo htmlspecialchars($post['title_ar']); ?>" 
                                    class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
                                >
                            </div>
                            <div class="p-6">
                                <div class="flex justify-between items-center text-xs font-semibold uppercase mb-3">
                                    <span class="text-primary-green">
                                        # <?php echo htmlspecialchars($post['category']['name_ar'] ?? 'عام'); ?>
                                    </span>
                                    <span class="text-gray-500">
                                        ⏰ <?php echo date('Y-m-d', strtotime($post['published_at'])); ?>
                                    </span>
                                </div>

                                <h3 class="text-2xl font-extrabold text-dark-slate mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars($post['title_ar']); ?>
                                </h3>
                                <p class="text-gray-600 text-base mb-6 line-clamp-3">
                                    <?php echo htmlspecialchars($post['summary_ar']); ?>
                                </p>
                                <a href="post-details.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="inline-flex items-center text-secondary-blue font-bold hover:text-accent-yellow transition duration-300 group">
                                    قراءة المقال 
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