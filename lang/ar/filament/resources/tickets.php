<?php

return [
    'model' => 'تذكرة',
    'plural' => 'التذاكر',
    'navigation_label' => 'التذاكر',

    'tabs' => [
        'all' => 'الكل',
        'unassigned' => 'غير مكلَّفة',
        'open' => 'مفتوحة',
        'in_progress' => 'قيد العمل',
        'resolved' => 'تم حلها',
    ],

    'sections' => [
        'details' => 'تفاصيل التذكرة',
        'reporter' => 'المُبلِّغ',
        'triage' => 'الفرز',
        'timeline' => 'الجدول الزمني',
        'pms' => 'نظام المشاريع',
        'browser' => 'متصفح المُبلِّغ',
        'screenshots' => 'لقطات الشاشة (:count)',
        'ai' => 'الفرز بالذكاء الاصطناعي',
    ],

    'fields' => [
        'category' => 'التصنيف',
        'ai_priority' => 'الأولوية المقترحة بالذكاء الاصطناعي',
        'ai_confidence' => 'ثقة الذكاء الاصطناعي',
        'ai_summary' => 'ملخص الذكاء الاصطناعي (إنجليزي)',
        'ai_summary_ar' => 'ملخص الذكاء الاصطناعي (عربي)',
        'source_app' => 'التطبيق المصدر',
        'ai_model' => 'نموذج الذكاء الاصطناعي',
        'ai_classified_at' => 'تاريخ التصنيف',
        'user_agent' => 'المتصفح',
        'viewport' => 'حجم نافذة العرض',
        'console_errors' => 'أخطاء وحدة التحكم',
        'pms_sync_status' => 'حالة المزامنة مع نظام المشاريع',
        'pms_last_error' => 'آخر خطأ في نظام المشاريع',
        'ticket_number' => 'رقم التذكرة',
        'title' => 'العنوان',
        'description' => 'الوصف',
        'page_url' => 'رابط الصفحة',
        'locale' => 'لغة التذكرة',
        'attachments' => 'المرفقات',
        'reporter_name' => 'اسم المُبلِّغ',
        'reporter_email' => 'بريد المُبلِّغ',
        'status' => 'الحالة',
        'priority' => 'الأولوية',
        'assignee' => 'المكلَّف',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'آخر تحديث',
    ],

    'unassigned' => 'غير مكلَّف',
    'no_console_errors' => 'لم يتم تسجيل أخطاء في وحدة التحكم.',
    'ai' => [
        'apply_priority' => 'تطبيق الأولوية المقترحة',
        'priority_applied' => 'تم تطبيق الأولوية المقترحة بالذكاء الاصطناعي.',
        'rerun' => 'إعادة الفرز بالذكاء الاصطناعي',
        'queued' => 'تمت جدولة الفرز بالذكاء الاصطناعي.',
        'unclassified' => 'غير مصنَّفة',
    ],
    'no_screenshots' => 'لا توجد لقطات شاشة مرفقة بهذه التذكرة.',
    'screenshot_label' => 'لقطة :number',
    'open_full_size' => 'فتح بالحجم الكامل',

    'messages' => [
        'invalid_transition' => 'تغيير الحالة الحالية إلى هذه الحالة غير مسموح.',
        'team_lead_required_to_close' => 'لا يمكن إغلاق تذكرة غير محلولة إلا من قبل قائد الفريق أو المدير العام.',
    ],

    'comments' => [
        'title' => 'التعليقات',
        'internal_help' => 'Internal notes are visible only to staff and are never shown to the reporter.',
        'authors' => [
            'reporter' => 'المُبلِّغ',
            'system' => 'النظام',
        ],
        'fields' => [
            'body' => 'الرسالة',
            'is_internal' => 'ملاحظة داخلية',
            'visibility' => 'الظهور',
            'author' => 'الكاتب',
        ],
    ],

    'pms' => [
        'push_action' => 'إرسال إلى نظام المشاريع',
        'queued' => 'تمت جدولة الإرسال إلى نظام المشاريع.',
        'completed' => 'أكمل فريق التطوير المهمة.',
        'cancelled' => 'أغلق فريق التطوير هذا الطلب بوصفه ملغى.',
    ],

    'activities' => [
        'title' => 'سجل النشاط',
        'system' => 'النظام',
        'change' => 'من :from إلى :to',
        'fields' => [
            'event' => 'الحدث',
            'change' => 'التغيير',
            'actor' => 'بواسطة',
        ],
        'events' => [
            'ticket_created' => 'تم إنشاء التذكرة',
            'status_changed' => 'تم تغيير الحالة',
            'priority_changed' => 'تم تغيير الأولوية',
            'ai_priority_applied' => 'رفع الذكاء الاصطناعي الأولوية',
            'ai_triage_no_result' => 'تعذّر على الذكاء الاصطناعي تصنيف التذكرة',
            'ai_triage_failed' => 'فشل تصنيف الذكاء الاصطناعي',
            'assignee_changed' => 'تم تغيير المكلَّف',
        ],
    ],
];
