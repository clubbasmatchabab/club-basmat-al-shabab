<?php
// ملف: header.php
// يحتوي على رأس الصفحة، وسمي <head>، تحميل المكتبات، وفتح وسم <body>

// يُفترض أن $page_title يتم تعريفه في الصفحة المستدعية (مثل join.php)
// تأكد من تضمين config.php في الصفحة الرئيسية (join.php) قبل استدعاء هذا الملف

$club_name_ar = "نادي بصمة الشباب";
$page_title_default = $page_title ?? $club_name_ar;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> 
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* تعريف الألوان الخاصة بالنادي (Green, Blue, Yellow) */
        :root { 
            --primary-green: #38a169; /* أخضر النادي */
            --secondary-blue: #4299e1; /* أزرق النادي */
            --accent-yellow: #f6e05e; /* أصفر النادي */
            --dark-slate: #2d3748; /* لون داكن للخلفيات */
            --neutral-gray: #f7fafc;
        }
        .text-primary-green { color: var(--primary-green); }
        .bg-secondary-blue { background-color: var(--secondary-blue); }
        .bg-primary-green { background-color: var(--primary-green); }
        .bg-dark-slate { background-color: var(--dark-slate); }
        .bg-neutral-gray { background-color: var(--neutral-gray); }
        .border-primary-green { border-color: var(--primary-green); }
        
        /* تأثير القائمة المنسدلة (Desktop Dropdown) */
        .group:hover .group-hover\:scale-y-100 {
            transform: scaleY(1);
            opacity: 1;
        }
        /* تصحيح القائمة المنسدلة لكي لا تختفي عندما يتحرك المؤشر */
        .group-hover\:scale-y-100 { 
            transform: scaleY(0); 
            opacity: 0;
            transform-origin: top; 
            transition: transform 300ms ease, opacity 300ms ease;
        }
    </style>
    <title><?php echo $page_title_default; ?></title>
</head>
<body class="bg-neutral-gray font-sans min-h-screen flex flex-col antialiased">

<header class="shadow-xl sticky top-0 z-50 bg-secondary-blue border-b-4 border-primary-green">
    <nav class="flex justify-between items-center h-20 max-w-7xl mx-auto px-4 lg:px-8">
        
        <div class="text-2xl font-extrabold text-white tracking-wider">
            نادي <span class="text-accent-yellow">بصمة</span> الشباب
        </div>
        
        <div class="space-x-8 space-x-reverse hidden lg:flex font-semibold text-lg">
            <a href="index.php" class="text-white hover:text-accent-yellow transition duration-300 py-2 px-1">الرئيسية</a>
            
            <div class="relative group">
                <button class="text-white hover:text-accent-yellow transition duration-300 py-2 px-1 flex items-center">
                    المركز الإعلامي <i class="fas fa-caret-down mr-1"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white text-dark-slate rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition duration-300 transform scale-y-0 origin-top z-50">
                    <a href="news.php" class="block px-4 py-2 hover:bg-neutral-gray transition duration-200">الأخبار</a>
                    <a href="blog.php" class="block px-4 py-2 hover:bg-neutral-gray transition duration-200">المدونة</a>
                    <a href="documents.php" class="block px-4 py-2 hover:bg-neutral-gray transition duration-200">اللوائح والوثائق</a>
                </div>
            </div>

            <a href="activities.php" class="text-white hover:text-accent-yellow transition duration-300 py-2 px-1">الأنشطة</a> 
            <a href="contact.php" class="text-white hover:text-accent-yellow transition duration-300 py-2 px-1">تواصل معنا</a> 
        </div>
        
        <div class="hidden md:flex space-x-4 space-x-reverse items-center">
            
            <a href="login.php" class="px-3 py-2 text-md font-bold rounded-lg bg-primary-green text-white hover:bg-accent-yellow hover:text-dark-slate shadow-md transition duration-300 transform hover:scale-105">
                <i class="fas fa-sign-in-alt ml-2"></i> تسجيل الدخول
            </a>
            
            <a href="join.php" class="hidden lg:block px-5 py-2 text-md font-bold rounded-full bg-accent-yellow text-gray-900 hover:bg-white hover:text-secondary-blue shadow-md transition duration-300 transform hover:scale-105">
                انضم إلينا الآن
            </a>
        </div>
        
        <button id="menu-toggle" class="lg:hidden text-white hover:text-accent-yellow text-3xl p-1" aria-label="فتح القائمة">
            &#9776;
        </button>
    </nav>
</header>

<div id="mobile-menu" class="fixed inset-0 z-50 transform translate-x-full transition-transform duration-500 ease-in-out lg:hidden bg-dark-slate/95 backdrop-blur-sm">
    <div class="h-full w-64 bg-dark-slate text-white shadow-2xl p-6 absolute right-0">
        <div class="flex justify-end mb-8">
            <button id="close-menu" class="text-white hover:text-accent-yellow text-3xl">&times;</button>
        </div>
        <nav class="space-y-4 text-xl font-bold">
            <a href="index.php" class="block p-2 rounded hover:bg-gray-700 transition duration-200">الرئيسية</a>
            <a href="activities.php" class="block p-2 rounded hover:bg-gray-700 transition duration-200">الأنشطة</a>
            <a href="news.php" class="block p-2 rounded hover:bg-gray-700 transition duration-200">الأخبار</a>
            <a href="blog.php" class="block p-2 rounded hover:bg-gray-700 transition duration-200">المدونة</a>
            <a href="documents.php" class="block p-2 rounded hover:bg-gray-700 transition duration-200">الوثائق واللوائح</a>
            <a href="contact.php" class="block p-2 rounded hover:bg-gray-700 transition duration-200">تواصل معنا</a>
            <a href="login.php" class="block mt-6 text-center px-4 py-2 text-lg font-bold rounded-lg bg-primary-green text-white hover:bg-accent-yellow hover:text-dark-slate transition duration-300">
                تسجيل الدخول
            </a>
            <a href="join.php" class="block mt-2 text-center px-4 py-2 text-lg font-bold rounded-full bg-accent-yellow text-gray-900 hover:bg-white hover:text-secondary-blue transition duration-300">
                انضم إلينا
            </a>
        </nav>
    </div>
</div>

<main class="flex-grow py-12">
    <div class="max-w-4xl mx-auto px-4">