<?php

namespace App\Repositories\Groups;

use App\Interfaces\GroupRepositoryInterface;
use App\Models\Group;

class EloquentGroupRepository implements GroupRepositoryInterface{

    public function all(){
        return Group::with(['files.reserver','members'])->get();
    }
    public function create(array  $data){
        return Group::create($data);
    }
    public function update(array $data, $id){
        return Group::findOrFail($id)->update($data);
    }
    public function delete($id){
        return Group::destroy($id);
    }
    public function find($id){
        return Group::with(['files.reserver','members'])->findOrFail($id);
    }
}