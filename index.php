<?php
// index.php
// session_start(); // Remove this initial call

require_once 'config/config.php';
require_once 'core/SessionManager.php'; // Include SessionManager first
require_once 'core/Router.php';

// Start the session with security measures *before* any output or routing logic
SessionManager::startSecureSession(); // This handles session_start() and security settings

$router = new Router();

// Define routes using a simple action parameter
// Map GET requests
$router->addRoute('GET', 'login', 'AuthController@showLoginForm');
$router->addRoute('GET', 'dashboard', 'DashboardController@index');
$router->addRoute('GET', 'logout', 'AuthController@logout');
$router->addRoute('GET', 'admin_dashboard', 'AdminController@index');
$router->addRoute('GET', 'admin_dashboard_modern', 'AdminController@modernDashboard');
$router->addRoute('GET', 'admin_dashboard_stats', 'AdminController@dashboardStats');
$router->addRoute('POST', 'admin_action', 'AdminController@handleAjax');
// Admin sub-pages
$router->addRoute('GET', 'admin_allocations', 'AdminController@allocations');
$router->addRoute('GET', 'admin_adjustments', 'AdminController@adjustments');
$router->addRoute('GET', 'admin_disputes', 'AdminController@disputes');
$router->addRoute('GET', 'admin_transfers', 'AdminController@transfers');
$router->addRoute('GET', 'admin_inventory', 'AdminController@inventory');
$router->addRoute('GET', 'branch_dashboard', 'BranchController@index');
$router->addRoute('GET', 'branch_dashboard_modern', 'BranchController@modernDashboard');
$router->addRoute('GET', 'branch_allocations', 'BranchController@allocations');
$router->addRoute('GET', 'branch_adjustments', 'BranchController@adjustments');
$router->addRoute('GET', 'branch_transfers', 'BranchController@transfers');
$router->addRoute('GET', 'branch_transfers_out', 'BranchController@transfersOut');
$router->addRoute('GET', 'branch_dashboard_stats', 'BranchController@dashboardStats');
$router->addRoute('POST', 'branch_action', 'BranchController@handleAjax');
$router->addRoute('GET', 'settings', 'SettingsController@index');
$router->addRoute('POST', 'settings_ajax', 'SettingsController@handleAjax');

// Configuration (Roles & Permissions)
$router->addRoute('GET', 'configuration', 'ConfigurationController@index');
$router->addRoute('GET', 'manage_users', 'ConfigurationController@manageUsers');
$router->addRoute('POST', 'configuration_ajax', 'ConfigurationController@handleAjax');

// Add more GET routes as needed

// Map POST requests
$router->addRoute('POST', 'login', 'AuthController@login');
// Add more POST routes as needed

// Get the 'action' parameter from the query string
$action = $_GET['action'] ?? $_POST['action'] ?? null; // Remove 'default' fallback
$method = $_SERVER['REQUEST_METHOD'];

// If no action is specified, redirect to the login page
if ($action === null) {
    // Construct the login URL explicitly
    $loginUrl = BASE_URL . '/index.php?action=login';
    header("Location: $loginUrl");
    exit(); // Important: Stop execution after redirect
}

// Dispatch based on the action parameter
// Fallback: directly handle legacy or missing route for 'configuration' (GET)
if ($action === 'configuration' && $method === 'GET') {
    $confFile = __DIR__ . '/modules/Inventra/controllers/ConfigurationController.php';
    if (file_exists($confFile)) {
        require_once $confFile;
        if (class_exists('ConfigurationController')) {
            $c = new ConfigurationController();
            if (method_exists($c, 'index')) {
                $c->index();
                exit();
            }
        }
    }
}

$router->dispatch($action, $method);