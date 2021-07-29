<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\MenuRequest;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MenuCrudController.
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
        $this->crud->setModel(\App\Models\Menu::class);
        $this->crud->setRoute(config('backpack.base.route_prefix').'/menu');
        $this->crud->setEntityNameStrings('меню', 'меню');
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
        $this->setupColumns(false);
    }

    protected function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupColumns(true);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(MenuRequest::class);
        $this->setupFields();
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

    private function setupColumns($isShowing = false)
    {
        $this->crud->addButtonFromModelFunction('line', 'custom_actions', 'customActions', 'beginning');

        $this->crud->addColumn([
            'name'  => 'name',
            'label' => 'Нзвание',
            'type'  => 'text',
        ]);
        $this->crud->addColumn([
            'name'    => 'placement',
            'label'   => 'Расположение',
            'type'    => 'select_from_array',
            'options' => config('backpack.menu.placement'),
        ]);
    }

    private function setupFields()
    {
        $this->crud->addField([
            'name'  => 'name',
            'label' => 'Нзвание',
            'type'  => 'text',
        ]);
        $this->crud->addField([
            'name'        => 'placement',
            'label'       => 'Расположение',
            'type'        => 'select2_from_array',
            'options'     => config('backpack.menu.placement'),
            'allows_null' => true,
            'default'     => null,
        ]);
    }
}
