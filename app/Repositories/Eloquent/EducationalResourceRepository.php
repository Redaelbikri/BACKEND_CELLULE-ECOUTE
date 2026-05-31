<?php

namespace App\Repositories\Eloquent;

use App\Models\EducationalResource;
use App\Repositories\Interfaces\EducationalResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EducationalResourceRepository implements EducationalResourceRepositoryInterface
{
    public function getAll(?string $search = null, ?string $category = null): Collection
    {
        return $this->baseQuery($search, $category)
            ->with('creator')
            ->latest()
            ->get();
    }

    public function getPublished(?string $search = null, ?string $category = null): Collection
    {
        return $this->baseQuery($search, $category)
            ->where('is_published', true)
            ->latest()
            ->get();
    }

    public function findById(int $id): ?EducationalResource
    {
        return EducationalResource::query()
            ->with('creator')
            ->find($id);
    }

    public function create(array $data): EducationalResource
    {
        return EducationalResource::query()->create($data)->load('creator');
    }

    public function update(EducationalResource $resource, array $data): EducationalResource
    {
        $resource->update($data);

        return $resource->refresh()->load('creator');
    }

    public function delete(EducationalResource $resource): void
    {
        $resource->delete();
    }

    private function baseQuery(?string $search, ?string $category)
    {
        return EducationalResource::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('content', 'like', '%'.$search.'%');
                });
            })
            ->when($category, fn ($query) => $query->where('category', $category));
    }
}
