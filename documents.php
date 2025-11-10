<?php 
// تضمين ملف الإعدادات
include 'config.php';

// دالة مساعدة لتحويل حجم الملف إلى صيغة سهلة القراءة (KB, MB)
function format_file_size($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// =======================================================
// 1. جلب المستندات العامة المنشورة
// =======================================================

// جلب المستندات التي مستوى وصولها 'Public' مع اسم الفئة
$url = SUPABASE_URL . '/rest/v1/' . 'documents' . 
       '?access_level=eq.Public' . // فلترة المستندات العامة فقط
       '&select=id,title_ar,summary_ar,document_url,file_extension,file_size_bytes,uploaded_at,category:categories(name_ar)' . 
       '&order=uploaded_at.desc';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
));
$response = curl_exec($ch);
curl_close($ch);

$documents = json_decode($response, true);
$is_error = !is_array($documents) || (isset($documents['code']) && $documents['code'] == 404);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مستندات النادي | نادي بصمة الشباب</title>
    <meta name="description" content="تصفح وحمل الوثائق الرسمية، اللوائح، والمستندات العامة لنادي بصمة الشباب.">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        function getFileIcon(extension) {
            extension = extension ? extension.toLowerCase() : '';
            switch(extension) {
                case 'pdf': return 'fas fa-file-pdf text-red-600';
                case 'docx': 
                case 'doc': return 'fas fa-file-word text-blue-600';
                case 'xlsx': 
                case 'xls': return 'fas fa-file-excel text-green-600';
                case 'pptx': 
                case 'ppt': return 'fas fa-file-powerpoint text-orange-600';
                case 'zip':
                case 'rar': return 'fas fa-file-archive text-gray-600';
                default: return 'fas fa-file text-secondary-blue';
            }
        }
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
                <h1 class="text-5xl md:text-6xl font-extrabold text-dark-slate mb-4">📚 مكتبة المستندات</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    هنا تجدون جميع المستندات الرسمية، اللوائح، والوثائق العامة لنادي بصمة الشباب.
                </p>
            </div>
        </section>

        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4">
                
                <?php if ($is_error || empty($documents)): ?>
                    <div class="text-center p-16 bg-blue-50 border-r-8 border-secondary-blue rounded-xl shadow-lg">
                        <p class="text-2xl font-bold text-dark-slate mb-4">💡 لا توجد مستندات عامة متاحة حالياً.</p>
                        <p class="text-lg text-gray-700">سيتم إضافة لوائح النادي والتقارير العامة فور اعتمادها.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="space-y-6">
                        <?php foreach ($documents as $doc): ?>
                        
                        <?php 
                            // استخدام دالة JavaScript لتحديد الأيقونة بناءً على امتداد الملف
                            $extension = htmlspecialchars($doc['file_extension'] ?? '');
                            $size = format_file_size($doc['file_size_bytes'] ?? 0);
                        ?>

                        <article class="flex items-start bg-white p-6 rounded-xl shadow-lg border-r-8 border-secondary-blue/70 transition duration-300 hover:shadow-xl hover:border-secondary-blue">
                            
                            <div class="flex-shrink-0 ml-6 pt-1">
                                <i class="text-5xl" 
                                   data-file-icon="<?php echo $extension; ?>"
                                   id="icon-<?php echo $doc['id']; ?>"></i>
                                <script>
                                    // تنفيذ دالة JavaScript هنا لتغيير الأيقونة واللون ديناميكياً
                                    document.getElementById('icon-<?php echo $doc['id']; ?>').className = getFileIcon('<?php echo $extension; ?>');
                                </script>
                            </div>
                            
                            <div class="flex-grow">
                                <h3 class="text-2xl font-extrabold text-dark-slate mb-2">
                                    <?php echo htmlspecialchars($doc['title_ar']); ?>
                                </h3>
                                <p class="text-gray-600 text-base mb-3">
                                    <?php echo htmlspecialchars($doc['summary_ar']); ?>
                                </p>
                                
                                <div class="flex items-center text-sm text-gray-500 space-x-6 space-x-reverse">
                                    <span>
                                        <i class="fas fa-tag text-primary-green ml-1"></i> الفئة: **<?php echo htmlspecialchars($doc['category']['name_ar'] ?? 'غير مصنف'); ?>**
                                    </span>
                                    <span>
                                        <i class="fas fa-database text-accent-yellow ml-1"></i> الحجم: **<?php echo $size; ?>**
                                    </span>
                                    <span>
                                        <i class="fas fa-clock text-secondary-blue ml-1"></i> تاريخ الرفع: **<?php echo date('Y-m-d', strtotime($doc['uploaded_at'])); ?>**
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0 mr-6 pt-1">
                                <a href="<?php echo htmlspecialchars($doc['document_url']); ?>" target="_blank" 
                                   class="inline-flex items-center px-6 py-2 font-bold text-white rounded-lg bg-primary-green hover:bg-green-700 transition duration-300 shadow-md transform hover:scale-105"
                                   download>
                                    <i class="fas fa-download ml-2"></i> تحميل
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