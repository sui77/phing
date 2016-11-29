<?php

require_once "phing/tasks/ext/supervisor/SupervisorAction.php";

class SupervisorStop extends SupervisorAction {

    protected $action = 'stop';

    public function execute()
    {
        $this->callRpc('start', $this->getName());
    }
}