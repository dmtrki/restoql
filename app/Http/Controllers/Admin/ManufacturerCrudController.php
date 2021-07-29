<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ManufacturerRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Gaspertrix\Backpack\DropzoneField\Traits\HandleAjaxMedia;

/**
 * Class ManufacturerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ManufacturerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Manufacturer::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/manufacturer');
        CRUD::setEntityNameStrings('производителя', 'производители');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      CRUD::addColumn(
        [
          'name' => 'title', 
          'type' => 'text',
          'label' => 'Название'
        ]
      );
      CRUD::addColumn(
        [
          'name' => 'logo', 
          'type'  => 'model_function',
          'function_name' => 'getLogoUrl',
          'label' => 'Логотип',
          'limit' => 1024
        ]
      );
      
      CRUD::addColumn(
        [
          'name' => 'updated_at', 
          'type' => 'datetime',
          'label' => 'Обновлен'
        ]
      );
      CRUD::addColumn(
        [
          'name' => 'created_at', 
          'type' => 'date',
          'label' => 'Создан'
        ]
      );
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ManufacturerRequest::class); 
        $this->crud->addField([
          'label' => 'Название',
          'type' => 'text',
          'name' => 'title',
        ]);
        CRUD::field('slug');
        $this->crud->addField([
          'label' => 'Логотип',
          'type' => 'dropzone_media',
          'name' => 'logo',
          'collection' => 'logo',
          'options' => [
            'thumbnailHeight' => 55,
            'maxFilesize' => 10,
            'addRemoveLinks' => true,
            'createImageThumbnails' => true,
            'dictMaxFilesExceeded' => 'Вы можете загрузить только 1 логотип для производителя'
          ],
          'crop' => [89,55]
        ]);
        $this->crud->addField([
          'label' => 'Описание',
          'type' => 'wysiwyg',
          'name' => 'description',
        ]);
        $this->crud->addField([
          'label' => 'Страна',
          'type' => 'text',
          'name' => 'country',
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
