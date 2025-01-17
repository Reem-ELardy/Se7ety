<?php
require_once 'ICommand.php';

class DonationInvoker {
    private $command;

    public function setCommand(ICommand $command) {
        $this->command = $command;
    }

    public function executeUndoCommand(){
        $this->command->undo();
    }

    public function executeRedoCommand() {
        return $this->command->redo();
    }
}
?>