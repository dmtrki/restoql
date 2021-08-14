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
        'id',
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

    // Backpack only functions >>>

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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'code', 'country_code');
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeAbicaly($query)
    {
        return $query->orderBy('title')->get()->groupBy(function($item) { 
                                            return mb_substr($item->name, 0, 1); 
                                        });
    }

    public function scopeCountryly($query)
    {
        return $query->orderBy('title')->get()->groupBy('country_code');
    }

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
