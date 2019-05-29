<div class="box {{ isset($class) ? $class : '' }}" {{ isset($attribute) ? $attribute : '' }}>
  <div class="box-header" {{ isset($boxHeaderAttribute) ? $boxHeaderAttribute : '' }}>
    <div class="row">
      <div class="{{ isset($titleClass) ? $titleClass : 'col-xs-8' }}">
        <h3 class="box-title">{{ isset($title) ? $title : '' }}</h3>
      </div>
      <div class="{{ isset($actionClass) ? $actionClass : 'col-xs-4' }}">
        <div class="pull-right text-right">{{ isset($action) ? $action : '' }}</div>
      </div>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body {{ isset($bodyClass) ? $bodyClass : 'table-responsive no-padding' }}">
    {{ isset($body) ? $body : '' }}
    {{ $slot }}
  </div>
  <!-- /.box-body -->
</div>