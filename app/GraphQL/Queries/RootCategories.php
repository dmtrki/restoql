<?php

namespace App\GraphQL\Queries;

class RootCategories
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        return \App\Models\ProductCategory::whereNull('parent_id')->with('media')->get();
    }
}
