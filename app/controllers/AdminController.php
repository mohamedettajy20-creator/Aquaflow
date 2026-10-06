<?php
class AdminController extends Controller
{
    private CustomerModel $customers;
    private AgentModel $agents;
    private MeterModel $meters;
    private UserModel $users;
    private InvoiceModel $invoices;
    private PaymentModel $payments;
    private NotificationModel $notifications;
    private SettingModel $settings;

    public function __construct()
    {
        $this->customers     = new CustomerModel();
        $this->agents        = new AgentModel();
        $this->meters        = new MeterModel();
        $this->users         = new UserModel();
        $this->invoices      = new InvoiceModel();
        $this->payments      = new PaymentModel();
        $this->notifications = new NotificationModel();
        $this->settings      = new SettingModel();
    }

    // ------------------------------------------------------------------ DASHBOARD
    public function dashboard(): void
    {
        $stats = [
            'total_customers'   => $this->customers->count(),
            'total_meters'      => $this->meters->count(),
            'total_agents'      => $this->agents->count(),
            'paid_invoices'     => $this->invoices->count(['status' => 'paid']),
            'unpaid_invoices'   => $this->invoices->count(['status' => 'unpaid']),
            'late_invoices'     => $this->invoices->count(['status' => 'late']),
        ];

        $revenueRow = $this->invoices->queryOne("SELECT SUM(total_amount) AS total FROM invoices WHERE MONTH(issue_date)=MONTH(CURDATE()) AND YEAR(issue_date)=YEAR(CURDATE())");
        $stats['monthly_revenue'] = (float) ($revenueRow['total'] ?? 0);

        $consumptionRow = $this->invoices->queryOne("SELECT SUM(consumption) AS total FROM invoices WHERE MONTH(issue_date)=MONTH(CURDATE()) AND YEAR(issue_date)=YEAR(CURDATE())");
        $stats['monthly_consumption'] = (float) ($consumptionRow['total'] ?? 0);

        $revenueTrend = array_reverse($this->invoices->query(
            "SELECT DATE_FORMAT(issue_date, '%Y-%m') AS ym, SUM(total_amount) AS revenue
             FROM invoices GROUP BY ym ORDER BY ym DESC LIMIT 6"
        ));

        $statusBreakdown = $this->invoices->query(
            "SELECT status, COUNT(*) AS c FROM invoices GROUP BY status"
        );

        $logger = new ActivityLogger();
        $recentActivity = $logger->recent(8);

        $this->view('admin/dashboard', [
            'stats' => $stats,
            'revenueTrend' => $revenueTrend,
            'statusBreakdown' => $statusBreakdown,
            'recentActivity' => $recentActivity,
            'pageTitle' => 'Dashboard',
            'active' => 'dashboard',
        ]);
    }

    // ------------------------------------------------------------------ CUSTOMERS
    public function customers(): void
    {
        $search = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $rows = $this->customers->listWithDetails($search, $perPage, $offset);
        $total = $this->customers->countSearch($search);
        $agentList = $this->agents->listWithDetails();

        $this->view('admin/customers', [
            'customers' => $rows,
            'search' => $search,
            'page' => $page,
            'totalPages' => (int) ceil($total / $perPage),
            'agentList' => $agentList,
            'pageTitle' => 'Customers',
            'active' => 'customers',
        ]);
    }

    public function storeCustomer(): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $v = new Validator($input);
        $v->required('full_name')->required('email')->email('email')
          ->required('address')->required('password')->min('password', 6);

        if ($v->fails()) {
            $this->json(['success' => false, 'message' => $v->firstError()], 422);
        }

        if ($this->users->first(['email' => $input['email']])) {
            $this->json(['success' => false, 'message' => 'This email is already registered.'], 422);
        }

        $userId = $this->users->create([
            'full_name'     => trim($input['full_name']),
            'email'         => strtolower(trim($input['email'])),
            'password_hash' => Auth::hash($input['password']),
            'role'          => 'customer',
            'phone'         => $input['phone'] ?? null,
            'status'        => 'active',
        ]);

        $customerId = $this->customers->create([
            'user_id'       => $userId,
            'customer_code' => $this->customers->nextCustomerCode(),
            'address'       => trim($input['address']),
            'city'          => $input['city'] ?? null,
            'postal_code'   => $input['postal_code'] ?? null,
            'customer_type' => $input['customer_type'] ?? 'residential',
            'agent_id'      => !empty($input['agent_id']) ? $input['agent_id'] : null,
            'registered_at' => date('Y-m-d'),
        ]);

        ActivityLogger::log('customer_created', "Created customer #{$customerId}");
        $this->json(['success' => true, 'message' => 'Customer created successfully.', 'id' => $customerId]);
    }

    public function updateCustomer(int $id): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $customer = $this->customers->find($id);
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $v = new Validator($input);
        $v->required('full_name')->required('email')->email('email')->required('address');
        if ($v->fails()) {
            $this->json(['success' => false, 'message' => $v->firstError()], 422);
        }

        $this->users->update($customer['user_id'], [
            'full_name' => trim($input['full_name']),
            'email'     => strtolower(trim($input['email'])),
            'phone'     => $input['phone'] ?? null,
        ]);

        $this->customers->update($id, [
            'address'       => trim($input['address']),
            'city'          => $input['city'] ?? null,
            'postal_code'   => $input['postal_code'] ?? null,
            'customer_type' => $input['customer_type'] ?? 'residential',
            'agent_id'      => !empty($input['agent_id']) ? $input['agent_id'] : null,
        ]);

        ActivityLogger::log('customer_updated', "Updated customer #{$id}");
        $this->json(['success' => true, 'message' => 'Customer updated successfully.']);
    }

    public function deleteCustomer(int $id): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $customer = $this->customers->find($id);
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $this->users->delete($customer['user_id']); // cascades to customer row
        ActivityLogger::log('customer_deleted', "Deleted customer #{$id}");
        $this->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }

    public function toggleCustomerStatus(int $id): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $customer = $this->customers->find($id);
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }
        $user = $this->users->find($customer['user_id']);
        $newStatus = $user['status'] === 'active' ? 'suspended' : 'active';
        $this->users->update($user['id'], ['status' => $newStatus]);

        $this->json(['success' => true, 'status' => $newStatus]);
    }

    // ------------------------------------------------------------------ METERS
    public function meters(): void
    {
        $search = trim($_GET['q'] ?? '');
        $rows = $this->meters->listWithDetails($search);
        $customerList = $this->customers->listWithDetails('', 500, 0);

        $this->view('admin/meters', [
            'meters' => $rows,
            'search' => $search,
            'customerList' => $customerList,
            'pageTitle' => 'Meters',
            'active' => 'meters',
        ]);
    }

    public function storeMeter(): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $v = new Validator($input);
        $v->required('customer_id')->required('installation_date');
        if ($v->fails()) {
            $this->json(['success' => false, 'message' => $v->firstError()], 422);
        }

        $id = $this->meters->create([
            'meter_number'      => $this->meters->nextMeterNumber(),
            'customer_id'       => (int) $input['customer_id'],
            'installation_date' => $input['installation_date'],
            'status'            => $input['status'] ?? 'active',
            'initial_reading'   => $input['initial_reading'] ?? 0,
            'location_note'     => $input['location_note'] ?? null,
        ]);

        ActivityLogger::log('meter_created', "Created meter #{$id}");
        $this->json(['success' => true, 'message' => 'Meter added successfully.', 'id' => $id]);
    }

    public function updateMeter(int $id): void
    {
        $input = $this->input();
        Csrf::guard($input);

        if (!$this->meters->find($id)) {
            $this->json(['success' => false, 'message' => 'Meter not found.'], 404);
        }

        $this->meters->update($id, [
            'status'        => $input['status'] ?? 'active',
            'location_note' => $input['location_note'] ?? null,
        ]);

        ActivityLogger::log('meter_updated', "Updated meter #{$id}");
        $this->json(['success' => true, 'message' => 'Meter updated successfully.']);
    }

    public function deleteMeter(int $id): void
    {
        $input = $this->input();
        Csrf::guard($input);

        if (!$this->meters->find($id)) {
            $this->json(['success' => false, 'message' => 'Meter not found.'], 404);
        }
        $this->meters->delete($id);
        ActivityLogger::log('meter_deleted', "Deleted meter #{$id}");
        $this->json(['success' => true, 'message' => 'Meter deleted successfully.']);
    }

    // ------------------------------------------------------------------ AGENTS
    public function agents(): void
    {
        $rows = $this->agents->listWithDetails();
        $this->view('admin/agents', ['agents' => $rows, 'pageTitle' => 'Agents', 'active' => 'agents']);
    }

    public function storeAgent(): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $v = new Validator($input);
        $v->required('full_name')->required('email')->email('email')->required('password')->min('password', 6);
        if ($v->fails()) {
            $this->json(['success' => false, 'message' => $v->firstError()], 422);
        }
        if ($this->users->first(['email' => $input['email']])) {
            $this->json(['success' => false, 'message' => 'This email is already registered.'], 422);
        }

        $userId = $this->users->create([
            'full_name'     => trim($input['full_name']),
            'email'         => strtolower(trim($input['email'])),
            'password_hash' => Auth::hash($input['password']),
            'role'          => 'agent',
            'phone'         => $input['phone'] ?? null,
            'status'        => 'active',
        ]);

        $id = $this->agents->create([
            'user_id'       => $userId,
            'employee_code' => $this->agents->nextEmployeeCode(),
            'zone'          => $input['zone'] ?? null,
            'hire_date'     => date('Y-m-d'),
        ]);

        ActivityLogger::log('agent_created', "Created agent #{$id}");
        $this->json(['success' => true, 'message' => 'Agent added successfully.', 'id' => $id]);
    }

    // ------------------------------------------------------------------ INVOICES / PAYMENTS
    public function invoices(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['q'] ?? null,
        ];
        $rows = $this->invoices->listWithDetails($filters);
        $this->view('admin/invoices', ['invoices' => $rows, 'filters' => $filters, 'pageTitle' => 'Invoices', 'active' => 'invoices']);
    }

    public function paymentsPage(): void
    {
        $rows = $this->payments->listWithDetails();
        $this->view('admin/payments', ['payments' => $rows, 'pageTitle' => 'Payments', 'active' => 'payments']);
    }

    public function recordPayment(): void
    {
        $input = $this->input();
        Csrf::guard($input);

        $invoice = $this->invoices->find((int) ($input['invoice_id'] ?? 0));
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $this->payments->create([
            'invoice_id'  => $invoice['id'],
            'customer_id' => $invoice['customer_id'],
            'amount'      => $input['amount'] ?? $invoice['total_amount'],
            'method'      => $input['method'] ?? 'cash',
            'reference'   => $input['reference'] ?? null,
            'recorded_by' => Auth::id(),
            'status'      => 'completed',
        ]);

        $this->invoices->update($invoice['id'], ['status' => 'paid']);
        ActivityLogger::log('payment_recorded', "Recorded payment for invoice #{$invoice['id']}");

        $this->json(['success' => true, 'message' => 'Payment recorded and invoice marked as paid.']);
    }

    // ------------------------------------------------------------------ REPORTS
    public function reports(): void
    {
        $topConsumers = $this->invoices->topConsumers(10);
        $unpaid = $this->invoices->listWithDetails(['status' => 'unpaid']);
        $late = $this->invoices->listWithDetails(['status' => 'late']);
        $revenueTrend = array_reverse($this->invoices->query(
            "SELECT DATE_FORMAT(issue_date, '%Y-%m') AS ym, SUM(total_amount) AS revenue FROM invoices GROUP BY ym ORDER BY ym DESC LIMIT 12"
        ));

        $this->view('admin/reports', [
            'topConsumers' => $topConsumers,
            'unpaid' => $unpaid,
            'late' => $late,
            'revenueTrend' => $revenueTrend,
            'pageTitle' => 'Reports & Statistics',
            'active' => 'reports',
        ]);
    }

    // ------------------------------------------------------------------ USERS & SETTINGS
    public function users(): void
    {
        $rows = $this->users->all('role, full_name');
        $this->view('admin/users', ['users' => $rows, 'pageTitle' => 'System Users', 'active' => 'users']);
    }

    public function settings(): void
    {
        $rows = $this->settings->all();
        $this->view('admin/settings', ['settings' => $rows, 'pageTitle' => 'System Settings', 'active' => 'settings']);
    }

    public function updateSettings(): void
    {
        $input = $this->input();
        Csrf::guard($input);

        foreach ($input as $key => $value) {
            if ($key === 'csrf_token') continue;
            $this->settings->set($key, is_array($value) ? json_encode($value) : $value);
        }

        ActivityLogger::log('settings_updated', 'Updated system settings');
        $this->json(['success' => true, 'message' => 'Settings updated successfully.']);
    }

    public function notificationsFeed(): void
    {
        $rows = $this->notifications->forUser(Auth::id(), 10);
        $this->notifications->markAllRead(Auth::id());
        $this->json(['success' => true, 'notifications' => $rows]);
    }
}
