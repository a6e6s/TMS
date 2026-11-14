<?php

return [
    'model' => 'Source app',
    'plural' => 'Source apps',
    'navigation_label' => 'Source apps',

    'sections' => [
        'details' => 'Source app',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'pms_project_id' => 'PMS project',
        'is_active' => 'Active',
        'tickets' => 'Tickets',
    ],

    'help' => [
        'pms_project_id' => 'The PMS project that tasks from this app are created in.',
    ],

    'actions' => [
        'issue_token' => 'Issue token',
        'revoke_tokens' => 'Revoke tokens',
    ],

    'messages' => [
        'token_once' => 'The token is shown once. Copy it now; it cannot be displayed again.',
        'token_created' => 'API token created. Copy it now.',
        'tokens_revoked' => 'All tokens for this app were revoked.',
    ],
];
