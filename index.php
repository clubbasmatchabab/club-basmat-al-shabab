<?php 
// تضمين ملف الإعدادات
include 'config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نادي بصمة الشباب - الصفحة الرئيسية</title>
    <meta name="description" content="نادي بصمة الشباب: نظام شامل لإدارة الأعضاء والأنشطة والمالية. رؤيتنا للشباب الواعد.">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Keyframes for a smooth entry effect */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down {
            animation: fadeInDown 0.8s ease-out forwards;
        }
        /* Style for the professional button glow */
        .btn-glow {
            /* Shadow using primary-green color */
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.7); 
        }
    </style>
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                'primary-green': '#10B981', // تركواز/أخضر أساسي
                'secondary-blue': '#3B82F6', // أزرق ثانوي
                'accent-yellow': '#FBBF24', // أصفر للإبراز
                'neutral-gray': '#F9FAFB', // رمادي فاتح للخلفيات
                'dark-slate': '#1E293B', // لون داكن للنص/الخلفية
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
        
        <?php 
        // =======================================================
        // ** إعدادات المحتوى المتحرك  **
        // =======================================================
        
        // 1. رابط الفيديو (YouTube أو ملف مباشر).
        $youtube_embed_url = "https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=1&loop=1&playlist=dQw4w9WgXcQ&controls=0&modestbranding=1&rel=0"; 
        
        // 2. صور شرائح السلايدر (تستخدم إذا لم يكن هناك فيديو).
        $slides = [
            ["url" => "images/youth-activity-1.jpg", "alt" => "أنشطة القيادة الشبابية"], // تأكد من وجود الصور في مجلد images/
            ["url" => "images/activity-1.jpg", "alt" => "تطوع مجتمعي"],
            ["url" => "images/activity-2.jpg", "alt" => "ورش عمل رقمية"],
        ];
        
        // 3. مفتاح التحويل: ضعه TRUE لاستخدام الفيديو، FALSE لاستخدام الصور المتحركة.
        $use_video_as_background = FALSE; 
        
        // =======================================================
        ?>

        <section id="hero-slider" class="relative min-h-[60vh] md:min-h-[85vh] overflow-hidden bg-dark-slate flex justify-center items-center rounded-b-3xl shadow-2xl">
            
            <div id="media-container" class="absolute inset-0">
                
                <?php if ($use_video_as_background): ?>
                    <iframe 
                        class="w-full h-full object-cover opacity-80" 
                        src="<?php echo $youtube_embed_url; ?>" 
                        frameborder="0" 
                        allow="autoplay; encrypted-media" 
                        allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <?php foreach ($slides as $index => $slide): ?>
                        <img 
                            src="<?php echo htmlspecialchars($slide['url']); ?>" 
                            alt="<?php echo htmlspecialchars($slide['alt']); ?>" 
                            class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out opacity-0" 
                            data-slide-index="<?php echo $index; ?>"
                        >
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="absolute inset-0 bg-gradient-to-b from-gray-900/60 to-gray-900/80"></div> 
            </div>

            <div class="relative z-10 text-center max-w-5xl px-4 py-8">
                <h1 class="text-6xl md:text-8xl font-black text-white mb-6 leading-tight drop-shadow-xl animate-fade-in-down">
                    <span class="block text-accent-yellow">بصمة</span>
                    <span class="block">القيادة الشبابية.</span>
                </h1>
                <p class="text-xl md:text-2xl text-neutral-gray max-w-3xl mx-auto drop-shadow-lg opacity-0 animate-fade-in-down" style="animation-delay: 0.3s;">
                    نادي بصمة الشباب: نطور، ننظم، ونمكّن، لخلق جيل يقود المستقبل بثقة ووعي.
                </p>
                <div class="mt-10 opacity-0 animate-fade-in-down" style="animation-delay: 0.6s;">
                     <a href="join.php" class="inline-block px-12 py-4 text-xl font-extrabold rounded-full bg-primary-green text-white shadow-2xl btn-glow hover:bg-green-600 hover:shadow-primary-green/70 transition duration-300 transform hover:scale-105">
                        اكتشف كيف تكون بصمة
                    </a>
                </div>
            </div>
        </section>
        
        <section class="py-12 bg-white -mt-10 relative z-20">
            <div class="max-w-6xl mx-auto text-center px-4 md:flex justify-between items-center bg-secondary-blue/5 rounded-2xl shadow-lg p-6 border-b-4 border-accent-yellow">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-0">لا تكن مجرد رقم، كن بصمة في مجتمعك!</h2>
                <a href="join.php" class="inline-block px-8 py-3 text-lg font-bold rounded-full bg-accent-yellow text-dark-slate shadow-xl hover:bg-white hover:text-primary-green transition duration-300 transform hover:scale-105 border-2 border-transparent hover:border-accent-yellow">
                    انضم إلينا الآن
                </a>
            </div>
        </section>

        <section class="py-20 bg-neutral-gray">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h2 class="text-4xl md:text-5xl font-extrabold text-dark-slate mb-4">لماذا تنضم لنادي بصمة الشباب؟</h2>
                <p class="text-xl text-gray-500 mb-16">نحن نقدم تجربة متكاملة لتنمية قدراتك ومهاراتك الحياتية والمهنية.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    
                    <div class="group p-8 rounded-3xl shadow-xl border-b-8 border-primary-green bg-white transform transition duration-500 hover:shadow-primary-green/40 hover:-translate-y-2">
                        <div class="text-primary-green group-hover:text-accent-yellow mb-5 transition duration-300 mx-auto w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div> 
                        <h3 class="text-2xl font-extrabold text-secondary-blue mb-4">صناعة القادة</h3>
                        <p class="text-gray-600 leading-relaxed">برامج تدريبية متخصصة تركز على مهارات القيادة، التخطيط الاستراتيجي، وإدارة الأزمات لتكون قائداً مؤثراً.</p>
                    </div>

                    <div class="group p-8 rounded-3xl shadow-xl border-b-8 border-secondary-blue bg-white transform transition duration-500 hover:shadow-secondary-blue/40 hover:-translate-y-2">
                         <div class="text-secondary-blue group-hover:text-primary-green mb-5 transition duration-300 mx-auto w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div> 
                        <h3 class="text-2xl font-extrabold text-secondary-blue mb-4">مجتمع متكامل</h3>
                        <p class="text-gray-600 leading-relaxed">تواصل مع شباب طموح من مختلف التخصصات لبناء شبكة علاقات مهنية وشخصية واسعة ومستدامة.</p>
                    </div>

                    <div class="group p-8 rounded-3xl shadow-xl border-b-8 border-accent-yellow bg-white transform transition duration-500 hover:shadow-accent-yellow/40 hover:-translate-y-2">
                        <div class="text-accent-yellow group-hover:text-secondary-blue mb-5 transition duration-300 mx-auto w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="12" x2="12" y1="17" y2="21"/><line x1="8" x2="16" y1="21" y2="21"/></svg>
                        </div>
                        <h3 class="text-2xl font-extrabold text-secondary-blue mb-4">بيئة رقمية منظمة</h3>
                        <p class="text-gray-600 leading-relaxed">نظامنا الإداري يسهل عليك تتبع الحضور، والأنشطة، والحصول على الشهادات بضغطة زر وبكل شفافية.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-12 border-b-4 border-primary-green/20 pb-4">
                    <h2 class="text-4xl md:text-5xl font-extrabold text-dark-slate">📌 آخر المستجدات والأنشطة</h2>
                    <a href="activities.php" class="mt-4 sm:mt-0 text-lg font-semibold text-primary-green hover:text-secondary-blue transition duration-300 transform hover:translate-x-1 border-b-2 border-primary-green">
                        إظهار المزيد من الأنشطة &larr;
                    </a>
                </div>

                <?php 
                // كود جلب الأنشطة من Supabase
                $url = SUPABASE_URL . '/rest/v1/' . SUPABASE_ACTIVITIES_TABLE . '?select=slug,title,summary,image_url,activity_date&order=activity_date.desc&limit=3';
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'apikey: ' . SUPABASE_ANON_KEY,
                    'Authorization: Bearer ' . SUPABASE_ANON_KEY
                ));
                $response = curl_exec($ch);
                curl_close($ch);
                
                $activities = json_decode($response, true);

                // عرض الأنشطة 
                if (is_array($activities) && !empty($activities)):
                ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($activities as $activity): ?>
                    <article class="bg-neutral-gray rounded-xl shadow-2xl overflow-hidden border border-gray-100 transition duration-300 hover:shadow-primary-green/20 hover:border-primary-green">
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
                            <a href="activity-details.php?slug=<?php echo htmlspecialchars($activity['slug']); ?>" class="inline-flex items-center text-primary-green font-bold hover:text-accent-yellow transition duration-300">
                                قراءة التفاصيل كاملة 
                                <span class="mr-2 text-xl transform group-hover:mr-3 transition-all">&larr;</span> 
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-center text-xl text-gray-500 p-10 bg-neutral-gray rounded-xl shadow-inner">لا توجد أنشطة حالية للعرض. يرجى إضافة أنشطة جديدة.</p>
                <?php endif; ?>
                
                <div class="text-center mt-12 lg:hidden">
                    <a href="activities.php" class="text-lg font-semibold text-primary-green hover:text-secondary-blue transition duration-300 border-b-2 border-primary-green">
                        إظهار المزيد من الأنشطة &larr;
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        // كود JavaScript لتشغيل السلايدر (Hero Slider)
        document.addEventListener('DOMContentLoaded', () => {
            const mediaContainer = document.getElementById('media-container');
            // جمع جميع الشرائح (الصور) التي تحمل خاصية data-slide-index
            const slides = mediaContainer.querySelectorAll('img[data-slide-index]');
            
            // الخروج إذا كان هناك فيديو أو أقل من شريحتين (لا حاجة للتحريك)
            // (مفتاح $use_video_as_background هو الذي يتحكم بهذا)
            if (<?php echo $use_video_as_background ? 'true' : 'false'; ?> || slides.length <= 1) return; 

            let currentIndex = 0;
            const intervalTime = 6000; // 6 ثوانٍ للتحول

            function updateSlider() {
                // إخفاء جميع الشرائح
                slides.forEach(slide => {
                    slide.classList.remove('opacity-100');
                    slide.classList.add('opacity-0');
                    slide.style.zIndex = 10; // طبقة منخفضة
                });

                // الانتقال إلى الشريحة التالية (دائري)
                currentIndex = (currentIndex + 1) % slides.length;

                // إظهار الشريحة الحالية
                const currentSlide = slides[currentIndex];
                currentSlide.classList.remove('opacity-0');
                currentSlide.classList.add('opacity-100');
                currentSlide.style.zIndex = 20; // طبقة علوية
            }

            // تفعيل الشريحة الأولى فوراً
            slides[0].classList.add('opacity-100');
            slides[0].style.zIndex = 20;

            // بدء مؤقت التحديث
            setInterval(updateSlider, intervalTime);
        });

        // تشغيل تأثيرات الدخول بعد تحميل الصفحة (لضمان عمل animation-delay)
        window.onload = () => {
            document.querySelectorAll('.animate-fade-in-down').forEach(el => {
                el.style.opacity = 1; 
            });
        };
    </script>
    <script src="main.js"></script>
</body>
</html>