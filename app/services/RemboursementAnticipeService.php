<?php

namespace App\Services;

use App\Models\RemboursementAnticipe;

class RemboursementAnticipeService
{
    public function paginate(int $perPage = 15)
    {
        return RemboursementAnticipe::query()->latest()->paginate($perPage);
    }

    public function create(array $data): RemboursementAnticipe
    {
        return RemboursementAnticipe::create($data);
    }

    public function findOrFail(string $id): RemboursementAnticipe
    {
        return RemboursementAnticipe::findOrFail($id);
    }

    public function update(string $id, array $data): RemboursementAnticipe
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
