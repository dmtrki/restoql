<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class MenuItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;

    private $menu_id;
    private $menu;

    public function setup()
    {
        $this->menu_id = \Route::current()->parameter('menu_id');
        $this->menu = \App\Models\Menu::find($this->menu_id);

        $this->crud->setModel("App\Models\MenuItem");
        $this->crud->setRoute(config('backpack.base.route_prefix').'/menu-item/'.$this->menu_id);
        $this->crud->setEntityNameStrings('пункт меню', 'пункты меню');
        $this->crud->addClause('orderBy', 'lft', 'asc');
        $this->crud->addClause('where', 'menu_id', $this->menu_id);

        $this->crud->setHeading('Пункты меню '." - <a href='".backpack_url('menu/'.$this->menu_id.'/show')."'>".$this->menu->name.'</a>', false);

        $this->crud->enableReorder('name', 30); // Basically infinite

        $this->crud->operation('list', function () {
            $this->crud->addColumn([
                'name' => 'name',
                'label' => 'Имя',
            ]);
            $this->crud->addColumn([
                'label' => 'Родитель',
                'type' => 'select',
                'name' => 'parent_id',
                'entity' => 'parent',
                'attribute' => 'name',
                'model' => "\App\Models\MenuItem",
            ]);
        });

        $this->crud->operation(['create', 'update'], function () {
            $this->crud->addField([
                'name' => 'name',
                'label' => 'Имя',
            ]);
            $this->crud->addField([
                'name'  => 'menu_id',
                'type'  => 'hidden',
                'value' => $this->menu_id,
            ]);
            $this->crud->addField([
                'label' => 'Родительский пункт',
                'type' => 'select',
                'name' => 'parent_id',
                'entity' => 'parent',
                'attribute' => 'name',
                'model' => "\App\Models\MenuItem",
            ]);
            $this->crud->addField([
                'name' => ['type', 'link', 'page_id'],
                'label' => 'Тип',
                'type' => 'page_or_link',
                'page_model' => '\App\Models\Page',
            ]);
        });
    }
}
