<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PageRequest;
use Backpack\PageManager\app\Http\Controllers\Admin\PageCrudController as BasePageCrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PageCrudController extends BasePageCrudController
{
  public function setup()
  {
      $this->crud->setModel(config('backpack.pagemanager.page_model_class', 'Backpack\PageManager\app\Models\Page'));
      $this->crud->setRoute(config('backpack.base.route_prefix').'/page');
      $this->crud->setEntityNameStrings('страницу', 'страницы');
  }
    public function setupListOperation()
    {      
      $this->crud->addColumn([
          'name' => 'name',
          'label' => trans('backpack::pagemanager.name'),
      ]);
      $this->crud->addColumn([
          'name' => 'slug',
          'label' => trans('backpack::pagemanager.slug'),
      ]);
      $this->crud->addClause('where', 'template', '!=', 'common');
    }

    public function addDefaultPageFields($template = false)
    {
        $this->crud->addField([
            'name' => 'template',
            'label' => trans('backpack::pagemanager.template'),
            'type' => 'select_page_template',
            'view_namespace' => file_exists(resource_path('views/vendor/backpack/crud/fields/select_page_template.blade.php')) ? null : 'pagemanager::fields',
            'options' => $this->getTemplatesArray(),
            'value' => $template,
            'allows_null' => false,
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        $this->crud->addField([
            'name' => 'name',
            'label' => 'Название страницы',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        $this->crud->addField([
            'name' => 'title',
            'label' => 'Заголовок',
            'type' => 'text',
        ]);
        $this->crud->addField([
            'name' => 'slug',
            'label' => 'URL',
            'type' => 'text',
            'hint' => trans('backpack::pagemanager.page_slug_hint'),
        ]);        
    }

    protected function setupUpdateOperation()
    {
        // if the template in the GET parameter is missing, figure it out from the db
        $template = \Request::input('template') ?? $this->crud->getCurrentEntry()->template;

        if($template !== 'common') $this->addDefaultPageFields($template);
        $this->useTemplate($template);

        $this->crud->setValidation(PageRequest::class);
    }

    public function getTemplatesArray()
    {
        $templates = parent::getTemplatesArray();

        foreach ($templates as $key => $templateName) {
          switch ($key) {
            case 'default':
              $templates[$key] = 'По-умолчанию';
              break;
            case 'visual':
              $templates[$key] = 'С визуальным редактором';
              break;
            case 'service':
              $templates[$key] = 'Услуги';
              break;
            case 'about_us':
              $templates[$key] = 'О компании';
              break;
            case 'homepage':
              $templates[$key] = 'Главная';
              break;
            default:
              break;
          }
        }

        unset($templates['common']);

        return $templates;
    }
}
