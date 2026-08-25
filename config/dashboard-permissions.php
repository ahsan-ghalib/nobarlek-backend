<?php

return [
    'admin_role' => 'admin',
    'admin_email' => env('DASHBOARD_ADMIN_EMAIL', 'admin@upliga.com'),

    'groups' => [
        [
            'name' => 'Users',
            'permissions' => [
                ['name' => 'users.view', 'label' => 'View users'],
                ['name' => 'users.create', 'label' => 'Create users'],
                ['name' => 'users.update', 'label' => 'Update users'],
                ['name' => 'users.delete', 'label' => 'Delete users'],
            ],
        ],
        [
            'name' => 'News',
            'permissions' => [
                ['name' => 'news.view', 'label' => 'View news'],
                ['name' => 'news.create', 'label' => 'Create news'],
                ['name' => 'news.update', 'label' => 'Edit news'],
                ['name' => 'news.delete', 'label' => 'Delete news'],
                ['name' => 'news.publish', 'label' => 'Publish / unpublish news'],
            ],
        ],
        [
            'name' => 'Footer',
            'permissions' => [
                ['name' => 'footer.settings', 'label' => 'Manage site / footer settings'],
                ['name' => 'footer.match-links.view', 'label' => 'View footer match links'],
                ['name' => 'footer.match-links.create', 'label' => 'Create footer match links'],
                ['name' => 'footer.match-links.update', 'label' => 'Edit footer match links'],
                ['name' => 'footer.match-links.delete', 'label' => 'Delete footer match links'],
            ],
        ],
        [
            'name' => 'SEO',
            'permissions' => [
                ['name' => 'seo.home', 'label' => 'Homepage SEO'],
                ['name' => 'seo.teams', 'label' => 'Teams SEO'],
                ['name' => 'seo.players', 'label' => 'Players SEO'],
                ['name' => 'seo.competitions', 'label' => 'Competitions SEO'],
                ['name' => 'seo.matches', 'label' => 'Matches SEO'],
            ],
        ],
    ],
];
