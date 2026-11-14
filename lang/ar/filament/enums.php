<?php

return [
    'ticket_status' => [
        'open' => 'مفتوحة',
        'triaged' => 'تم فرزها',
        'pushed_to_pms' => 'مُرسلة إلى نظام المشاريع',
        'in_progress' => 'قيد العمل',
        'resolved' => 'تم حلها',
        'closed' => 'مغلقة',
    ],
    'ticket_priority' => [
        'low' => 'منخفضة',
        'medium' => 'متوسطة',
        'high' => 'عالية',
        'critical' => 'حرجة',
    ],
    'ticket_locale' => [
        'en' => 'الإنجليزية',
        'ar' => 'العربية',
    ],
    'ticket_source' => [
        'manual' => 'يدوية',
        'api' => 'تطبيق مصدر',
    ],
    'user_type' => [
        'staff' => 'موظف',
        'service' => 'حساب خدمة',
    ],
    'comment_author_type' => [
        'staff' => 'موظف',
        'client' => 'المُبلِّغ',
        'system' => 'النظام',
    ],
    'project_sync_status' => [
        'pending' => 'قيد الانتظار',
        'synced' => 'متزامنة',
        'failed' => 'فشلت',
        'out_of_sync' => 'غير متزامنة',
    ],
    'webhook_processing_status' => [
        'pending' => 'قيد الانتظار',
        'processed' => 'تمت المعالجة',
        'failed' => 'فشلت',
        'duplicate' => 'مكررة',
    ],
    'user_role' => [
        'super_admin' => 'مدير عام',
        'team_lead' => 'قائد فريق',
        'support_agent' => 'موظف دعم',
    ],
];
