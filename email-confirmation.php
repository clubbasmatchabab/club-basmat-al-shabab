<?php
// ملف: email-confirmation.php
// صفحة تظهر للمستخدم بعد إرسال طلب الانخراط بنجاح
include 'config.php';
session_start();

$page_title = "تأكيد البريد الإلكتروني - نادي بصمة الشباب";
$status = $_GET['status'] ?? 'error'; // يمكن أن يكون 'success' أو 'error'

include 'header.php';
?>

<div class="max-w-xl mx-auto px-4">
    <div class="bg-white p-8 md:p-10 rounded-xl shadow-2xl text-center">

        <?php if ($status === 'success'): ?>
            <div class="text-primary-green mb-6">
                <i class="fas fa-check-circle text-7xl animate-bounce"></i>
            </div>
            <h1 class="text-3xl font-bold text-dark-slate mb-4">نجاح التسجيل المبدئي!</h1>
            <p class="text-lg text-gray-700 mb-6 leading-loose">
                تم استلام طلب الانخراط بنجاح، وتبقى خطوة أخيرة ومهمة.
            </p>
            <div class="bg-blue-50 border-r-4 border-secondary-blue p-4 rounded-lg shadow-md mb-8">
                <p class="text-base text-gray-800 font-semibold flex items-center justify-center">
                    <i class="fas fa-envelope-open-text ml-2 text-secondary-blue"></i>
                    الرجاء **التحقق من بريدك الإلكتروني** الآن لإتمام عملية التسجيل وتأكيد حسابك.
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    (تأكد من مراجعة مجلد الرسائل غير المرغوب فيها/Spam إذا لم تجد الرسالة).
                </p>
            </div>
            
            <a href="index.php" class="inline-block px-8 py-3 bg-secondary-blue text-white font-bold rounded-lg hover:bg-primary-green transition duration-300 transform hover:scale-105">
                العودة إلى الصفحة الرئيسية <i class="fas fa-home ml-2"></i>
            </a>

        <?php else: ?>
            <div class="text-red-600 mb-6">
                <i class="fas fa-times-circle text-7xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-dark-slate mb-4">خطأ في التأكيد</h1>
            <p class="text-lg text-gray-700 mb-6 leading-loose">
                حدث خطأ ما أثناء معالجة طلبك أو أثناء التوجيه.
            </p>
            <a href="join.php" class="inline-block px-8 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-dark-slate transition duration-300 transform hover:scale-105">
                حاول التسجيل مرة أخرى <i class="fas fa-redo-alt ml-2"></i>
            </a>
        <?php endif; ?>

    </div>
</div>

<?php
include 'footer.php';
?>