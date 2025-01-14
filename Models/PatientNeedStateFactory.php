<?php
require_once 'PatientNeedWaitingState.php';
require_once 'PatientNeedAcceptedState.php';
require_once 'PatientNeedDoneState.php';

class PatientNeedStateFactory {
    /**
     * Create the appropriate state object based on the given status.
     *
     * @param Status $status The status to map to a state object.
     * @return IPatientNeedState The corresponding state object.
     */
    public static function create(Status $status): IPatientNeedState {
        return match ($status) {
            Status::Waiting => new PatientNeedWaitingState(),
            Status::Accepted => new PatientNeedAcceptedState(),
            Status::Done => new PatientNeedDoneState(),
        };
    }
}
