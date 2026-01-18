<?php

namespace App\Services;

use App\Models\Pret;

class PretService
{
    public function paginate(int $perPage = 15)
    {
        return Pret::query()->latest()->paginate($perPage);
    }

    public function create(array $data): Pret
    {
        return Pret::create($data);
    }

    public function findOrFail(string $id): Pret
    {
        return Pret::findOrFail($id);
    }

    public function update(string $id, array $data): Pret
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
