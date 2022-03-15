<?php

namespace App\GraphQL\Queries;

class ManufacturersByAbc
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        return \App\Models\Manufacturer::orderBy('title')->get()
                                        ->groupBy(function($item) {
                                            return mb_substr($item->title, 0, 1);
                                        });
    }
}
