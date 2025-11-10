<?php 
// يتطلب متغيرات محددة ليتم تمريرها من الملف الذي يستدعيه
if (!isset($page_title) || !isset($page_slug) || !isset($page_content)) {
    // توجيه في حالة الاستدعاء الخاطئ
    header('Location: index.php');
    exit;
}

// تضمين ملف الإعدادات
include 'config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | نادي بصمة الشباب</title>
    <meta name="description" content="اقرأ <?php echo htmlspecialchars($page_title); ?> لنادي بصمة الشباب.">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-5xl font-extrabold text-dark-slate mb-4">
                    <i class="fas fa-gavel text-secondary-blue ml-2"></i> <?php echo htmlspecialchars($page_title); ?>
                </h1>
            </div>
        </section>

        <section class="py-20">
            <div class="max-w-4xl mx-auto px-4 bg-white p-8 md:p-12 rounded-xl shadow-2xl">
                <div class="text-lg text-gray-700 leading-relaxed space-y-6">
                    <?php 
                        // طباعة المحتوى الذي تم تمريره من الملف المستدعي
                        echo $page_content; 
                    ?>
                </div>
            </div>
        </section>
        
    </main>

    <?php include 'footer.php'; ?>
    <script src="main.js"></script> 

</body>
</html>