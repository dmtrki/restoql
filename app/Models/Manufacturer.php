<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Manufacturer extends Model implements HasMedia
{
    use CrudTrait, HasSlug, GeneratesUuid, InteractsWithMedia;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'slug',
        'title',
        'country_code',
        'description',
        'details',
    ];

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'details' => 'array'
      ];


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
              ->singleFile()
              ->registerMediaConversions(function (Media $media) {
                  $this
                      ->addMediaConversion('thumbnail')
                      ->width(155);
              });
    }

    public function backpackGetLogoUrl()
    {
      $media = \App\Models\Media::where('model_type', 'App\Models\Manufacturer')->where('collection_name', 'logo')->where('model_id',$this->id)->first();
      return empty($media) ? '' : '<img style="max-width: 100%; height: 55px;" src="'.$media->getUrl().'" />';
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
