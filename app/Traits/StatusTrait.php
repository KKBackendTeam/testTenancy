<?php

namespace App\Traits;

trait StatusTrait
{
    public $tenancyStatus = [
        '', 'Pending', 'Hold', 'Awaiting Review', 'Failed Review',
        'Awaiting TA Signing', 'Let', 'Rolling', 'In progress', 'Expired', 'Cancelled', 'Completed',
        'Stalled at Pending', 'Stalled at Hold', 'Stalled at Awaiting Review', 'Stalled at Failed Review',
        'Stalled at Awaiting Signing', 'Awaiting TA Sending', 'Awaiting TA Review'
    ];

    public $applicantStatus = [
        '', 'Awaiting Application Form', 'Awaiting Reference', 'References Returned', 'Failed Review', 'Awaiting Tenancy Agreement Signing',
        'Application Cancelled', 'Application Complete', 'Tenancy In Progress', 'Tenancy Expired',
        'Stalled at Awaiting Application Form', 'Stalled at Awaiting Reference',
        'Stalled at Awaiting Tenancy Agreement Signing'
    ];

    public $propertyStatus = [
        '', 'Available To Let (Unoccupied)', 'Check If Renewing/Section1', 'Available To Let (Occupied)',
        'Hold (Processing Application)', 'Let', 'Not Available To Let'
    ];

    public $activeDeactiveArray = ['Deactive', 'Active'], $sortingOrderArray = ['ASC', 'DESC'];
    public $authorizeUnauthorizeArray = ['Unauthorized', 'Authorized'];
    public $tenancyTypeStatus = ['', 'New', 'Renewal', 'Part Renewal'];
}
