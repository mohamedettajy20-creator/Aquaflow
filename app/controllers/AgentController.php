<?php
class AgentController extends Controller
{
    public function dashboard(): void
    {
        $agentModel = new AgentModel();
        $agent = $agentModel->findByUserId(Auth::id());
        $assigned = $agent ? $agentModel->assignedCustomers($agent['id']) : [];

        $this->view('agent/dashboard', [
            'agent' => $agent,
            'assigned' => $assigned,
            'pageTitle' => 'Agent Dashboard',
        ]);
    }
}
