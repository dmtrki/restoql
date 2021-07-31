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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use CrudTrait, HasSlug, GeneratesUuid, InteractsWithMedia;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */


    protected $fillable = [
        'uuid',
        'slug',
        'category_uuid',
        'manufacturer_uuid',
        'status_code',
        'title',
        'price',
        'rating',
        'views',
        'details',
    ];

      protected $casts = [
        'uuid' => EfficientUuid::class,
        'category_uuid' => EfficientUuid::class,
        'manufacturer_uuid' => EfficientUuid::class,
        'details' => 'array',
        'attributes.pivot.product_uuid' => EfficientUuid::class,
        'attributes.pivot.attribute_uuid' => EfficientUuid::class,
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
        $this->addMediaCollection('photos')
              ->onlyKeepLatest(10)
              ->registerMediaConversions(function (Media $media) {
                  $this
                    ->addMediaConversion('thumbnail')
                    ->height(55);
                  $this
                    ->addMediaConversion('small')
                    ->height(244);
                  $this
                    ->addMediaConversion('medium')
                    ->height(420);
              });
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function category()
    {
      return $this->belongsTo(\App\Models\ProductCategory::class, 'category_uuid')->select('id', 'uuid', 'title', 'slug');
    }

    public function manufacturer()
    {
      return $this->belongsTo(\App\Models\Manufacturer::class, 'manufacturer_uuid')->select('id', 'uuid', 'title', 'slug');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')->using(ProductAttribute::class)->withPivot('value');
    }

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

    public function getPhotoUrlAttribute()
    {
      $media = \App\Models\Media::where('model_type', 'App\Models\Product')->where('collection_name', 'photos')->where('model_id',$this->id)->first();
      return empty($media) ? '' : $media->getUrl();
    }

    public function getPriceFormattedAttribute()
    {
      return number_format((int) $this->price, 0, '', ' ') . ' ₽';
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
