@extends('admin.layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h4>
      {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i> Mail Templates ({{$data->total()}})
    </h4>

  </section>

  <!-- Main content -->
  <section class="content">
    @if(Session::has('info_message'))
		    <div class="alert alert-warning">
		    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">×</span>
								</button>
		      <i class="fa fa-warning margin-separator"></i>  {{ Session::get('info_message') }}
		    </div>
		@endif

    @if(Session::has('success_message'))
    <div class="alert alert-success">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
      </button>
      <i class="fa fa-check margin-separator"></i> {{ Session::get('success_message') }}
    </div>
    @endif

    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title"> Mail Templates</h3>
            <div class="box-tools">
              <a href="{{ url('panel/admin/mail-template/add') }}" class="btn btn-sm btn-success no-shadow pull-right">
                <i class="glyphicon glyphicon-plus myicon-right"></i> {{ trans('misc.add_new') }}
              </a>
            </div>
          </div><!-- /.box-header -->



          <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
              <tbody>

                @if( $data->count() != 0 )
                <tr>
                  <th class="active">ID</th>
                  <th class="active">Type</th>
                  <th class="active">Template Subject</th>
                  <th class="active">Template Body</th>
                  <th class="active">{{ trans('admin.actions') }}</th>
                </tr>

                @foreach( $data as $item )
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->type }}</td>
                  <td>{{ $item->template_subject }}</td>
                  <td>{{ $item->template_body }}</td>
                  <td>
                    <a href="{{ url('panel/admin/mail-template/edit/').'/'.$item->id }}" class="btn btn-success btn-xs padding-btn">
                      {{ trans('admin.edit') }}
                    </a>
                  </td>
                </tr>
                @endforeach

                @else
                <hr />
                <h3 class="text-center no-found">{{ trans('misc.no_results_found') }}</h3>
                @endif

              </tbody>

            </table>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
        @if( $data->lastPage() > 1 )
        {{ $data->links() }}
        @endif
      </div>
    </div>

    <!-- Your Page Content Here -->

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection

@section('javascript')

<script type="text/javascript">
  $(".actionDelete").click(function(e) {
    e.preventDefault();

    var element = $(this);
    var url = element.attr('data-url');

    element.blur();

    swal({
        title: "{{trans('misc.delete_confirm')}}",
        type: "warning",
        showLoaderOnConfirm: true,
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "{{trans('misc.yes_confirm')}}",
        cancelButtonText: "{{trans('misc.cancel_confirm')}}",
        closeOnConfirm: false,
      },
      function(isConfirm) {
        if (isConfirm) {
          window.location.href = url;
        }
      });


  });
</script>
@endsection