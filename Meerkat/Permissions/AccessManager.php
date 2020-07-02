<?php

namespace Statamic\Addons\Meerkat\Permissions;

use Statamic\Extend\Extensible;

class AccessManager
{
    use Extensible;

    const PERMISSION_ALL = 'all_permissions';
    const PERMISSION_CAN_VIEW = 'can_view_comments';
    const PERMISSION_CAN_APPROVE = 'can_approve_comments';
    const PERMISSION_CAN_UNAPPROVE = 'can_unapprove_comments';
    const PERMISSION_CAN_REPLY = 'can_reply_to_comments';
    const PERMISSION_CAN_EDIT = 'can_edit_comments';
    const PERMISSION_CAN_REPORT_SPAM = 'can_report_as_spam';
    const PERMISSION_CAN_REPORT_HAM = 'can_report_as_ham';
    const PERMISSION_CAN_REMOVE = 'can_remove_comments';

    protected $canViewComments = true;
    protected $canApproveComments = true;
    protected $canUnApproveComments = true;
    protected $canReplyToComments = true;
    protected $canEditComments = true;
    protected $canReportAsSpam = true;
    protected $canReportAsHam = true;
    protected $canRemoveComments = true;
    protected $statamicUser = null;
    protected $userRoles = [];
    protected $userRoleInstances = null;
    protected $configuredPermissions = [];
    protected $totalRoleCount = 0;
    private $isGlobalAdmin = false;

    public function __construct()
    {
        $this->addon_name = 'Meerkat';
    }

    public function setUser($user)
    {
        $this->statamicUser = $user;

        if ($this->statamicUser != null) {
            $roles = $this->statamicUser->roles();

            if ($user->isSuper()) {
                $this->isGlobalAdmin = true;
            }

            if ($roles != null && $roles->count() > 0) {
                $this->userRoles = $roles->keys();
                $this->userRoleInstances = $roles;
            }
        }
    }

    /**
     * Sets the internal configured properties.
     *
     * @param $permissions array The configuration properties.
     */
    public function setPermissions($permissions)
    {
        $this->configuredPermissions = $permissions;

        $allPermissionKeys = [AccessManager::PERMISSION_ALL, AccessManager::PERMISSION_CAN_VIEW, AccessManager::PERMISSION_CAN_APPROVE,
            AccessManager::PERMISSION_CAN_UNAPPROVE, AccessManager::PERMISSION_CAN_REPLY, AccessManager::PERMISSION_CAN_EDIT,
            AccessManager::PERMISSION_CAN_REPORT_HAM, AccessManager::PERMISSION_CAN_REPORT_SPAM, AccessManager::PERMISSION_CAN_REMOVE];

        foreach ($allPermissionKeys as $permissionCategory) {
            if (array_key_exists($permissionCategory, $this->configuredPermissions) == false) {
                $this->configuredPermissions[$permissionCategory] = [];
            } else if ($this->configuredPermissions[$permissionCategory] === null) {
                $this->configuredPermissions[$permissionCategory] = [];
            }
        }

        // Get rid of the PERMISSION_ALL entry.
        array_shift($allPermissionKeys);

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_ALL])) {
            $this->configuredPermissions[AccessManager::PERMISSION_ALL] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_VIEW])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_VIEW] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_APPROVE])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_APPROVE] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_UNAPPROVE])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_UNAPPROVE] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REPLY])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPLY] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_EDIT])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_EDIT] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_SPAM])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_SPAM] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_HAM])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_HAM] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REMOVE])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REMOVE] = [];
        }

        if (count($this->configuredPermissions[AccessManager::PERMISSION_ALL]) > 0) {
            foreach ($this->configuredPermissions[AccessManager::PERMISSION_ALL] as $userRole) {
                foreach ($allPermissionKeys as $permissionCategory) {
                    if (in_array($userRole, $this->configuredPermissions[$permissionCategory]) == false) {
                        array_push($this->configuredPermissions[$permissionCategory], $userRole);
                    }
                }
            }
        }

        // Calculate a role count.
        foreach ($allPermissionKeys as $permissionCategory) {
            $this->totalRoleCount += count($this->configuredPermissions[$permissionCategory]);
        }
    }

    public function resolve()
    {
        if ($this->isGlobalAdmin) {
            // Global admins do not trigger permission resolving events.
            return;
        }

        if ($this->configuredPermissions == null || $this->totalRoleCount == 0) {
            $this->resolveFromEvents();
            return;
        }

        if ($this->isGlobalAdmin == false && $this->totalRoleCount > 0 && count($this->userRoles) == 0) {
            $this->canViewComments = false;
            $this->canApproveComments = false;
            $this->canUnApproveComments = false;
            $this->canReplyToComments = false;
            $this->canEditComments = false;
            $this->canReportAsSpam = false;
            $this->canReportAsHam = false;
            $this->canRemoveComments = false;

            $this->resolveFromEvents();
            return;
        }

        // At this point, revoke everything and add them back.
        $this->canViewComments = false;
        $this->canApproveComments = false;
        $this->canUnApproveComments = false;
        $this->canReplyToComments = false;
        $this->canEditComments = false;
        $this->canReportAsSpam = false;
        $this->canReportAsHam = false;
        $this->canRemoveComments = false;

        foreach ($this->userRoles as $userRole) {
            if ($this->canViewComments == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_VIEW])) {
                $this->canViewComments = true;
            }

            if ($this->canApproveComments == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_APPROVE])) {
                $this->canApproveComments = true;
            }

            if ($this->canUnApproveComments == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_UNAPPROVE])) {
                $this->canUnApproveComments = true;
            }

            if ($this->canReplyToComments == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPLY])) {
                $this->canReplyToComments = true;
            }

            if ($this->canEditComments == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_EDIT])) {
                $this->canEditComments = true;
            }

            if ($this->canReportAsSpam == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_SPAM])) {
                $this->canReportAsSpam = true;
            }

            if ($this->canReportAsHam == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_HAM])) {
                $this->canReportAsHam = true;
            }

            if ($this->canRemoveComments == false && in_array($userRole, $this->configuredPermissions[AccessManager::PERMISSION_CAN_REMOVE])) {
                $this->canRemoveComments = true;
            }
        }

        $this->resolveFromEvents();
    }

    private function resolveFromEvents()
    {
        $permissionSet = $this->toPermissionSet();

        foreach ($this->emitEvent('permissions.resolving', [$this->statamicUser, $permissionSet]) as $resolved) {
            if ($resolved !== null && $resolved instanceof PermissionSet) {
                $this->canViewComments = $resolved->canViewComments;
                $this->canApproveComments = $resolved->canApproveComments;
                $this->canUnApproveComments = $resolved->canUnApproveComments;
                $this->canReplyToComments = $resolved->canReplyToComments;
                $this->canEditComments = $resolved->canEditComments;
                $this->canReportAsSpam = $resolved->canReportAsSpam;
                $this->canReportAsHam = $resolved->canReportAsHam;
                $this->canRemoveComments = $resolved->canRemoveComments;
            }
        }
    }

    /**
     * Converts the access manager permissions to a PermissionSet object.
     *
     * @return PermissionSet
     */
    public function toPermissionSet()
    {
        $permissionSet = new PermissionSet();
        $permissionSet->canApproveComments = $this->canApproveComments();
        $permissionSet->canViewComments = $this->canViewComments();
        $permissionSet->canUnApproveComments = $this->canUnApproveComments();
        $permissionSet->canReplyToComments = $this->canReplyToComments();
        $permissionSet->canEditComments = $this->canEditComments();
        $permissionSet->canReportAsSpam = $this->canReportAsSpam();
        $permissionSet->canReportAsHam = $this->canReportAsHam();
        $permissionSet->canRemoveComments = $this->canRemoveComments();

        return $permissionSet;
    }

    public function canViewComments()
    {
        return $this->canViewComments;
    }

    public function canApproveComments()
    {
        return $this->canApproveComments;
    }

    public function canUnApproveComments()
    {
        return $this->canUnApproveComments;
    }

    public function canReplyToComments()
    {
        return $this->canReplyToComments;
    }

    public function canEditComments()
    {
        return $this->canEditComments;
    }

    public function canReportAsSpam()
    {
        return $this->canReportAsSpam;
    }

    public function canReportAsHam()
    {
        return $this->canReportAsHam;
    }

    public function canRemoveComments()
    {
        return $this->canRemoveComments;
    }

}