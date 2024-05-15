<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Create default permissions when initializing the application.
     *
     * @return string Indicates that the default permissions have been created.
    */
    public function index()
    {
        Permission::create(['name' => 'create landlord']);
        Permission::create(['name' => 'edit landlord']);
        Permission::create(['name' => 'delete landlord']);

        Permission::create(['name' => 'create property']);
        Permission::create(['name' => 'edit property']);
        Permission::create(['name' => 'delete property']);

        Permission::create(['name' => 'create tenancy']);
        Permission::create(['name' => 'edit tenancy']);
        Permission::create(['name' => 'delete tenancy']);

        Permission::create(['name' => 'edit applicant']);
        Permission::create(['name' => 'delete applicant']);

        Permission::create(['name' => 'review tenancy']);
        Permission::create(['name' => 'tenancy negotiator']);

        Permission::create(['name' => 'manually tenancy status']);
        Permission::create(['name' => 'manually property status']);

        Permission::create(['name' => 'configuration edit']);
        Permission::create(['name' => 'access customisation']);

        Permission::create(['name' => 'access configuration']);
        Permission::create(['name' => 'create edit staff user']);

        return "done";
    }
}
