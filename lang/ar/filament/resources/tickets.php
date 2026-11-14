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
    ],

    'fields' => [
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

    'messages' => [
        'invalid_transition' => 'تغيير الحالة الحالية إلى هذه الحالة غير مسموح.',
        'team_lead_required_to_close' => 'لا يمكن إغلاق تذكرة غير محلولة إلا من قبل قائد الفريق أو المدير العام.',
    ],

    'comments' => [
        'title' => 'Comments',
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
            'assignee_changed' => 'تم تغيير المكلَّف',
        ],
    ],
];
