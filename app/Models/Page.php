<?php

namespace App\Models;

use Backpack\PageManager\app\Models\Page as BaseModel;
use VanOns\Laraberg\Models\Gutenbergable;
use App\Services\GutenbergParser\GutenbergParser;

class Page extends BaseModel
{
  use Gutenbergable;


  protected static function boot()
  {
    parent::boot();
    static::updating(function ($page) {
      $page->lb_content = $page->content;
      $parser = new GutenbergParser($page->lb_content);
      $page->blocks = $parser->parse($page->lb_content);
    });
    static::creating(function ($page) {
      $page->lb_content = $page->content;
      $parser = new GutenbergParser($page->lb_content);
      $page->blocks = $parser->parse($page->lb_content);
    });
  }

  /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

  protected $fillable = [
    'template',
    'name',
    'title',
    'slug',
    'url',
    'content',
    'extras',
    'blocks',
    'site_id'
  ];


  protected $casts = [
    'extras' => 'array',
    'blocks' => 'array'
  ];

  /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

  /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

  public function site()
  {
    return $this->belongsTo(Site::class);
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

  // public function getContentAttribute()
  // {
  //     // return $this->lb_raw_content ?? $this->content;
  //     return $this->content;
  // }

  public function getFullUrlAttribute()
  {
    $detected = request('detected');
    $site = $detected['site'];
    return 'https://' . $site->domain . '/' . $this->url;
  }

  /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
