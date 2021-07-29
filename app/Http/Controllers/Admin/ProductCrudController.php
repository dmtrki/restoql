<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Gaspertrix\Backpack\DropzoneField\Traits\HandleAjaxMedia;

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
    use HandleAjaxMedia;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
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

        CRUD::field('title');
        CRUD::field('slug');
        CRUD::field('description');
        $this->crud->addField([
          'label' => 'Основное изображение',
          'type' => 'dropzone_media',
          'name' => 'main',
          'collection' => 'main',
          'options' => [
            'thumbnailHeight' => 244,
            'maxFilesize' => 10,
            'addRemoveLinks' => true,
            'createImageThumbnails' => true,
          ],
        ]);        
        $this->crud->addField([
          'label' => 'Дополнительные фото',
          'type' => 'dropzone_media',
          'name' => 'gallery',
          'collection' => 'gallery',
          'options' => [
            'thumbnailHeight' => 55,
            'maxFilesize' => 10,
            'addRemoveLinks' => true,
            'createImageThumbnails' => true,
          ],
        ]);
        CRUD::field('external');
        CRUD::field('category_id');
        CRUD::field('manufacturer_id');
        CRUD::field('price');
        CRUD::field('quantity');
        CRUD::field('options');
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
