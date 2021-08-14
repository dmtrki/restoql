<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCategoryMetas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->onQueue('import');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }

    public function categoriesMinMax(Request $request)
    {
      $categories = ProductCategory::all();

      foreach ($categories as $key => $category) {
       

        $maxPrice = DB::table('products')
                      ->where('category_id', $category->id)                      
                      ->where('price', '!=', 0)
                      ->max('price');
        $minPrice = DB::table('products')
          ->where('category_id', $category->id)
          ->where('price', '!=', 0)
          ->min('price');
        
        
        Redis::set('category_price_range:'.$category->id, $minPrice.'-'.$maxPrice);
        var_dump(Redis::get('category_price_range:'.$category->id));
        echo '<hr/>';
      }
      
    }

    public function generateThumbs(Request $request)
    {
      echo '<hr/>';
      echo '<hr/>';
      if ($request->cat) {
        if ($request->cat == 'all') {
          $categories = ProductCategory::all();
        } else {
          $categories = ProductCategory::where('id', (int)$request->cat)->get();
        }

        foreach ($categories as $key => $category) {
          $category->regenerateThumbUrls();
          echo '<b>'.$category->title . '</b><br/>'.Redis::get('category_thumb_url:'.$category->id).'<br><img src="'.Redis::get('category_thumb_url:'.$category->id).'" /><hr>';
        }
      }
    }
}
