<?php

namespace App\Repositories\Interfaces;

use App\Models\EducationalResource;
use Illuminate\Database\Eloquent\Collection;

interface EducationalResourceRepositoryInterface
{
    public function getAll(?string $search = null, ?string $category = null): Collection;

    public function getPublished(?string $search = null, ?string $category = null): Collection;

    public function findById(int $id): ?EducationalResource;

    public function create(array $data): EducationalResource;

    public function update(EducationalResource $resource, array $data): EducationalResource;

    public function delete(EducationalResource $resource): void;
}
