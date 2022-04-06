@extends('admin.layout')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h4>
                {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i>
                Mail <i class="fa fa-angle-right margin-separator"></i>
                Send Mail
            </h4>
        </section>

        <!-- Main content -->
        <section class="content">

            @if (Session::has('info_message'))
                <div class="alert alert-warning">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <i class="fa fa-warning margin-separator"></i> {{ Session::get('info_message') }}
                </div>
            @endif

            @if (Session::has('success_message'))
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <i class="fa fa-check margin-separator"></i> {{ Session::get('success_message') }}
                </div>
            @endif

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">Send Mail</h3>
                        </div>
                        <!-- form start -->
                        <form class="form-horizontal" method="POST" action="{{ url('panel/admin/mail-send/submit') }}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @include('errors.errors-forms')
                            <div class="panel-body">
                                <div class="row" id="addinvoiceItem2">
                                    <div class="panel panel-default panel-assembly">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-sm-8 col-sm-offset-2">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group row">
                                                                <label for="pn" class="col-sm-3 col-form-label">Mail To <i class="text-danger">*</i></label>
                                                                <div class="col-sm-6">
                                                                    <input class="form-control" name="mail_to" id="pn" type="email" required="" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group row">
                                                                <label for="ps" class="col-sm-3 col-form-label">Mail Subject <i class="text-danger">*</i></label>
                                                                <div class="col-sm-6">
                                                                    <input class="form-control" name="mail_subject" id="ps" type="text" required="" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="form-group row">
                                                                <label for="pq" class="col-sm-3 col-form-label">Mail Body <i class="text-danger">*</i></label>
                                                                <div class="col-sm-6">
                                                                    <textarea class="form-control" name="mail_body" id="pq" cols="80" rows="10"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <div class="col-sm-8">
                                    <button type="submit" class="btn btn-success pull-right">Send</button>
                                </div>
                            </div><!-- /.box-footer -->
                        </form>
                    </div>
                </div>
            </div><!-- Your Page Content Here -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
@endsection


@section('javascript')
<script>
    "use strict";
   
</script>
@endsection