<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProjectRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Gaspertrix\Backpack\DropzoneField\Traits\HandleAjaxMedia;

/**
 * Class ProjectCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProjectCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/project');
        CRUD::setEntityNameStrings('Проект', 'Проекты');
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

        CRUD::column('category')
              ->type('relationship')
              ->label('Категория')
              ->entity('category')
              ->attribute('title')
              ->model(App\Models\ProjectCategory::class);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProjectRequest::class);

        CRUD::field('title')
              ->label("Название");
        
        CRUD::field('slug')
              ->label("Slug (URL)");

        CRUD::field('category_id')
              ->type('relationship')
              ->label('Категория')
              ->multiple(false)
              ->entity('category')
              ->attribute('title')
              ->model('App\Models\ProjectCategory')
              ->placeholder('Выберите категорию');



        CRUD::field('description')
              ->type('wysiwyg')
              ->label("Описание");  


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
        CRUD::addField([
          'label' => 'Основное фото',
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

        CRUD::addField([
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
    }
}
