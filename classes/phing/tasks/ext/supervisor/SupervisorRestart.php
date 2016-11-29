<?php

require_once "phing/tasks/ext/supervisor/SupervisorAction.php";

class SupervisorRestart extends SupervisorAction {

protected $action = 'restart';

    public function execute()
    {
        $this->callRpc('start', $this->getName());
    }
}