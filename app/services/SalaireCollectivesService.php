<?php

namespace App\Services;

use App\Models\SalaireCollectives;

class SalaireCollectivesService
{
    public function paginate(int $perPage = 15)
    {
        return SalaireCollectives::query()->latest()->paginate($perPage);
    }

    public function create(array $data): SalaireCollectives
    {
        return SalaireCollectives::create($data);
    }

    public function findOrFail(string $id): SalaireCollectives
    {
        return SalaireCollectives::findOrFail($id);
    }

    public function update(string $id, array $data): SalaireCollectives
    {
        $doc = $this->findOrFail($id);
        $doc->update($data);

        return $doc->refresh();
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }
}
