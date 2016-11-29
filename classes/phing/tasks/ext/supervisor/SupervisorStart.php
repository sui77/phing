<?php

require_once "phing/tasks/ext/supervisor/SupervisorAction.php";

class SupervisorStart extends SupervisorAction {

    protected $action = 'start';

    public function execute()
    {
        $x = $this->callRpc('start', $this->getName());
        //print_r($x);
    }
}