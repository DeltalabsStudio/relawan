<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class UserStatus extends Model
{
    //
    protected $table = 'user_statuses';
    protected $fillable = ['id','name'];
    protected $hidden = ['created_at', 'updated_at'];
 
    public function addUserStatus($response){
        try{
            $this->name = $response->name;
            $this->save();
            return true;
        }catch(QueryException $e){
            report($e);
            return false;
        }
    }

    public function getUserStatusesByName($name){
        return $this->where('name',$name)->get();
    }
}
