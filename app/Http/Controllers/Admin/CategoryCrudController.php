<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Gaspertrix\Backpack\DropzoneField\Traits\HandleAjaxMedia;

/**
 * Class CategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CategoryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Category::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/category');
        CRUD::setEntityNameStrings('категорию', 'категории');
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
          'name' => 'parent', 
          'type' => 'relationship',
          'label' => 'Родительская категория'
        ]
      );
      CRUD::addColumn(
        [
          'name' => 'picture', 
          'type'  => 'model_function',
          'function_name' => 'getPictureUrl',
          'label' => 'Превью',
          'limit' => 1024
        ]
      );
      //CRUD::column('created_at');
      //CRUD::column('updated_at');
      //$this->crud->removeAllFilters();
      
      $this->crud->addFilter([ 
        'type'  => 'dropdown',
        'name'  => 'parent_id',
        'label' => 'Родительская категория'
      ],
      function() {
        $roots = \App\Models\Category::select('id','title')->where('parent_id', null)->get()->keyBy('id')->pluck('title','id')->toArray();
        $roots[0] = 'Только родительские';
        return $roots;
      },
      function($value) { 
        if ($value === '0') {
          $this->crud->addClause('where', 'parent_id', null); 
        } else {
          $this->crud->addClause('where', 'parent_id', $value); 
        }
        
      });
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CategoryRequest::class);

        CRUD::field('title');
        $this->crud->addField([
          'label' => 'Название',
          'type' => 'text',
          'name' => 'title',
        ]);
        CRUD::field('slug');
        $this->crud->addField([
          'label' => 'Превью',
          'type' => 'dropzone_media',
          'name' => 'picture',
          'collection' => 'picture',
          'options' => [
            'thumbnailHeight' => 89,
            'maxFilesize' => 10,
            'addRemoveLinks' => true,
            'createImageThumbnails' => true,
            'dictMaxFilesExceeded' => 'Вы можете загрузить только 1 превью для категории'
          ],
          'crop' => [233,233]
        ]);
        $this->crud->addField([
          'label' => 'Описание',
          'type' => 'wysiwyg',
          'name' => 'description',
        ]);
        $this->crud->addField([  // Select
          'label'     => "Родительская категория",
          'type'      => 'select',
          'name'      => 'parent_id', // the db column for the foreign key
          'attribute' => 'title',
          'allows_null' => true,
          'options'   => (function () {
            $roots = \App\Models\Category::where('parent_id', null)->get();
            return $roots;
           }), //  you can use this to filter the results show in the select
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
