<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Spatie\MediaLibrary\Models\Media;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;

class ProcessImportCategories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $now;
    protected $todayFileName;
    protected $pathToToday;

    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->onQueue('import');
      $this->now = Carbon::now();
      $this->todayFileName = $this->now->format('d-m-y').'.xml';
      $this->pathToToday =storage_path('app/'.$this->todayFileName);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $xml = XML::import($this->pathToToday)->get();
      $categoriesXml = $xml->categories;
      $categories = [];
      $onlyChildren = [];
      foreach ($categoriesXml as $category) {
          if (empty($category->parent_id)) {
            $details = [
              'id' => "$category->id",
              'code' => "$category->code",
              'parent_id' => null
            ];
            $categories["$category->id"] = [
              'title' => "$category->name",
              'details' => $details,
              'children' => []
            ];
          }
      }
      foreach ($categoriesXml as $category) {
        if (!empty($category->parent_id) && isset($categories["$category->parent_id"])) {
          $details = [
            'id' => "$category->id",
            'code' => "$category->code",
            'parent_id' => "$category->parent_id"
          ];
          $categories["$category->parent_id"]['children']["$category->id"] = [
            'title' => "$category->name",
            'details' => $details,
            'children' => []
          ];
          $onlyChildren["$category->id"] = $category;
        }
      }
      foreach ($onlyChildren as $category) {
        if (!empty($category->parent_id) && !isset($categories["$category->parent_id"])) {
          $details = [
            'id' => "$category->id",
            'code' => "$category->code",
            'parent_id' => "$category->parent_id"
          ];
          $parent = $onlyChildren["$category->parent_id"];
          $root = $categories["$parent->parent_id"];
          $root['children']["$category->parent_id"]['children'] = [
            'title' => "$category->name",
            'details' => $details,
            'children' => []
          ];
        }
      }
      $categoriesFormated = [];

      foreach ($categories as $key => $category) {
        $categoriesFormated[] = $category;
      }

      foreach ($categoriesFormated as $key => $item) {
        $children = [];
        foreach ($item['children'] as $childKey => $child) {
          $descenders = [];
          if (!empty($child['children'])) {
            foreach ($child['children'] as $downestKey => $downestChild) {
              $descenders[] = $downestChild;
            }                    
          }
          $child['children'] = $descenders;
          $children[] = $child;
        }
        $categoriesFormated[$key]['children'] = $children;
      }
      
      foreach ($categoriesFormated as $item) {
        if(!ProductCategory::where('title',$item['title'])->exists()){
          $creating = ProductCategory::create($item);
        }
      }

      Log::debug($categoriesFormated);

      $tree = ProductCategory::get()->toTree();
    }

}
