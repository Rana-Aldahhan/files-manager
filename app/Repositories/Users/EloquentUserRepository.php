<?php

namespace App\Repositories\Users;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User; 

class EloquentUserRepository implements UserRepositoryInterface{

    public function all(){
        return User::all();
    }
    public function create(array  $data){
        return User::create($data);
    }
    public function update(array $data, $id){
        return User::findOrFail($id)->update($data);
    }
    public function delete($id){
        return User::destroy($id);
    }
    public function find($id){
        return User::findOrFail($id);
    }
    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }
}