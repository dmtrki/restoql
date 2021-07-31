<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<style>.opace{opacity:.5;}</style>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> Данные</a></li>
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('leads') }}"><i class='nav-icon la la-phone-volume'></i> Заявки</a></li>
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-store-alt"></i> Магазин</a>
	<ul class="nav-dropdown-items">
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('orders') }}"><i class='nav-icon la la-shopping-cart'></i> Заказы</a></li>
    <!-- <li class='nav-item opace'><a class='nav-link' href="#"><i class='nav-icon la la-bullhorn'></i> Промо</a></li> -->
    <!--<li class='nav-item'><a class='nav-link' href="{{ backpack_url('dashboard') }}"><i class='nav-icon la la-gift'></i> Купоны</a></li>-->
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('customers') }}"><i class='nav-icon la la-address-card'></i> Покупатели</a></li>
    <li class="nav-item nav-dropdown">
      <a class="nav-link nav-dropdown-toggle" href="#"> Каталог</a>
      <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href="{{ backpack_url('products') }}"><i class='nav-icon la la-grip-horizontal'></i> Товары</a></li>
        <li class='nav-item'><a class='nav-link' href="{{ backpack_url('product-categories') }}"><i class='nav-icon la la-sitemap'></i> Категории</a></li>
        <li class='nav-item'><a class='nav-link' href="{{ backpack_url('manufacturers') }}"><i class='nav-icon la la-industry'></i> Производители</a></li>
        <!-- <li class='nav-item'><a class='nav-link' href="{{ backpack_url('tag') }}"><i class='nav-icon la la-tags'></i> Теги</a></li> -->
        {{--<li class="nav-item nav-dropdown">
          <a class="nav-link nav-dropdown-toggle" href="#"> Аттрибуты</a>
          <ul class="nav-dropdown-items">
            <li class='nav-item'><a class='nav-link' href="{{ backpack_url('attributes') }}"><i class='nav-icon la la-icons'></i> Аттрибуты</a></li>
            <li class='nav-item'><a class='nav-link' href="{{ backpack_url('attribute-groups') }}"><i class='nav-icon la la-icons'></i> Группы аттрибутов</a></li>
          </ul>
        </li>--}}
        {{--<li class='nav-item opace'><a class='nav-link' href="#"><i class='nav-icon la la-clock'></i> Скидки</a></li>--}}
        <li class='nav-item'><a class='nav-link' href="{{ backpack_url('product-reviews') }}"><i class='nav-icon la la-comment'></i> Отзывы</a></li>
      </ul>
    </li>
    <li class="nav-item nav-dropdown">
      <a class="nav-link nav-dropdown-toggle" href="#"> Настройка</a>
      <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href="{{ backpack_url('currencies') }}"><i class='nav-icon la la-ruble-sign'></i> Валюты</a></li>
        <li class='nav-item opace'><a class='nav-link' href="#"><i class='nav-icon la la-truck'></i> Способы доставки</a></li>
        <li class='nav-item opace'><a class='nav-link' href="#"><i class='nav-icon la la-credit-card'></i> Способы оплаты</a></li>
        <li class='nav-item opace'><a class='nav-link' href="#"><i class='nav-icon la la-sync'></i> Синхронизация</a></li>
      </ul>
    </li>
	</ul>
</li>
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-building"></i> Проекты</a>
  <ul class="nav-dropdown-items">
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('projects') }}"><i class='nav-icon la la-suitcase'></i> Кейсы</a></li>
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('project_categories') }}"><i class='nav-icon la la-sitemap'></i> Категории</a></li>
  </ul>
</li>
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-file-alt"></i> Контент</a>
  <ul class="nav-dropdown-items">
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('page/1/edit') }}"><i class='nav-icon la la-home'></i> <span>Общие данные</span></a></li>
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('menu') }}"><i class='nav-icon la la-bars'></i> Меню</a></li>
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('page') }}"><i class='nav-icon fa fa-file-o'></i> <span>Страницы</span></a></li>
    <li class='nav-item'><a class='nav-link' href="{{ backpack_url('stories') }}"><i class='nav-icon fa fa-file-o'></i> <span>Истории</span></a></li>
  </ul>
</li>

<li class='nav-item'><a class='nav-link' href="{{ backpack_url('settings') }}"><i class='nav-icon la la-cog'></i> <span>Настройки</span></a></li>
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('user') }}"><i class='nav-icon la la-user'></i> <span>Пользователи</span></a></li>

@if (backpack_user()->can('manage_roles'))
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Роли</span></a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Полномочия</span></a></li>
@endif
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('systemevents') }}"><i class='nav-icon la la-question'></i> Журнал системы</a></li>