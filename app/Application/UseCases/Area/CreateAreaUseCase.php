<?php

namespace App\Application\UseCases\Area;

use App\Domain\Repositories\AreaRepositoryInterface;
use Illuminate\Support\Str;

final class CreateAreaUseCase
{
    public function __construct(
        private readonly AreaRepositoryInterface $areaRepository,
    ) {
    }

    /**
     * @param array{name: string, slug?: string} $data
     * @return array
     */
    public function execute(array $data): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->areaRepository->create($data);
    }
}
