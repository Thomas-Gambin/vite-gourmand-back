<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Controller\Api\Employee\UpdateEmployeeOrderStatusController;
use App\Dto\Commande\UpdateCommandeStatutPayload;

#[ApiResource(
    shortName: 'Employe',
    operations: [
        new Patch(
            uriTemplate: '/employee/commandes/{id}/statut',
            name: 'api_employee_commandes_statut_doc',
            controller: UpdateEmployeeOrderStatusController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            input: UpdateCommandeStatutPayload::class,
            output: false,
        ),
    ],
)]
final class EmployeeResource
{
}
