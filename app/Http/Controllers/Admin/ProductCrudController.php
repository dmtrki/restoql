<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Traits\AjaxMediable;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use AjaxMediable;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/products');
        CRUD::setEntityNameStrings('товар', 'товары');
        $this->crud->denyAccess('show');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('title')
              ->label('Название');
        CRUD::column('slug')
              ->label('URL');
        CRUD::column('category')
              ->label('Категория')
              ->type('relationship')
              ->attribute('title');
        CRUD::column('manufacturer')
              ->label('Производитель')
              ->type('relationship')
              ->attribute('title');
        CRUD::column('price')
              ->label('Цена')
              ->type('number');
        CRUD::column('created_at')
              ->label('Создан');
        CRUD::column('updated_at')
              ->label('Обновлен');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);

        $tab = 'Основная информация';

        CRUD::addField([
            'label' => 'Название',
            'type' => 'text',
            'name' => 'title',
            'tab' => $tab
        ]);
        CRUD::addField([
            'label' => 'Описание',
            'type' => 'easymde',
            'name' => 'description',
            'tab' => $tab
        ]);
        CRUD::addField([    
            'label' => 'Категория',
            'type' => 'relationship',
            'name' => 'category_uuid',
            'attribute' => 'title',
            'model' => 'App\Models\ProductCategory',
            'placeholder' => 'Выберите из списка',
            'tab' => $tab
        ]);
        CRUD::addField([
            'label' => 'Производитель',
            'type' => 'relationship',
            'name' => 'manufacturer_uuid',
            'attribute' => 'title',
            'model' => 'App\Models\Manufacturer',
            'placeholder' => 'Выберите из списка',
            'tab' => $tab
        ]);
        CRUD::addField([
            'label' => 'Цена в рублях',
            'type' => 'number',
            'name' => 'price',
        ]);
        CRUD::addField([
            'label' => 'Количество на складе',
            'type' => 'number',
            'name' => 'quantity',
        ]);

        $tab = 'Фотографии';

        CRUD::addField([
          'label' => 'Фотографии товара',
          'type' => 'dropzone_media',
          'name' => 'photos',
          'collection' => 'photos',
          'options' => [
            'thumbnailHeight' => 244,
            'maxFilesize' => 10,
            'addRemoveLinks' => true,
            'createImageThumbnails' => true,
          ],
            'tab' => $tab
        ]);

        $tab = 'Дополнительно';

        CRUD::addField([
          'label' => 'Дополнительно',
          'type' => 'text',
          'name' => 'details',
          'tab' => $tab
        ]);

    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
