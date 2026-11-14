<?php

return [
    'model' => 'Ticket',
    'plural' => 'Tickets',
    'navigation_label' => 'Tickets',

    'sections' => [
        'details' => 'Ticket details',
        'reporter' => 'Reporter',
        'triage' => 'Triage',
        'timeline' => 'Timeline',
    ],

    'fields' => [
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
        'fields' => [
            'body' => 'Message',
            'is_internal' => 'Internal note',
            'visibility' => 'Visibility',
            'author' => 'Author',
        ],
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
            'assignee_changed' => 'Assignee changed',
        ],
    ],
];
