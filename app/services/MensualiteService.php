<?php

namespace App\Services;

use App\Models\Mensualite;

class MensualiteService
{
    public function paginate(int $perPage = 15)
    {
        return Mensualite::query()->latest()->paginate($perPage);
    }

    public function create(array $data): Mensualite
    {
        return Mensualite::create($data);
    }

    public function findOrFail(string $id): Mensualite
    {
        return Mensualite::findOrFail($id);
    }

    public function update(string $id, array $data): Mensualite
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
