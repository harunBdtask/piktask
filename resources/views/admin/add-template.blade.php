@extends('admin.layout')

@section('css')
<link href="{{{ asset('public/plugins/iCheck/all.css') }}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h4>
      {{{ trans('admin.admin') }}}
      <i class="fa fa-angle-right margin-separator"></i>
      Mail Template
      <i class="fa fa-angle-right margin-separator"></i>
      {{{ trans('misc.add_new') }}}
    </h4>

  </section>

  <!-- Main content -->
  <section class="content">

    <div class="content">

      <div class="row">

        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title">{{{ trans('misc.add_new') }}}</h3>
          </div><!-- /.box-header -->



          <!-- form start -->
          <form class="form-horizontal" method="post" action="{{{ url('panel/admin/mail-template/store') }}}" enctype="multipart/form-data">

            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

            @include('errors.errors-forms')

            <!-- Start Box Body -->
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-2 control-label">Type</label>
                <div class="col-sm-4">
                  <select name="type" class="form-control">
                    <option value="withdrawal_approve">Withdrawal Approve</option>
                    <option value="withdrawal_reject">Withdrawal Reject</option>
                  </select>
                </div>
              </div>
            </div><!-- /.box-body -->

            <!-- Start Box Body -->
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-2 control-label">Template Subject</label>
                <div class="col-sm-10">
                  <input type="text" name="template_subject" class="form-control" >
                </div>
              </div>
            </div><!-- /.box-body -->

            <!-- Start Box Body -->
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-2 control-label">Template Subject</label>
                <div class="col-sm-6">
                  <textarea class="form-control" name="template_body" cols="80" rows="10"></textarea>
                </div>
              </div>
            </div><!-- /.box-body -->



            <div class="box-footer">
              <a href="{{{ url('panel/admin/mail-template') }}}" class="btn btn-default">{{{ trans('admin.cancel') }}}</a>
              <button type="submit" class="btn btn-success pull-right">{{{ trans('admin.save') }}}</button>
            </div><!-- /.box-footer -->
          </form>
        </div>

      </div><!-- /.row -->

    </div><!-- /.content -->

    <!-- Your Page Content Here -->

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection

@section('javascript')

<!-- icheck -->
<script src="{{{ asset('public/plugins/iCheck/icheck.min.js') }}}" type="text/javascript"></script>

<script type="text/javascript">
  //Flat red color scheme for iCheck
  $('input[type="radio"]').iCheck({
    radioClass: 'iradio_flat-red'
  });
</script>


@endsection