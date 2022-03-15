<?php

namespace App;

trait PageTemplates
{
  public static $templatesLabels = [
    'default' => 'По-умолчанию'
  ];

    private function default()
    {
      $this->metaFields();
      $this->crud->addField([
        'name' => 'content',
        'label' => 'Содержимое',
        'type' => 'wysiwyg',
        'tab' => 'Основное'
      ]);
    }

    private function visual()
    {
      $this->metaFields();
      $this->crud->addField([
        'name' => 'content',
        'label' => 'Содержимое',
        'type' => 'laraberg',
      ]);
    }

    private function service()
    {
      $this->metaFields();
      $this->crud->addField([   // CustomHTML
                      'name' => 'content_separator',
                      'type' => 'custom_html',
                      'value' => '<br><h2>'.trans('backpack::pagemanager.content').'</h2><hr>',
                  ]);
      $this->crud->addField([
                      'name' => 'content',
                      'label' => trans('backpack::pagemanager.content'),
                      'type' => 'wysiwyg',
                      'placeholder' => trans('backpack::pagemanager.content_placeholder'),
                  ]);
    }

    private function about_us()
    {
      $this->metaFields();
      $this->crud->addField([
          'name' => 'content',
          'label' => trans('backpack::pagemanager.content'),
          'type' => 'wysiwyg',
          'placeholder' => trans('backpack::pagemanager.content_placeholder'),
      ]);
    }

    private function homepage()
    {
      $this->metaFields();
      $this->crud->addField(
        [   // repeatable
          'name'  => 'home_featured',
          'label' => 'Карточки продвижения',
          'type'  => 'repeatable',
          'fields' => [
            [
              'name' => 'title',
              'label' => 'Название',
            ],
            [
              'name' => 'details',
              'label' => 'Текст ссылки',
            ],
            [
              'label'        => 'Иллюстрация',
              'name'         => 'image',
              'filename'     => null, // set to null if not needed
              'type'         => 'base64_image',
              'aspect_ratio' => 0, // set to 0 to allow any aspect ratio
              'crop'         => true, // set to true to allow cropping, false to disable
              'src'          => NULL, // null to read straight from DB, otherwise set to model accessor function
            ],
            [
              'name'       => ['type', 'link', 'page_id'],
              'label'      => "Ссылка",
              'type'       => 'page_or_link',
              'page_model' => '\App\Models\Page'
            ],
            [
              'label'                => 'Начальный цвет фона',
              'name'                 => 'bg_start',
              'type'                 => 'color_picker',
              'default'              => '#000000',
            ],
            [
              'label'                => 'Конечный цвет фона',
              'name'                 => 'bg_finish',
              'type'                 => 'color_picker',
              'default'              => '#ffffff',
            ]
          ],
          'fake' => true,
          'store_in' => 'extras',
          'new_item_label'  => 'Добавить пункт', // customize the text of the button
          'max_rows' => 10,
          'tab' => 'Содержимое'
        ],
      );
      $this->crud->addField([
        'name' => 'home_title',
        'label' => 'H1 заголовок',
        'type' => 'textarea',
        'fake' => true,
        'store_in' => 'extras',
        'tab' => 'Содержимое'
      ]);
    }

    private function common()
    {
        $this->crud->addField([
          'name' => 'common_phone',
          'label' => 'Номер телефона',
          'fake' => true,
          'store_in' => 'extras',
        ]);
        $this->crud->addField([
          'name' => 'common_email',
          'label' => 'Е-почта',
          'fake' => true,
          'store_in' => 'extras',
        ]);
        $this->crud->addField([
          'name' => 'common_company',
          'label' => 'Данные об организации',
          'type' => 'repeatable',
          'fields' => [
            [
              'name'=>'row',
              'label' => ''
            ],
            [
              'name'  => 'mini',
              'label' => 'Уменьшить?',
              'type'  => 'checkbox'
            ],
          ],
          'fake' => true,
          'store_in' => 'extras',
        ]);
        $this->crud->addField([
          'name' => 'common_directions',
          'label' => 'Сферы деятельности',
          'type' => 'textarea',
          'fake' => true,
          'store_in' => 'extras',
        ]);
        $this->crud->addField(
          [   // repeatable
            'name'  => 'common_menu_top',
            'label' => 'Верхнее меню',
            'type'  => 'repeatable',
            'fields' => [
              [   // PageOrLink
                'name'       => ['type', 'link', 'page_id'],
                'label'      => "Тип",
                'type'       => 'page_or_link',
                'page_model' => '\App\Models\Page'
              ],
            ],
            'fake' => true,
            'store_in' => 'extras',
            'new_item_label'  => 'Добавить пункт', // customize the text of the button
            'max_rows' => 10 // maximum rows allowed, when reached the "new item" button will be hidden
          ],
        );
    }

    public function metaFields() {
      $this->crud->addField([   // CustomHTML
        'name' => 'metas_separator',
        'type' => 'custom_html',
        'value' => '<br><h2>Мета-теги</h2><hr>',
        'tab' => 'Мета'
      ]);
      $this->crud->addField([
        'name' => 'meta_title',
        'label' => trans('backpack::pagemanager.meta_title'),
        'fake' => true,
        'store_in' => 'extras',
        'tab' => 'Мета'
      ]);
      $this->crud->addField([
        'name' => 'meta_description',
        'label' => trans('backpack::pagemanager.meta_description'),
        'fake' => true,
        'store_in' => 'extras',
        'tab' => 'Мета'
      ]);
      $this->crud->addField([
        'name' => 'meta_keywords',
        'type' => 'textarea',
        'label' => trans('backpack::pagemanager.meta_keywords'),
        'fake' => true,
        'store_in' => 'extras',
        'tab' => 'Мета'
      ]);
    }
}
