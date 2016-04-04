<?php
namespace myagsource\Permissions;

/**
 * Pemissions
 *
 * Permission management
 *
 * User: ctranel
 * Date: 3/31/2016
 */

interface iPermissions {
    public function permissionsList();
    public function hasPermission($task_name);
}