<?php

namespace App\Models;

use Illuminate\Database\QueryException;

class Place extends Content
{
    private $contentGeometry;
    private $contentGeometryCoordinate;
    private $contentCategory;
    private $timezone;

    public function __construct()
    {
        $this->contentGeometry = new ContentGeometry;
        $this->contentCategory = new ContentCategory;
        $this->contentGeometryCoordinate = new ContentGeometryCoordinate;
        $this->timezone = new TimeZone;
    }

    private function createContentCategory($contentId, $categoryId, $userId){
        $objContentCategory = new \StdClass;
        $objContentCategory->content_id = $contentId;
        $objContentCategory->categories_id = $categoryId;
        $objContentCategory->user_id = $userId;
        return $this->contentCategory->addContentCategory($objContentCategory);
    }

    public function addPlace($placeProperties, $code, $keyword, $listPlace, $timezone, $owner_id, $user_id)
    {
        $objContent = new \StdClass;
        $objContent->title = $placeProperties['Name'];
        $objContent->code = $code;
        $objContent->description = $placeProperties['description'];
        $objContent->keyword = $keyword;
        $objContent->og_title = $placeProperties['Name'];
        $objContent->og_description = $placeProperties['description'];
        $objContent->default_image = '1';
        $objContent->status_id = '2';
        $objContent->language_id = '1';
        $objContent->publish_date = date_format_to_utc();
        $objContent->additional_info = $listPlace;
        $objContent->content = '';
        $objContent->time_zone_id = $timezone->id;
        $objContent->owner_id = $owner_id;
        $objContent->user_id = $user_id;
        
        $result = $this->addContent($objContent);
        if($result!=false){
            $resultContentCategory = $this->contentCategory->getContentCategoryByContentIdAndCategoryId($result->id, '1');
            if($resultContentCategory==null){
                $result = $this->createContentCategory($result->id, '1', $user_id);
            }
        }
    }

    public function updatePlace($content, $placeProperties, $code, $keyword, $listPlace, $timezone, $owner_id, $user_id){
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
            $content->additional_info = $listPlace;
            $content->content = '';
            $content->time_zone_id = $timezone->id;
            $content->owner_id = $owner_id;
            $content->user_id = $user_id;
            $content->save();
            
            $resultContentCategory = $this->contentCategory->getContentCategoryByContentIdAndCategoryId($content->id, '1');
            
            if($resultContentCategory==null){
                $result = $this->addContentCategory($content->id, '1', $user_id);
            }
            return true;
        } catch (QueryException $e) {
            report($e);
            return false;
        }
    }

    public function addBulkPlace($dataPlace, $user_id, $owner_id, $timezone)
    {
        
        $result = true;
        $listPlace = $dataPlace['features'];
        $timezone = $this->timezone->getOneTimeZoneByName($timezone);
        for ($i = 0; $i < count($listPlace); $i++) {
            $code = str_replace(' ', '-', strtolower($listPlace[$i]['properties']['Name']));
            $keyword = str_replace(' ', ',', strtolower($listPlace[$i]['properties']['Name']));
            $objContent = new Content;
            $content = $this->getContentByCode($code);
            $placeProperties = $listPlace[$i]['properties'];
            $placeGeometry = $listPlace[$i]['geometry'];
            $placeCoordinate = $listPlace[$i]['geometry']['coordinates'];

            if ($content == null) {
                $content = $this->addPlace($placeProperties, $code, $keyword, json_encode($listPlace[$i]), $timezone, $owner_id, $user_id);
                if (!$content) {
                    return false;
                }
            } else {
                $result = $this->updatePlace($content, $placeProperties, $code, $keyword, json_encode($listPlace[$i]), $timezone, $owner_id, $user_id);
                if(!$result){
                    return false;
                }
            }

            $contentGeometry = $this->contentGeometry->getContentGeometryByContentId($content->id);

            if (count($contentGeometry) > 0) {
                $this->contentGeometryCoordinate->deleteGeometryCoordinateByGeometryId($contentGeometry[0]->id);
            }
            $this->contentGeometry->deleteContentGeometryByContentId($content->id);

            $placeGeometry['content_id'] = $content->id;
            $placeGeometry['user_id'] = $user_id;

            $placeGeometry = $this->contentGeometry->addContentGeometry((Object) $placeGeometry);
            if (!$placeGeometry) {
                return false;
            } else {
                $placeCoordinate['geometry_id'] = $placeGeometry->id;
                $placeCoordinate['user_id'] = $user_id;
                $geometryCoordinate = $this->contentGeometryCoordinate->addGeometryCoordinate($placeCoordinate);
                if (!$geometryCoordinate) {
                    return false;
                }
            }

        }
        return $content;
    }

    public function getPlaceById($id)
    {
        return $this->find($id);
    }

    public function getAllPlace()
    {
        return $this->get();
    }

    public function contentGeometry()
    {
        return $this->hasMany('App\Models\ContentGeometry', 'content_id', 'id');
    }
}
