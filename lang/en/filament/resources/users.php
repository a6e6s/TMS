<?php

return [
    'model' => 'User',
    'plural' => 'Staff',
    'navigation_label' => 'Staff',

    'sections' => [
        'account' => 'Account',
        'access' => 'Access',
    ],

    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'role' => 'Role',
        'reports_to' => 'Reports to',
    ],

    'password_help' => 'Leave blank to keep the current password. Minimum 8 characters.',
    'no_manager' => 'No manager',

    'messages' => [
        'cannot_report_to_self' => 'A staff member cannot report to themselves.',
    ],
];
