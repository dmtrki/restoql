<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MenuRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MenuCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MenuCrudController extends CrudController
{
  use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

  /**
   * Configure the CrudPanel object. Apply settings to all operations.
   *
   * @return void
   */
  public function setup()
  {
    CRUD::setModel(\App\Models\Menu::class);
    CRUD::setRoute(config('backpack.base.route_prefix') . '/menu');
    CRUD::setEntityNameStrings('меню', 'меню');
  }

  /**
   * Define what happens when the List operation is loaded.
   *
   * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
   * @return void
   */
  protected function setupListOperation()
  {
    CRUD::addColumn([
      'name' => 'title',
      'type' => 'text',
      'label' => 'Название',
    ]);

    CRUD::addColumn([
      'type' => 'href-to-menu-items',
      'label' => 'Пункты меню',
    ]);

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
    CRUD::setValidation(MenuRequest::class);

    CRUD::addField([
      'name' => 'site',
      'type' => 'select',
      'attribute' => 'title',
      'label' => 'Для сайта',
      'hint' => 'Можно оставить пустым'
    ]);

    CRUD::addField([
      'name' => 'title',
      'type' => 'text',
      'label' => 'Название'
    ]);

    CRUD::addField([
      'name' => 'slug',
      'type' => 'text',
      'label' => 'Ярлык'
    ]);

    CRUD::addField([
      'name' => 'position',
      'type' => 'text',
      'label' => 'Позиция'
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
