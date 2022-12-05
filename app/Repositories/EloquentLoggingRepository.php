<?php

namespace App\Repositories;

use App\Interfaces\LoggingRepositoryInterface;
use App\Models\RequestLog;

class EloquentLoggingRepository implements LoggingRepositoryInterface
{

    public function all()
    {
        return RequestLog::all();
    }
    public function create(array $data)
    {
        return RequestLog::create($data);
    }
    public function update(array $data, $id)
    {
        return RequestLog::findOrFail($id)->update($data);
    }
    public function delete($id)
    {
        return RequestLog::destroy($id);
    }
    public function find($id)
    {
        return RequestLog::findOrFail($id);
    }
}