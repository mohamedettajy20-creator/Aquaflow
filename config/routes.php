<?php
/** @var Router $router */
$router = new Router();

// ----------------------------------------------------------------------- Auth
$router->get('/', [AuthController::class, 'showLogin'], [Middleware::guest()]);
$router->get('/login', [AuthController::class, 'showLogin'], [Middleware::guest()]);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// ----------------------------------------------------------------------- Admin
$adminMw = [Middleware::role('admin')];
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], $adminMw);

$router->get('/admin/customers', [AdminController::class, 'customers'], $adminMw);
$router->post('/admin/customers', [AdminController::class, 'storeCustomer'], $adminMw);
$router->post('/admin/customers/{id}/update', [AdminController::class, 'updateCustomer'], $adminMw);
$router->post('/admin/customers/{id}/delete', [AdminController::class, 'deleteCustomer'], $adminMw);
$router->post('/admin/customers/{id}/toggle', [AdminController::class, 'toggleCustomerStatus'], $adminMw);

$router->get('/admin/meters', [AdminController::class, 'meters'], $adminMw);
$router->post('/admin/meters', [AdminController::class, 'storeMeter'], $adminMw);
$router->post('/admin/meters/{id}/update', [AdminController::class, 'updateMeter'], $adminMw);
$router->post('/admin/meters/{id}/delete', [AdminController::class, 'deleteMeter'], $adminMw);

$router->get('/admin/agents', [AdminController::class, 'agents'], $adminMw);
$router->post('/admin/agents', [AdminController::class, 'storeAgent'], $adminMw);

$router->get('/admin/invoices', [AdminController::class, 'invoices'], $adminMw);
$router->get('/admin/payments', [AdminController::class, 'paymentsPage'], $adminMw);
$router->post('/admin/payments', [AdminController::class, 'recordPayment'], $adminMw);

$router->get('/admin/reports', [AdminController::class, 'reports'], $adminMw);
$router->get('/admin/users', [AdminController::class, 'users'], $adminMw);
$router->get('/admin/settings', [AdminController::class, 'settings'], $adminMw);
$router->post('/admin/settings', [AdminController::class, 'updateSettings'], $adminMw);
$router->get('/admin/notifications', [AdminController::class, 'notificationsFeed'], $adminMw);

// ----------------------------------------------------------------------- Agent (Phase 2)
$agentMw = [Middleware::role('agent')];
$router->get('/agent/dashboard', [AgentController::class, 'dashboard'], $agentMw);

// ----------------------------------------------------------------------- Customer (Phase 4)
$customerMw = [Middleware::role('customer')];
$router->get('/customer/dashboard', [CustomerController::class, 'dashboard'], $customerMw);

return $router;
