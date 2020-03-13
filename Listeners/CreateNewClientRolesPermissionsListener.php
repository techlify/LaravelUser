<?php

namespace Modules\LaravelUser\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\AddressBook\Traits\AddressBookSubscription;
use Modules\Administration\Traits\AdministrationSubscription;
use Modules\Module\Entities\Module;
use Modules\Module\Entities\ModulePackage;
use Modules\LaravelUser\Entities\Permission;
use Modules\LaravelUser\Entities\Role;

/**
 * When a new client is created,
 *
 * this listener is called to create a few roles
 * and permissions for this client
 */
class CreateNewClientRolesPermissionsListener
{
    use AddressBookSubscription;
    use AdministrationSubscription;
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $clientId = $event->client->id;
        $roles = [
            [
                'slug' => "$clientId-admin",
                'label' => 'Administrator',
                'description' => '',
                'is_editable' => false,
                'module_id' => null,
                'client_id' => $clientId,
            ],
            [
                'slug' => "$clientId-manager",
                'label' => 'Manager',
                'description' => '',
                'is_editable' => false,
                'module_id' => null,
                'client_id' => $clientId,
            ],
            [
                'slug' => "$clientId-supervisor",
                'label' => 'Supervisor',
                'description' => '',
                'is_editable' => false,
                'module_id' => null,
                'client_id' => $clientId,
            ],
            [
                'slug' => "$clientId-clerk",
                'label' => 'Clerk',
                'description' => '',
                'is_editable' => false,
                'module_id' => null,
                'client_id' => $clientId,
            ],
        ];
        DB::table((new Role())->getTable())
            ->insert($roles);

        /** Adding Address Book module to this client */
        $addressBookModule = Module::where('code', 'address-book')
            ->first();
        $addressBookModulePackage = ModulePackage::where('module_id', $addressBookModule->id)
            ->first();

        $this->doSubscribe($addressBookModule->id, $addressBookModulePackage->id, $clientId);

        /** Adding Administration module to this client */
        $administrationModule = Module::where('code', 'administration')
            ->first();
        $administrationModulePackage = ModulePackage::where('module_id', $administrationModule->id)
            ->first();

        $this->doAdministrationSubscribe($administrationModule->id, $administrationModulePackage->id, $clientId);
    }
}
