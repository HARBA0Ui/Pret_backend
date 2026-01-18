<?php

namespace App\Services;

use App\Models\AideSociale;

class AideSocialeService
{
    public function paginate(int $perPage = 15)
    {
        return AideSociale::query()->latest()->paginate($perPage);
    }

    public function create(array $data): AideSociale
    {
        return AideSociale::create($data);
    }

    public function findOrFail(string $id): AideSociale
    {
        return AideSociale::findOrFail($id);
    }

    public function update(string $id, array $data): AideSociale
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
