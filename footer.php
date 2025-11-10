```php
<?php
// ملف: footer.php
// يحتوي على التذييل وإغلاق الوسوم وجميع دوال JavaScript

$club_name_ar = "نادي بصمة الشباب";
?>
        </div>
</main>

<footer class="bg-dark-slate text-white mt-24 py-16">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-10 md:gap-8 px-4">
        
        <div class="col-span-full md:col-span-2 border-b border-gray-700 md:border-none pb-6 md:pb-0">
            <h3 class="text-3xl font-black mb-4 text-primary-green">نادي بصمة الشباب</h3>
            <p class="text-sm text-gray-400 leading-relaxed max-w-sm">
                نظام متكامل لإدارة الأعضاء والمالية والأنشطة، يدعم رؤيتنا في بناء قيادات شبابية واعية ومؤثرة.
            </p>
            <p class="mt-8 text-xs text-gray-500">
                &copy; 2025 نادي بصمة الشباب. جميع الحقوق محفوظة.
            </p>
        </div>
        
        <div>
            <h3 class="text-lg font-bold mb-5 border-r-4 border-accent-yellow pr-2">الرئيسية</h3>
            <ul class="space-y-4 text-sm">
                <li><a href="index.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-home ml-2 w-4"></i> الرئيسية</a></li>
                <li><a href="activities.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-calendar-check ml-2 w-4"></i> أنشطتنا وفعالياتنا</a></li>
                <li><a href="news.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-bullhorn ml-2 w-4"></i> آخر أخبار النادي</a></li>
                <li><a href="blog.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-feather-alt ml-2 w-4"></i> مدونة الرؤى</a></li>
            </ul>
        </div>
        
        <div>
            <h3 class="text-lg font-bold mb-5 border-r-4 border-secondary-blue pr-2">الدعم واللوائح</h3>
            <ul class="space-y-4 text-sm">
                <li><a href="contact.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-headset ml-2 w-4"></i> تواصل معنا</a></li>
                <li><a href="documents.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-file-alt ml-2 w-4"></i> المستندات واللوائح</a></li>
                <li><a href="privacy-policy.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-user-shield ml-2 w-4"></i> سياسة الخصوصية</a></li>
                <li><a href="terms-conditions.php" class="text-gray-300 hover:text-accent-yellow transition duration-200"><i class="fas fa-file-contract ml-2 w-4"></i> شروط الاستخدام</a></li>
            </ul>
        </div>
        
        <div>
            <h3 class="text-lg font-bold mb-5 border-r-4 border-primary-green pr-2">النشرة البريدية</h3>
            <p class="text-sm text-gray-400 mb-4">اشترك الآن لتصلك آخر أخبار الأنشطة والإعلانات الهامة.</p>
            
            <form action="subscribe.php" method="POST" class="flex flex-col space-y-3">
                <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" required 
                       class="w-full px-3 py-2 text-sm text-dark-slate rounded-lg focus:ring-secondary-blue focus:border-secondary-blue border-none">
                <button type="submit" 
                        class="px-4 py-2 text-sm font-bold text-dark-slate rounded-lg bg-primary-green hover:bg-accent-yellow transition duration-300 transform hover:scale-[1.02]">
                    <i class="fas fa-paper-plane ml-2"></i> اشترك الآن
                </button>
                <a href="join.php" class="text-sm font-bold text-secondary-blue hover:text-white transition duration-200 mt-2 block">
                    <i class="fas fa-user-plus ml-2"></i> طلب انخراط كعضو
                </a>
            </form>
        </div>

    </div>
    
    <div class="max-w-7xl mx-auto px-4 mt-16 pt-8 border-t border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <p class="text-base font-bold text-white mb-4 sm:mb-0">تابع بصمة الشباب:</p>
            <div class="flex space-x-4 space-x-reverse text-2xl">
                <a href="#" class="text-gray-400 hover:text-accent-yellow transition duration-300" title="فيسبوك">
                    <i class="fab fa-facebook-f"></i>
                </a> 
                <a href="#" class="text-gray-400 hover:text-accent-yellow transition duration-300" title="تويتر">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-accent-yellow transition duration-300" title="إنستغرام">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-accent-yellow transition duration-300" title="لينكدإن">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menuToggle = document.getElementById('menu-toggle');
        const closeMenu = document.getElementById('close-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        // فتح القائمة
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.remove('translate-x-full');
                mobileMenu.classList.add('translate-x-0');
            });
        }

        // إغلاق القائمة
        if (closeMenu) {
            closeMenu.addEventListener('click', () => {
                mobileMenu.classList.remove('translate-x-0');
                mobileMenu.classList.add('translate-x-full');
            });
        }
    });

    // الدوال الخاصة بـ join.php
    const MIN_ADULT_AGE = 18;

    function toggleHealthDetails(show) {
        const container = document.getElementById('health_details_container');
        const textarea = document.getElementById('health_status_details');
        if (container) {
            if (show) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                // مسح القيمة لضمان عدم إرسال تفاصيل فارغة
                if(textarea) textarea.value = ''; 
            }
        }
    }

    function checkMinority(birthDateString) {
        const consentSection = document.getElementById('minority_consent_section');
        if (!consentSection) return;

        if (!birthDateString) {
            consentSection.style.display = 'none';
            return;
        }

        const today = new Date();
        const birthDate = new Date(birthDateString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < MIN_ADULT_AGE) {
            consentSection.style.display = 'block';
        } else {
            consentSection.style.display = 'none';
        }
    }
    
    // تشغيل التحقق عند تحميل الصفحة بناءً على بيانات النموذج المحفوظة
    document.addEventListener('DOMContentLoaded', () => {
        const birthDateInput = document.getElementById('birth_date');
        if (birthDateInput && birthDateInput.value) {
            checkMinority(birthDateInput.value);
        }
        const healthStatus = document.querySelector('input[name="health_status_requires_care"]:checked');
        if (healthStatus) {
            toggleHealthDetails(healthStatus.value === '1');
        } else {
             const container = document.getElementById('health_details_container');
             if (container) container.classList.add('hidden');
        }
    });
</script>

</body>
</html>