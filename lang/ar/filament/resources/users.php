<?php

return [
    'model' => 'مستخدم',
    'plural' => 'الموظفون',
    'navigation_label' => 'الموظفون',

    'sections' => [
        'account' => 'الحساب',
        'access' => 'الصلاحيات',
    ],

    'fields' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'role' => 'الدور',
        'reports_to' => 'يتبع لـ',
    ],

    'password_help' => 'اتركه فارغًا للإبقاء على كلمة المرور الحالية. الحد الأدنى 8 أحرف.',
    'no_manager' => 'بدون مدير مباشر',

    'messages' => [
        'cannot_report_to_self' => 'لا يمكن لموظف أن يتبع لنفسه.',
    ],
];
