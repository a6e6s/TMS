<?php

return [
    'navigation_label' => 'Settings',

    'sections' => [
        'tickets' => 'Ticket settings',
    ],

    'fields' => [
        'auto_close_days' => 'Auto-close resolved tickets after (days)',
        'retention_days' => 'Retention period (days)',
    ],

    'help' => [
        'auto_close_days' => 'Resolved tickets close automatically when they are not reopened within this many days.',
        'retention_days' => 'Stored intake payloads older than this are removed.',
    ],

    'actions' => [
        'save' => 'Save settings',
    ],

    'messages' => [
        'saved' => 'Settings saved.',
    ],
];
