@php
  $value = '';
  if (old($field['name'])) {
      $value = old($field['name']);
  } elseif (isset($field['value']) && isset($field['default'])) {
      $value = array_merge_recursive($field['default'], $field['value']);
  } elseif (isset($field['value'])) {
      $value = $field['value'];
  } elseif (isset($field['default'])) {
      $value = $field['default'];
  }
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
<div class="row flex items-center col-12">
  @if($crud->entry !== null)
    @if ($crud->entry->site !== null)
      <span style="margin: .2rem .5rem .2rem 1rem;">
    https://{{$crud->entry->site->domain}}/
    </span>
    @else
      <span>
        https://**.**/
    </span>
    @endif
  @endif
  <input class="col-4 form-control" id="{{ $field['name'] }}" name="{{ $field['name'] }}" @include('crud::fields.inc.attributes') value="{{ $value }}" />
  @if($crud->entry !== null)
    @if ($crud->entry->site === null)
      <span class="help-block col-md-4">Т.к. сайт не задан, страница будет показываться для всех доменов, если нет страницы с таким же URL специфичной для какого-либо сайта</span>
    @endif
  @endif
</div>

@if (isset($field['hint']))
  <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

<script>
  const   slugInput = document.querySelector('[name="slug"]'),
    urlInput = document.querySelector('[name="url"]')
  console.log(urlInput)
  console.log(slugInput)
  let urlNotModified = true
  urlInput.addEventListener('input', function(e){
    if (!urlNotModified) urlNotModified = false
  })
  slugInput.addEventListener('input', function(e){
    if (urlInput.value == '' || urlNotModified) urlInput.value = e.target.value
  })
</script>
