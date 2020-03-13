<?php

namespace Modules\LaravelUser\Entities;

use Modules\LaravelUser\Entities\Permission;
use App\Models\TechlifyModel;
use App\User;

class RolePermission extends TechlifyModel
{

    protected $table = "permission_role";
}
