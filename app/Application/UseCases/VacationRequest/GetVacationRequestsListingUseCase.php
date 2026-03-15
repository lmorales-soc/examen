<?php

namespace App\Application\UseCases\VacationRequest;

use App\Domain\Repositories\VacationRequestRepositoryInterface;

final class GetVacationRequestsListingUseCase
{
    public function __construct(
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
    ) {
    }

    /** @return array<int, array> */
    public function execute(?int $employeeId = null, ?int $areaId = null): array
    {
        return $this->vacationRequestRepository->getForListing($employeeId, $areaId);
    }
}
