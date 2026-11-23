<?php

return [
    'ticket_status' => [
        'open' => 'Open',
        'triaged' => 'Triaged',
        'pushed_to_pms' => 'Pushed to PMS',
        'in_progress' => 'In progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ],
    'ticket_priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ],
    'ticket_category' => [
        'user_error' => 'User error',
        'how_to' => 'How-to',
        'bug' => 'Bug',
        'feature_request' => 'Feature request',
        'code_change' => 'Code change',
        'config_or_infra' => 'Config / infrastructure',
        'unclear' => 'Unclear',
    ],
    'ticket_locale' => [
        'en' => 'English',
        'ar' => 'Arabic',
    ],
    'ticket_source' => [
        'manual' => 'Manual',
        'api' => 'Source app',
    ],
    'user_type' => [
        'staff' => 'Staff',
        'service' => 'Service account',
    ],
    'comment_author_type' => [
        'staff' => 'Staff',
        'client' => 'Reporter',
        'system' => 'System',
    ],
    'project_sync_status' => [
        'pending' => 'Pending',
        'synced' => 'Synced',
        'failed' => 'Failed',
        'out_of_sync' => 'Out of sync',
    ],
    'webhook_processing_status' => [
        'pending' => 'Pending',
        'processed' => 'Processed',
        'failed' => 'Failed',
        'duplicate' => 'Duplicate',
    ],
    'user_role' => [
        'super_admin' => 'Super Admin',
        'team_lead' => 'Team Lead',
        'support_agent' => 'Support Agent',
    ],
];
