<?php

namespace Modules\LaraveUser\Entities;

use Modules\LaraveUser\Entities\Permission;
use App\Models\TechlifyModel;
use App\User;

class RolePermission extends TechlifyModel
{

    protected $table = "permission_role";
}
