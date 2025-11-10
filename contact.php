<?php 
// تضمين ملف الإعدادات
include 'config.php';

$success_message = '';
$error_message = '';

// =======================================================
// 1. معالجة إرسال النموذج (POST Request)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // تنظيف وتأمين البيانات
    $name = htmlspecialchars(trim($_POST['full_name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone_number'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message_content'] ?? ''));

    // التحقق الأساسي من صحة البيانات (لا يمكن أن تكون الحقول الأساسية فارغة)
    if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'الرجاء تعبئة جميع الحقول المطلوبة والتأكد من صحة البريد الإلكتروني.';
    } else {
        
        // إعداد البيانات للإرسال إلى Supabase
        $data = [
            'full_name' => $name,
            'email' => $email,
            'phone_number' => $phone,
            'subject' => $subject,
            'message_content' => $message
        ];

        $json_data = json_encode($data);

        // إعداد اتصال cURL
        $url = SUPABASE_URL . '/rest/v1/contact_messages';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); // طلب POST لإضافة سجل جديد
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'apikey: ' . SUPABASE_ANON_KEY,
            'Authorization: Bearer ' . SUPABASE_ANON_KEY,
            'Content-Type: application/json',
            'Prefer: return=minimal' // لا نطلب إعادة البيانات المضافة (أسرع)
        ));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // التحقق من حالة الرد
        if ($http_code == 201) {
            $success_message = 'تم استلام رسالتك بنجاح. سنرد عليك في أقرب وقت!';
            // إعادة تعيين المتغيرات لمسح النموذج بعد النجاح
            $name = $email = $phone = $subject = $message = '';
        } else {
            // يمكن تحليل $response لرسالة خطأ أكثر تفصيلاً
            $error_message = 'عذراً، حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة لاحقاً. (الكود: ' . $http_code . ')';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تواصل معنا | نادي بصمة الشباب</title>
    <meta name="description" content="تواصل مع نادي بصمة الشباب للاستفسار عن العضوية، الأنشطة، أو لأي تعاون.">
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
        
        <section class="py-16 bg-white border-b-4 border-secondary-blue/50">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-extrabold text-dark-slate mb-4">📞 تواصل معنا</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    نحن دائماً على استعداد للاستماع لاستفساراتكم، اقتراحاتكم، أو طلبات التعاون. 
                </p>
            </div>
        </section>

        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <div class="lg:col-span-1 p-8 bg-dark-slate rounded-xl shadow-2xl h-fit">
                    <h2 class="text-3xl font-bold text-white mb-6 border-b border-primary-green pb-2">بيانات النادي</h2>
                    
                    <ul class="space-y-6 text-lg text-gray-300">
                        <li class="flex items-center space-x-3 space-x-reverse">
                            <i class="fas fa-phone-alt text-primary-green w-6 h-6"></i>
                            <span class="font-bold text-white">الهاتف:</span>
                            <a href="tel:+966500000000" class="hover:text-primary-green transition duration-300"> +966 50 000 0000</a>
                        </li>
                        <li class="flex items-center space-x-3 space-x-reverse">
                            <i class="fas fa-envelope text-primary-green w-6 h-6"></i>
                            <span class="font-bold text-white">البريد:</span>
                            <a href="mailto:info@clubname.sa" class="hover:text-primary-green transition duration-300"> info@clubname.sa</a>
                        </li>
                        <li class="flex items-start space-x-3 space-x-reverse">
                            <i class="fas fa-map-marker-alt text-primary-green w-6 h-6 mt-1"></i>
                            <div class="flex-grow">
                                <span class="font-bold text-white block">العنوان:</span>
                                <span>المملكة العربية السعودية، مدينة الرياض، حي القدس.</span>
                            </div>
                        </li>
                    </ul>
                    
                    <div class="mt-10">
                         <h3 class="text-xl font-bold text-white mb-3 border-b border-accent-yellow pb-1">موقعنا</h3>
                         <div class="bg-gray-700 h-48 rounded-lg flex items-center justify-center text-gray-400">
                             
                             مساحة مخصصة لخريطة الموقع
                         </div>
                    </div>
                </div>

                <div class="lg:col-span-2 p-8 bg-white rounded-xl shadow-2xl border-t-8 border-secondary-blue/50">
                    <h2 class="text-3xl font-bold text-dark-slate mb-6">أرسل رسالتك</h2>
                    
                    <?php if ($success_message): ?>
                        <div class="p-4 mb-4 text-sm text-primary-green bg-green-100 rounded-lg border-r-4 border-primary-green shadow-md" role="alert">
                            <i class="fas fa-check-circle ml-2"></i> <?php echo $success_message; ?>
                        </div>
                        <?php $name = $email = $phone = $subject = $message = ''; // مسح البيانات في حالة النجاح ?>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border-r-4 border-red-500 shadow-md" role="alert">
                            <i class="fas fa-exclamation-triangle ml-2"></i> <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="full_name" class="block text-lg font-medium text-gray-700 mb-2">الاسم الكامل <span class="text-red-500">*</span></label>
                                <input type="text" id="full_name" name="full_name" required 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-300">
                            </div>
                            <div>
                                <label for="email" class="block text-lg font-medium text-gray-700 mb-2">البريد الإلكتروني <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone_number" class="block text-lg font-medium text-gray-700 mb-2">رقم الهاتف (اختياري)</label>
                                <input type="tel" id="phone_number" name="phone_number" 
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-300">
                            </div>
                            <div>
                                <label for="subject" class="block text-lg font-medium text-gray-700 mb-2">الموضوع</label>
                                <input type="text" id="subject" name="subject"
                                       value="<?php echo htmlspecialchars($subject ?? ''); ?>"
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-300">
                            </div>
                        </div>

                        <div>
                            <label for="message_content" class="block text-lg font-medium text-gray-700 mb-2">محتوى الرسالة <span class="text-red-500">*</span></label>
                            <textarea id="message_content" name="message_content" rows="6" required
                                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-300"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                        
                        <div>
                            <button type="submit" class="w-full px-8 py-3 text-xl font-bold text-white rounded-lg bg-secondary-blue hover:bg-primary-green transition duration-300 transform hover:scale-[1.01] shadow-xl">
                                إرسال الرسالة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        
    </main>

    <?php include 'footer.php'; ?>
    <script src="main.js"></script>

</body>
</html>