<?php

namespace App\Services;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class ProductsIndex extends IndexConfigurator
{
    use Migratable;

    /**
     * @var array
     */
    protected $settings = [
      'analysis' => [
        'analyzer' => [
          'camel' => [
            'type' => 'pattern',
            'pattern' => '([^\\p{L}\\d]+)|(?<=\\D)(?=\\d)|(?<=\\d)(?=\\D)|(?<=[\\p{L}&&[^\\p{Lu}]])(?=\\p{Lu})|(?<=\\p{Lu})(?=\\p{Lu}[\\p{L}&&[^\\p{Lu}]])'
          ],
        ],
      ]
    ];
}