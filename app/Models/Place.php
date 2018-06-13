<?php

namespace App\Models;

use Auth;
use Illuminate\Database\QueryException;

class Place extends Content
{
    private $objContentGeometry;
    private $objContentGeometryCoordinate;
    private $objTimezone;

    public function __construct()
    {
        $this->objContentGeometry = new ContentGeometry;
        $this->objContentGeometryCoordinate = new ContentGeometryCoordinate;
        $this->objTimezone = new TimeZone;
    }

    public function addBulkPlace($dataPlace, $user_id, $owner_id)
    {
        $result = true;
        $listPlace = $dataPlace['features'];
        $timezone = $this->objTimezone->getOneTimeZoneByName(session('timezone'));
        for ($i = 0; $i < count($listPlace); $i++) {
            $code = str_replace(' ', '-', strtolower($listPlace[$i]['properties']['Name']));
            $keyword = str_replace(' ', ',', strtolower($listPlace[$i]['properties']['Name']));
            $objContent = new Content;
            $content = $this->getContentByCode($code);
            $placeProperties = $listPlace[$i]['properties'];
            $placeGeometry = $listPlace[$i]['geometry'];
            $placeCoordinate = $listPlace[$i]['geometry']['coordinates'];

            if ($content == null) {
                $objNewContent = new \StdClass;
                $objNewContent->title = $placeProperties['Name'];
                $objNewContent->code = $code;
                $objNewContent->description = $placeProperties['description'];
                $objNewContent->keyword = $keyword;
                $objNewContent->og_title = $placeProperties['Name'];
                $objNewContent->og_description = $placeProperties['description'];
                $objNewContent->default_image = '1';
                $objNewContent->status_id = '2';
                $objNewContent->language_id = '1';
                $objNewContent->publish_date = date_format_to_utc();
                $objNewContent->additional_info = json_encode($listPlace[$i]);
                $objNewContent->content = '';
                $objNewContent->time_zone_id = $timezone->id;
                $objNewContent->owner_id = $owner_id;
                $objNewContent->user_id = $user_id;

                $content = $this->addContent($objNewContent);
                if (!$content) {
                    $result = false;
                }
            } else {
                try {
                    $content->title = $placeProperties['Name'];
                    $content->code = $code;
                    $content->description = $placeProperties['description'];
                    $content->keyword = $keyword;
                    $content->og_title = $placeProperties['Name'];
                    $content->og_description = $placeProperties['description'];
                    $content->default_image = '1';
                    $content->status_id = '2';
                    $content->language_id = '1';
                    $content->publish_date = date_format_to_utc();
                    $content->additional_info = json_encode($listPlace[$i]);
                    $content->content = '';
                    $content->time_zone_id = $timezone->id;
                    $content->owner_id = $owner_id;
                    $content->user_id = $user_id;
                    $content->save();
                } catch (QueryException $e) {
                    report($e);
                    $result = false;
                }
            }

            $contentGeometry = $this->objContentGeometry->getContentGeometryByContentId($content->id);

            if (count($contentGeometry) > 0) {
                $this->objContentGeometryCoordinate->deleteGeometryCoordinateByGeometryId($contentGeometry[0]->id);
            }
            $this->objContentGeometry->deleteContentGeometryByContentId($content->id);

            $placeGeometry['content_id'] = $content->id;
            $placeGeometry['user_id'] = $user_id;
            
            $placeGeometry = $this->objContentGeometry->addContentGeometry((Object) $placeGeometry);
            if(!$placeGeometry){
                $result = false;
                
            }else{
                $placeCoordinate['geometry_id'] = $placeGeometry->id;
                $placeCoordinate['user_id'] = $user_id;
                $geometryCoordinate = $this->objContentGeometryCoordinate->addGeometryCoordinate($placeCoordinate);
                if(!$geometryCoordinate){
                    $result=false;
                    dd($placeGeometry);
                }
            }
            

            if (!$result) {
                break;
            }
        }
        return $result;
    }

    public function getAllPlace()
    {
        return $this->get();
    }

    public function contentGeometry()
    {
        return $this->hasMany('App\Models\content_geometries', 'content_id', 'id');
    }
}
