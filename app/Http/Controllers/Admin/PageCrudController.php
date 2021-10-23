<?php

namespace App\Http\Controllers\Admin;

use Backpack\PageManager\app\Http\Requests\PageRequest;
use Backpack\PageManager\app\Http\Controllers\Admin\PageCrudController as BaseController;
use Str;

class PageCrudController extends BaseController
{
  use \App\PageTemplates;

  public function setup()
  {
    $this->crud->setModel('App\Models\Page');
    $this->crud->setRoute(config('backpack.base.route_prefix') . '/page');
    $this->crud->setEntityNameStrings(trans('backpack::pagemanager.page'), trans('backpack::pagemanager.pages'));
  }

  protected function setupListOperation()
  {
    $this->crud->addColumn([
      'name' => 'name',
      'label' => 'Название',
    ]);
    $this->crud->addColumn([
      'name'         => 'site',
      'type'         => 'relationship',
      'label'        => 'Сайт',
      'hint'         => 'Оставьте пустым, чтобы страница была доступна для всех сайтов.'
    ]);
    $this->crud->addColumn([
      'name' => 'template',
      'label' => 'Шаблон',
      'type' => 'model_function',
      'function_name' => 'getTemplateName',
    ]);
    $this->crud->addColumn([
      'name' => 'url',
      'label' => 'URL',
      'prefix' => '/'
    ]);

    // $this->crud->addButtonFromModelFunction('line', 'open', 'getOpenButton', 'beginning');
    $this->crud->removeButton('show');
  }

  protected function setupCreateOperation()
  {
    $this->crud->setCreateContentClass('col-md-12');
    $this->addDefaultPageFields(\Request::input('template'));
    $this->useTemplate(\Request::input('template'));
    $this->crud->setValidation(PageRequest::class);
  }

  protected function setupUpdateOperation()
  {
    $this->crud->setEditContentClass('col-md-12');
    $template = \Request::input('template') ?? $this->crud->getCurrentEntry()->template;
    $this->addDefaultPageFields($template);
    $this->useTemplate($template);
    $this->crud->setValidation(PageRequest::class);
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
      'tab' => 'Основное'
    ]);
    $this->crud->addField([
      'name' => 'name',
      'label' => trans('backpack::pagemanager.page_name'),
      'type' => 'text',
      'wrapperAttributes' => [
        'class' => 'form-group col-md-6',
      ],
      'tab' => 'Основное'
    ]);
    $this->crud->addField([
      'name' => 'title',
      'label' => trans('backpack::pagemanager.page_title'),
      'type' => 'text',
      'tab' => 'Основное'
    ]);
    $this->crud->addField([
      'name' => 'slug',
      'label' => trans('backpack::pagemanager.page_slug'),
      'type' => 'text',
      'hint' => trans('backpack::pagemanager.page_slug_hint'),
      'tab' => 'Основное',
      'disabled' => 'disabled'
    ]);
    $this->crud->addField([
      'name' => 'url',
      'label' => 'URL страницы',
      'type' => 'page_url',
      'allows_null' => false,
      'tab' => 'Основное'
    ]);
  }


  /**
   * Add the fields defined for a specific template.
   *
   * @param  string $template_name The name of the template that should be used in the current form.
   */
  public function useTemplate($template_name = false)
  {
    $templates = $this->getTemplates();

    // set the default template
    if ($template_name == false) {
      $template_name = $templates[0]->name;
    }

    // actually use the template
    if ($template_name) {
      $this->{$template_name}();
    }
  }

  /**
   * Get all defined templates.
   */
  public function getTemplates($template_name = false)
  {
    $templates_array = [];

    $templates_trait = new \ReflectionClass('App\PageTemplates');
    $templates = $templates_trait->getMethods(\ReflectionMethod::IS_PRIVATE);

    if (!count($templates)) {
      abort(503, trans('backpack::pagemanager.template_not_found'));
    }

    return $templates;
  }

  public function getTemplatesLabels()
  {
    $templates_trait = new \ReflectionClass('App\PageTemplates');
    return $templates_trait->getStaticPropertyValue('templatesLabels');
  }

  /**
   * Get all defined template as an array.
   *
   * Used to populate the template dropdown in the create/update forms.
   */
  public function getTemplatesArray()
  {
    $templates = $this->getTemplates();
    $labels = $this->getTemplatesLabels();

    foreach ($templates as $template) {
      $templates_array[$template->name] = (array_key_exists($template->name, $labels)) ? $labels[$template->name] : str_replace('_', ' ', Str::title($template->name));
    }

    return $templates_array;
  }
}
