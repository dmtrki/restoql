<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LeadRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class LeadCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LeadCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Lead::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lead');
        CRUD::setEntityNameStrings('заявку', 'заявки');

        $this->crud->denyAccess(['create','update']);
        $this->crud->setTitle('Заявки с сайта');
        $this->crud->setHeading('Заявки');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      $this->crud->denyAccess('create');
      
      CRUD::addColumn([
          'name'      => 'row_number',
          'type'      => 'row_number',
          'label'     => '№',
          'orderable' => false,
      ])->makeFirstColumn();

      CRUD::addColumn([
        'name'        => 'status',
        'label'       => 'Статус',
        'type'        => 'radio',
        'options'     => [
            1 => 'Не обработана',
            2 => 'Обработана'
        ]
      ])->afterColumn('row_number');

      CRUD::addColumn([
        'name'  => 'created_at', // The db column name
        'label' => 'Время получения', // Table column heading
        'type'  => 'datetime',
      ]);

      CRUD::addColumn([
        'name'     => 'data->phone', // The db column name
        'label'    => 'Телефон', // Table column heading
        'type'     => 'phone',
      ]);

      CRUD::addColumn([
        'name'  => 'data', // The db column name
        'label' => 'Дополнительная информация', // Table column heading
        'type'  => 'array'
      ]);
    }


}
