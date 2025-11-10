// =======================================================
// 1. تفعيل القائمة المتجاوبة (Mobile Off-Canvas Menu)
// =======================================================

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const closeMenu = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');

    // فتح القائمة الجانبية
    menuToggle.addEventListener('click', () => {
        // إزالة translate-x-full (إخفاء) وإضافة translate-x-0 (إظهار)
        mobileMenu.classList.remove('translate-x-full');
        mobileMenu.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden'; // منع التمرير في الخلفية
    });

    // إغلاق القائمة الجانبية
    closeMenu.addEventListener('click', () => {
        // إزالة translate-x-0 (إظهار) وإضافة translate-x-full (إخفاء)
        mobileMenu.classList.remove('translate-x-0');
        mobileMenu.classList.add('translate-x-full');
        document.body.style.overflow = 'auto'; // السماح بالتمرير مرة أخرى
    });
});


// =======================================================
// 2. تفعيل الشريط المتحرك للصور (Hero Slider Carousel)
// =======================================================

// يجب أن تكون هذه الصور موجودة في مجلد images/
const sliderImages = [
    "images/youth-activity-1.jpg",
    "images/activity-1.jpg",
    "images/activity-2.jpg"
];

let currentSlideIndex = 0;
const slideDuration = 5000; // 5 ثواني
const currentSlideElement = document.getElementById('current-slide');

// هذه الدالة تغير الصورة وتضيف تأثيراً انتقالياً
function nextSlide() {
    if (!currentSlideElement) return; // الخروج إذا لم يكن العنصر موجوداً (في حالة استخدام الفيديو)

    // تقليل الشفافية تدريجياً (تأثير التلاشي للخارج - fade-out)
    currentSlideElement.classList.remove('opacity-80');
    currentSlideElement.classList.add('opacity-0');
    
    // الانتظار قليلاً لتطبيق تأثير التلاشي قبل تغيير المصدر
    setTimeout(() => {
        currentSlideIndex = (currentSlideIndex + 1) % sliderImages.length;
        currentSlideElement.src = sliderImages[currentSlideIndex];
        
        // إعادة الشفافية تدريجياً (تأثير التلاشي للداخل - fade-in)
        currentSlideElement.classList.remove('opacity-0');
        currentSlideElement.classList.add('opacity-80');
    }, 1000); // 1000ms = مدة transition التي وضعناها في CSS
}

// البدء في التحريك التلقائي إذا كنا نستخدم الصور
if (currentSlideElement) {
    setInterval(nextSlide, slideDuration);
}