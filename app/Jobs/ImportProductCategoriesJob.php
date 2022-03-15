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

class ImportProductCategoriesJob extends ImportBaseJob
{

    public function retryUntil(): \Illuminate\Support\Carbon
    {
        return now()->addMinutes(5);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $xml = $this->getXmlFileContent();
      $categoriesXml = $xml->categories;
      $categories = [];
      $categoriesFlat = [];
      $categoriesRoot = [];
      $onlyChildren = [];
      foreach ($categoriesXml as $category) {
        $parent_id = empty($category->parent_id) ? null : "$category->parent_id";

        $details = [
          'id' => "$category->id",
          'code' => "$category->code",
          'parent_id' => $parent_id
        ];

        $data = [
          'uuid' => "$category->id",
          'title' => "$category->name",
          'details' => $details,
          'parent_id' => $parent_id,
          'children' => []
        ];

        $categoriesFlat["$category->id"] = $data;

        if ($parent_id === null) {
          $categoriesRoot["$category->id"] = $data;
        }
      }

      foreach ($categoriesFlat as $uuid => $category) {
        if ($category['parent_id'] !== null) {
          $categoriesFlat[$category['parent_id']]['children'][] = $category;
        }
      }

      foreach ($categoriesFlat as $uuid => $category) {
        if ($category['parent_id'] !== null && array_key_exists($category['parent_id'], $categoriesRoot)) {
          $categoriesRoot[$category['parent_id']]['children'][] = $category;
        }
      }

      ProductCategory::truncate();
      ProductCategory::rebuildTree($categoriesRoot);
      // ProductCategory::create($categoriesRoot);
      // $tree = ProductCategory::get()->toTree();
    }

}
