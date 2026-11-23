<?php

return [
    'model' => 'Ticket',
    'plural' => 'Tickets',
    'navigation_label' => 'Tickets',

    'tabs' => [
        'all' => 'All',
        'unassigned' => 'Unassigned',
        'open' => 'Open',
        'in_progress' => 'In progress',
        'resolved' => 'Resolved',
    ],

    'sections' => [
        'details' => 'Ticket details',
        'reporter' => 'Reporter',
        'triage' => 'Triage',
        'timeline' => 'Timeline',
        'pms' => 'PMS',
        'browser' => 'Submitter browser',
        'screenshots' => 'Screenshots (:count)',
        'ai' => 'AI triage',
    ],

    'fields' => [
        'category' => 'Category',
        'ai_priority' => 'AI suggested priority',
        'ai_confidence' => 'AI confidence',
        'ai_summary' => 'AI summary',
        'ai_model' => 'AI model',
        'ai_classified_at' => 'Classified at',
        'user_agent' => 'Browser',
        'viewport' => 'Viewport',
        'console_errors' => 'Console errors',
        'pms_sync_status' => 'PMS sync',
        'pms_last_error' => 'Last PMS error',
        'ticket_number' => 'Ticket number',
        'title' => 'Title',
        'description' => 'Description',
        'page_url' => 'Page URL',
        'locale' => 'Ticket language',
        'attachments' => 'Attachments',
        'reporter_name' => 'Reporter name',
        'reporter_email' => 'Reporter email',
        'status' => 'Status',
        'priority' => 'Priority',
        'assignee' => 'Assignee',
        'created_at' => 'Created',
        'updated_at' => 'Last updated',
    ],

    'unassigned' => 'Unassigned',
    'no_console_errors' => 'No console errors were captured.',
    'ai' => [
        'apply_priority' => 'Apply AI priority',
        'priority_applied' => 'AI suggested priority applied.',
        'rerun' => 'Re-run AI triage',
        'queued' => 'AI triage queued.',
        'unclassified' => 'Not classified',
    ],
    'no_screenshots' => 'No screenshots were attached to this ticket.',
    'screenshot_label' => 'Screenshot :number',
    'open_full_size' => 'Open full size',

    'messages' => [
        'invalid_transition' => 'This status change is not allowed from the current status.',
        'team_lead_required_to_close' => 'Only a Team Lead or Super Admin can close a ticket that is not resolved.',
    ],

    'comments' => [
        'title' => 'Comments',
        'internal_help' => 'Internal notes are visible only to staff and are never shown to the reporter.',
        'authors' => [
            'reporter' => 'Reporter',
            'system' => 'System',
        ],
        'fields' => [
            'body' => 'Message',
            'is_internal' => 'Internal note',
            'visibility' => 'Visibility',
            'author' => 'Author',
        ],
    ],

    'pms' => [
        'push_action' => 'Push to PMS',
        'queued' => 'Push to PMS queued.',
        'completed' => 'Engineering marked the task as completed.',
        'cancelled' => 'Engineering closed this request as cancelled.',
    ],

    'activities' => [
        'title' => 'Activity',
        'system' => 'System',
        'change' => ':from → :to',
        'fields' => [
            'event' => 'Event',
            'change' => 'Change',
            'actor' => 'By',
        ],
        'events' => [
            'ticket_created' => 'Ticket created',
            'status_changed' => 'Status changed',
            'priority_changed' => 'Priority changed',
            'ai_priority_applied' => 'AI raised priority',
            'assignee_changed' => 'Assignee changed',
        ],
    ],
];
