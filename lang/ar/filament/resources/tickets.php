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
    ],

    'fields' => [
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
        'title' => 'التعليقات',
        'internal_help' => 'الملاحظات الداخلية مرئية للفريق فقط، ولا تظهر للمُبلِّغ أبدًا.',
        'fields' => [
            'body' => 'الرسالة',
            'is_internal' => 'ملاحظة داخلية',
            'visibility' => 'الظهور',
            'author' => 'الكاتب',
        ],
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
            'assignee_changed' => 'تم تغيير المكلَّف',
        ],
    ],
];
