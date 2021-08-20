<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
  // protected $attributes = ['url', 'dimensions'];
  //
  protected $appends = ['url', 'dimensions'];
  protected $hidden = ['model'];

  function getUrlAttribute()
  {
    return $this->getFullUrl();
  }

  function getDimensionsAttribute()
  {
    $d = getimagesize($this->getPath());
    return ($d) ? ['width' => $d[0], 'height' => $d[1]] : null;
  }
}
