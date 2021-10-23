<?php

namespace App\Models;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasTags, SoftDeletes, CrudTrait, HasSlug, GeneratesUuid, InteractsWithMedia;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */


    protected $fillable = [
        'uuid',
        'slug',
        'category_id',
        'manufacturer_id',
        'status_code',
        'title',
        'price',
        'rating',
        'views',
        'details',
    ];

      protected $casts = [
        'uuid' => EfficientUuid::class,
        'details' => 'array',
        'attributes.pivot.product_uuid' => EfficientUuid::class,
        'attributes.pivot.attribute_uuid' => EfficientUuid::class,
      ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public static function getTagClassName(): string
    {
        return Tag::class;
    }

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

    public function category(): BelongsTo
    {
      return $this->belongsTo(ProductCategory::class)->select('id', 'uuid', 'title', 'slug');
    }

    public function manufacturer(): BelongsTo
    {
      return $this->belongsTo(Manufacturer::class)->select('id', 'uuid', 'title', 'slug', 'country_code');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')->using(ProductAttribute::class)->withPivot('value');
    }

    public function productAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')->using(ProductAttribute::class)->withPivot('value');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(ProductDiscount::class, 'product_uuid', 'uuid');
    }

    public function tags(): MorphToMany
    {
        return $this
            ->morphToMany(self::getTagClassName(), 'taggable', 'taggables', null, 'tag_id')
            ->orderBy('order_column');
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
      return $query->where('status_code', 1);
    }

    /**
     * Select only hidden products
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
    */
    public function scopeHidden(Builder $query): Builder
    {
      return $query->where('status_code', 2);
    }

    /**
     * Shuffles the products randomly
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
    */
    public function scopeRandom($query)
    {
      return $query->inRandomOrder();
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    public function getThumbnailUrl()
    {
      $media = \App\Models\Media::where('model_type', 'App\Models\Product')->where('collection_name', 'photos')->where('model_id',$this->id)->first();
      return empty($media) ? '' : $media->getUrl('thumbnail');
    }

    public function getThumbUrl()
    {
      $media = \App\Models\Media::where('model_type', 'App\Models\Product')->where('collection_name', 'photos')->where('model_id',$this->id)->first();
      return empty($media) ? '' : $media->getUrl('small');
    }

    public function getThumb()
    {
      $media = \App\Models\Media::where('model_type', 'App\Models\Product')->where('collection_name', 'photos')->where('model_id',$this->id)->first();
      return empty($media) ? '' : ['url' => $media->getUrl('small'), 'dimensions' => $media->dimensions];
    }

    public function getAttributesList()
    {
      $output = [];
      foreach ($this->productAttributes as $attr) {
        if ($attr->pivot->value == '') continue;
        $output[] = [
          'text' => $attr->title,
          'value' => $attr->pivot->value,
          'unit' => $attr->unit,
          'id' => $attr->id
        ];
      }
      return $output;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getPriceFormattedAttribute()
    {
      return number_format((int) $this->price, 0, '', ' ') . ' ₽';
    }

    public function getDescriptionAttribute()
    {
      if (!$this->details['description']) return null;

      return (is_array($this->details['description'])) 
             ? $this->details['description'][0] 
             : $this->details['description'];
    }

    public function getDescriptionCuttedAttribute()
    {
      return ($this->description !== null) ? Str::words($this->description, 8, '...') : null;
    }

    public function getQuantityAttribute()
    {
      return $this->details['quantity'] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
