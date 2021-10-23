<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MenuItemRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MenuItemCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MenuItemCrudController extends CrudController
{
  use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;

  /**
   * Configure the CrudPanel object. Apply settings to all operations.
   *
   * @return void
   */
  public function setup()
  {
    CRUD::setModel(\App\Models\MenuItem::class);
    CRUD::setRoute(config('backpack.base.route_prefix') . '/menu-item');
    CRUD::setEntityNameStrings('пункт меню', 'пункты меню');
  }

  /**
   * Define what happens when the List operation is loaded.
   *
   * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
   * @return void
   */
  protected function setupListOperation()
  {
    $request = $this->crud->getRequest();
    if ($request->has('menu_id')) {
      $this->crud->addClause('where', 'menu_id', '=', $request->menu_id);
    }
    CRUD::addColumn([
      'name' => 'title',
      'type' => 'text',
      'label' => 'Текст',
    ]);
    CRUD::addColumn([
      'name' => 'url',
      'type' => 'text',
      'label' => 'Ссылка',
    ]);

    CRUD::addColumn([
      'label' => 'Родительский пункт',
      'type' => 'select',
      'name' => 'parent_id',
      'entity' => 'parent',
      'attribute' => 'title',
      'model' => 'App\Models\MenuItem'
    ]);

    CRUD::addColumn([
      'label' => 'Меню',
      'type' => 'select',
      'name' => 'menu_id',
      'entity' => 'menu',
      'attribute' => 'title',
      'model' => 'App\Models\Menu'
    ]);

    CRUD::addFilter([
      'type'  => 'dropdown',
      'name'  => 'menu_id',
      'label' => 'Меню'
    ], function () {
      return \App\Models\Menu::all()->keyBy('id')->pluck('title', 'id')->toArray();
    }, function ($value) {
      $this->crud->addClause('where', 'menu_id', $value);
    });

    $this->crud->removeButton('show');
  }

  /**
   * Define what happens when the Create operation is loaded.
   *
   * @see https://backpackforlaravel.com/docs/crud-operation-create
   * @return void
   */
  protected function setupCreateOperation()
  {
    CRUD::setValidation(MenuItemRequest::class);

    CRUD::addField([
      'type' => "relationship",
      'name' => 'menu',
      'label' => "Для меню",
      'attribute' => "title",
      'entity' => 'menu',
      'model' => "App\Models\Menu",
      'placeholder' => "Выберите меню",
      'required' => true
    ]);

    CRUD::addField([
      'name' => 'title',
      'type' => 'text',
      'label' => 'Текст',
      'tab' => 'Основное'
    ]);

    CRUD::addField([
      'name' => 'url',
      'type' => 'text',
      'label' => 'Ссылка',
      'tab' => 'Основное'
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
