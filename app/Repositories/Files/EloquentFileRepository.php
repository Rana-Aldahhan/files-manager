<?php

namespace App\Repositories\Files;

use App\Interfaces\FileRepositoryInterface;
use App\Models\File;

class EloquentFileRepository implements FileRepositoryInterface
{

    public function all()
    {
        return File::all();
    }
    public function create(array $data)
    {
        return File::create($data);
    }
    public function update(array $data, $id)
    {
        return File::findOrFail($id)->update($data);
    }
    public function delete($id)
    {
        return File::destroy($id);
    }
    public function find($id)
    {
        return File::findOrFail($id);
    }
    public function lockForUpdate($id)
    {
        return File::sharedLock()->find($id);
    }
}