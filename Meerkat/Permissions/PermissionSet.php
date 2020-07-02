<?php

namespace Statamic\Addons\Meerkat\Permissions;

class PermissionSet
{

    public $canViewComments = true;
    public $canApproveComments = true;
    public $canUnApproveComments = true;
    public $canReplyToComments = true;
    public $canEditComments = true;
    public $canReportAsSpam = true;
    public $canReportAsHam = true;
    public $canRemoveComments = true;

    public function toArray()
    {
        return [
            AccessManager::PERMISSION_CAN_VIEW => $this->canViewComments,
            AccessManager::PERMISSION_CAN_APPROVE => $this->canApproveComments,
            AccessManager::PERMISSION_CAN_UNAPPROVE => $this->canUnApproveComments,
            AccessManager::PERMISSION_CAN_REPLY => $this->canReplyToComments,
            AccessManager::PERMISSION_CAN_EDIT => $this->canEditComments,
            AccessManager::PERMISSION_CAN_REPORT_SPAM => $this->canReportAsSpam,
            AccessManager::PERMISSION_CAN_REPORT_HAM => $this->canReportAsHam,
            AccessManager::PERMISSION_CAN_REMOVE => $this->canRemoveComments
        ];
    }

}