<?php

namespace Modules\LaravelUser\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Module\Entities\Module;
use Modules\LaravelUser\Entities\Permission;
use Modules\LaravelUser\Entities\RolePermission;
use Modules\LaravelUser\Entities\RoleUser;

class Role extends Model
{

    protected $casts = [
        "is_editable" => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, "creator_id", "id")
            ->withDefault([
                "name" => "(System)",
            ]);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function roleUsers()
    {
        return $this->hasMany(RoleUser::class, 'role_id', 'id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'id')
            ->withDefault(['name' => "(Global Role)"]);
    }

    public function hasPermission($slug)
    {
        foreach ($this->permissions as $perm) {
            if ($perm->slug == $slug) {
                return true;
            }
        }

        return false;
    }

    public function givePermission(Permission $permission)
    {
        $rp = new RolePermission();
        $rp->permission_id = $permission->id;
        $rp->role_id = $this->id;
        $rp->client_id = $this->client_id;
        $rp->creator_id = auth()->id();

        return $rp->save();
    }

    public function removePermission(Permission $permission)
    {
        return $this->permissions()->detach($permission);
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['user_id']) && is_numeric($filters['user_id'])) {
            $query->whereHas('roleUsers', function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id']);
            })
                ->get();
        }

        if (isset($filters['module_code']) && "" != trim($filters['module_code'])) {
            $query->whereHas('module', function ($q) use ($filters) {
                $q->where('code', $filters['module_code']);
            })
                ->orDoesntHave('module')
                ->get();
        }

        if (isset($filters['client_id']) && "" != trim($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!isset($filters['client_id'])) {
            $query->where(function ($query) {
                $query->where('client_id', isset($filters['client_id']) ? $filters['client_id'] : auth()->user()->client_id)
                    ->orWhere('is_global', true);
            });
        }
    }
}
