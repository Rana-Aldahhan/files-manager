<?php

namespace App\Repositories\Files;

use App\Interfaces\FileLogRepositoryInterface;
use App\Models\FileLog;

class EloquentFileLogRepository implements FileLogRepositoryInterface
{

    public function all()
    {
        return FileLog::all();
    }
    public function create(array $data)
    {
        return FileLog::create($data);
    }
    public function update(array $data, $id)
    {
        return FileLog::findOrFail($id)->update($data);
    }
    public function delete($id)
    {
        return FileLog::destroy($id);
    }
    public function find($id)
    {
        return FileLog::findOrFail($id);
    }
    public function getFileLog($id)
    {
        return FileLog::where('file_id', $id)->latest()->get();
        ;
    }
}