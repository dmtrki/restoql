<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProjectCategoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Gaspertrix\Backpack\DropzoneField\Traits\HandleAjaxMedia;

/**
 * Class ProjectCategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProjectCategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
    use HandleAjaxMedia;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ProjectCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/project_category');
        CRUD::setEntityNameStrings('Категория проектов', 'Категории проектов');
        $this->crud->denyAccess('show');
        $this->crud->addClause('orderBy', 'lft', 'asc');
        $this->crud->enableReorder('title', 1);
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

      CRUD::addColumn(
        [
          'name' => 'icon', 
          'type'  => 'model_function',
          'function_name' => 'getIconUrl',
          'label' => 'Иконка',
          'limit' => 1024
        ]
      );

      CRUD::column('slug')
            ->label('URL');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProjectCategoryRequest::class);

        CRUD::field('title')
              ->label('Название');

        CRUD::field('slug')
              ->label('URL');

        

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
          'label' => 'Иконка',
          'type' => 'dropzone_media',
          'name' => 'icon',
          'collection' => 'icon',
          'options' => [
            'thumbnailHeight' => 55,
            'maxFilesize' => 10,
            'addRemoveLinks' => true,
            'createImageThumbnails' => true,
          ],
          'crop' => [89,89]
        ]);
    }
}
