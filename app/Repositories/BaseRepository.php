<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected Model $model;

    public function paginate($limit = 20)
    {
        return $this->model
            ->latest()
            ->paginate($limit);
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->find($id);

        $model->update($data);

        return $model;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}