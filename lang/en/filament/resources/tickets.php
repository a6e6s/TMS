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
    ],

    'fields' => [
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
            'assignee_changed' => 'Assignee changed',
        ],
    ],
];
