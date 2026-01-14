<?php
/**
 * File: Module Configuration
 * Description: Branch Dashboard module configuration
 */

return [
    'name' => 'Inventra Branch Dashboard',
    'version' => '1.0.0',
    'routes' => [
        'GET' => [
            'inventra_branch_dashboard' => 'Inventra\Controllers\BranchController@index',
            'inventra_admin_dashboard' => 'Inventra\Controllers\AdminController@index',
        ],
        'POST' => [
            'inventra_branch_action' => 'Inventra\Controllers\BranchController@handleAjax',
            'inventra_admin_action' => 'Inventra\Controllers\AdminController@handleAjax',
        ]
    ],
    'dependencies' => [
        'core\Database',
        'core\Security',
        'core\SessionManager'
    ],
    'assets' => [
        'css' => [
            'branch' => plugins_url('inventra/assets/css/branch.css'),
        ],
        'js' => [
            'branch' => plugins_url('inventra/assets/js/branch.js'),
        ]
    ]
];
