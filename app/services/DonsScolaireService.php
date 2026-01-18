<?php

namespace App\Services;

use App\Models\DonsScolaire;

class DonsScolaireService
{
    public function paginate(int $perPage = 15)
    {
        return DonsScolaire::query()->latest()->paginate($perPage);
    }

    public function create(array $data): DonsScolaire
    {
        return DonsScolaire::create($data);
    }

    public function findOrFail(string $id): DonsScolaire
    {
        return DonsScolaire::findOrFail($id);
    }

    public function update(string $id, array $data): DonsScolaire
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
