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
use Kalnoy\Nestedset\NodeTrait;


class ProductCategory extends Model
{
    use CrudTrait, HasSlug, GeneratesUuid, InteractsWithMedia, NodeTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'id',
        'uuid',
        'slug',
        'title',
        'details',
    ];

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'details' => 'array',
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

    public function registerMediaCollections()
    {
        $this->addMediaCollection('picture')
              ->singleFile()
              ->registerMediaConversions(function (Media $media) {
                  $this
                      ->addMediaConversion('thumbnail')
                      ->width(223);
              });
    }

    public function backpackGetPictureUrl()
    {
      $media = \App\Models\Media::where('model_type', 'App\Models\ProductCategory')->where('collection_name', 'picture')->where('model_id',$this->id)->first();
      return empty($media) ? '' : '<img style="max-width: 100%; height: 55px;" src="'.$media->getUrl().'" />';
    }

    // nested 

    public function last_children()
    {
      return $this->children()
        ->select('id', 'uuid', 'title', 'slug', 'parent_id')
        ->limit(6);
    }

    public function childrenAndSelf()
    {
        $childrenAndSelf = $this->children;
        $childrenAndSelf->prepend($this);
        return $childrenAndSelf;
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function attributeGroups()
    {
        return $this->belongsToMany(AttributeGroup::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_category_attributes', 'product_category_uuid', 'attribute_uuid')->using(ProductCategoryAttribute::class)->withPivot('values');
    }

    public function manufacturers()
    {
        return $this->hasManyThrough(Manufacturer::class, Product::class);
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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
