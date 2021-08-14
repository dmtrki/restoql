<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
  //protected $attributes = ['url'];
  //
  protected $appends = ['url'];
  protected $hidden = ['model'];

  function getUrlAttribute()
  {
    return $this->getFullUrl();
  }
}
