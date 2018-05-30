<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Auth;

class ContentGeometry extends Model
{
    //
    protected $table='content_geometries';

    protected $fillable=['id','content_id','type'];

    protected $hidden = ['user_id', 'created_at','updated_at'];

    public function ContentGeometryCoordinate(){
        return $this->hasMany('App\Models\ContentGeometryCoordinate','geometry_id','id');
    }

    public function posko(){
        return $this->belongsTo('App\Models\Contents','content_id','id');
    }

    public function getContentGeometryByContentId($contentId){
        return $this->where('content_id',$contentId)->get();
    }

    public function deleteContentGeometryByContentId($contentId){
        $contentGeometry = $this->getContentGeometryByContentId($contentId);
        if(count($contentGeometry)>0){
            $contentGeometry->delete();
        }
    }

    public function addContentGeometry($data){
        try{
            $this->content_id = $data->content_id;
            $this->type=$data->type;
            $this->user_id = Auth::id();
            $this->save();
            return $this;
        }catch(QueryException $e){
            report($e);
            return false;
        }
    }
}
