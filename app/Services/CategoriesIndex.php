<?php

namespace App\Services;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class CategoriesIndex extends IndexConfigurator
{
    use Migratable;

    /**
     * @var array
     */
    protected $settings = [
      'max_ngram_diff' => 4,
      'analysis' => [
        'analyzer' => [
          'camel' => [
            'type' => 'pattern',
            'pattern' => '([^\\p{L}\\d]+)|(?<=\\D)(?=\\d)|(?<=\\d)(?=\\D)|(?<=[\\p{L}&&[^\\p{Lu}]])(?=\\p{Lu})|(?<=\\p{Lu})(?=\\p{Lu}[\\p{L}&&[^\\p{Lu}]])'
          ],
          'ngram_analyzer' => [
            'tokenizer' => 'ngramizer'
          ]
          ],
          'tokenizer' => [
            'ngramizer' => [
              'type' => 'ngram',
              'min_gram' => 3,
              'max_gram' => 7, 
              'token_chars' => [
                'letter', 'digit'
              ]
            ]
          ]
      ]
    ];
}