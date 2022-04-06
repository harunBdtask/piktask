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
      {{{ trans('admin.edit') }}}
    </h4>

  </section>

  <!-- Main content -->
  <section class="content">

    <div class="content">

      <div class="row">

        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title">{{{ trans('admin.edit') }}}</h3>
          </div><!-- /.box-header -->



          <!-- form start -->
          <form class="form-horizontal" method="post" action="{{{ url('panel/admin/mail-template/update') }}}" enctype="multipart/form-data">

            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
            <input type="hidden" name="id" value="{{{ $data->id }}}">

            @include('errors.errors-forms')
            
      
            <!-- Start Box Body -->
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-2 control-label">Type</label>
                <div class="col-sm-4">
                  <select name="type" class="form-control">
                    <option value="withdrawal_approve" <?php if($data->type == 'withdrawal_approve'){echo 'selected'; } ?> >Withdrawal Approve</option>
                    <option value="withdrawal_reject" <?php if($data->type == 'withdrawal_reject'){echo 'selected'; } ?> >Withdrawal Reject</option>
                  </select>
                </div>
              </div>
            </div><!-- /.box-body -->

            <!-- Start Box Body -->
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-2 control-label">Template Subject</label>
                <div class="col-sm-10">
                  <input type="text" value="{{{ $data->template_subject }}}" name="template_subject" class="form-control" >
                </div>
              </div>
            </div><!-- /.box-body -->

            <!-- Start Box Body -->
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-2 control-label">Template Subject</label>
                <div class="col-sm-6">
                  <textarea class="form-control" name="template_body" cols="80" rows="10">{{{ $data->template_body }}}</textarea>
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



@endsection