<?php

namespace App\Generators;

use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;
use Illuminate\Support\Str;


class RestorecaMediaPathGenerator extends BasePathGenerator
{
    protected function getBasePath(Media $media): string
    {      
      return Str::plural(strtolower(class_basename($media->model_type)))
            .DIRECTORY_SEPARATOR
            .$media->model_id
            .DIRECTORY_SEPARATOR
            .$media->getKey();
    }

    public function getPath(Media $media): string
    {      
      return Str::plural(strtolower(class_basename($media->model_type)))
            .DIRECTORY_SEPARATOR
            .$media->model_id
            .DIRECTORY_SEPARATOR
            .$media->getKey();
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/converted/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive/';
    }
}