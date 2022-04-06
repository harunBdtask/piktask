@extends('admin.layout')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h4>
                {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i>
                {{ trans_choice('misc.images_plural', 0) }} <i class="fa fa-angle-right margin-separator"></i>
                Reject-image
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
                            <h3 class="box-title">Reject-image</h3>
                        </div>
                        <!-- form start -->
                        <form class="form-horizontal" method="POST" action="{{ url('panel/admin/pending-images/reject') }}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="token_id" id="token_id" value="<?php echo $data->token_id ?>">
                            @include('errors.errors-forms')
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div id="home" class="tab-pane fade in active">
                                        <div class="panel-body">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">Report Reasons</div>
                                                <div class="panel-body">
                                                    <div class="row" id="addinvoiceItem2">
                                                        <div class="panel panel-default panel-assembly">
                                                            <div class="panel-body">
                                                                <div class="row">
                                                                    <div class="col-sm-8 col-sm-offset-2">
                                                                        <div class="row">
                                                                            <div class="col-sm-12">
                                                                                <div class="form-group row">
                                                                                    <label for="pn" class="col-sm-3 col-form-label">Reason Title <i class="text-danger">*</i></label>
                                                                                    <div class="col-sm-6">
                                                                                        <input class="form-control" name="reason_title[1]" id="pn" type="text" required="" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-sm-12">
                                                                                <div class="form-group row">
                                                                                    <label for="pq" class="col-sm-3 col-form-label">Reason Description <i class="text-danger">*</i></label>
                                                                                    <div class="col-sm-6">
                                                                                        <textarea class="form-control" name="reason_description[1]" id="pq" cols="80" rows="10"></textarea>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
        
        
                                                                    </div>
                                                                    <div class="col-sm-2 text-right">
                                                                        <button class="btn btn-danger mr-auto assemble_delete" type="button" value="delete" onclick="deleteRow(this)"><i class="fa fa-minus-circle"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    <input type="button" id="add-invoice-item" class="btn btn-info color4 color5" name="add-invoice-item" onClick="addInputField('addinvoiceItem2');" value="Add New" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <div class="col-sm-8">
                                    <a href="{{ url('panel/admin/pending-images') }}"
                                        class="btn btn-default">{{ trans('admin.cancel') }}</a>
                                    <button type="submit"
                                        class="btn btn-success pull-right">{{ trans('admin.save') }}</button>
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
    // Counts and limit for invoice
    var count = 2;
    var limits = 500;
    //Add Invoice Field
    function addInputField(divName) {
        if (count == limits) {
            alert("You have reached the limit of adding " + count + " inputs");
        } else {
            var newdiv = document.createElement('div');
            newdiv.classList.add("panel");
            newdiv.classList.add("panel-default");
            newdiv.classList.add("panel-assembly");
            

            var assembly_product_area = "'assembly_product_area_" + count + "'";

            newdiv.innerHTML = '</div><div class="panel-body">'+
                                    '<div class="row">'+
                                        '<div class="col-sm-8 col-sm-offset-2">'+
                                            '<div class="row">'+
                                                '<div class="col-sm-12">'+
                                                    '<div class="form-group row">'+
                                                        '<label for="pn" class="col-sm-3 col-form-label">Reason Title <i class="text-danger">*</i></label>'+
                                                        '<div class="col-sm-6">'+
                                                            '<input class="form-control" name="reason_title[' + count + ']" id="pn" type="text" required="" />'+
                                                        '</div>'+
                                                    '</div>'+
                                                '</div>'+
                                            '</div>'+
                                            '<div class="row">'+
                                                '<div class="col-sm-12">'+
                                                    '<div class="form-group row">'+
                                                        '<label for="pq" class="col-sm-3 col-form-label">Reason Description <i class="text-danger">*</i></label>'+
                                                        '<div class="col-sm-6">'+
                                                            '<textarea class="form-control" name="reason_description[' + count + ']" id="pq" cols="80" rows="10"></textarea>'+
                                                        '</div>'+
                                                    '</div>'+
                                                '</div>'+
                                            '</div>'+

                                        '</div>'+
                                        '<div class="col-sm-2 text-right"><button class="btn btn-danger mr-auto assemble_delete" type="button" value="' + ('delete') + '" onclick="deleteRow(this)"><i class="fa fa-minus-circle"></i></button></div>'+
                                    '</div>';



            document.getElementById(divName).appendChild(newdiv);
            count++;
            
            $('.default_box').on('click', function() {
                var sl = $(this).parent().parent().find(".autocomplete_hidden_value").val();
                $(this).val(sl);
                console.log(sl);
            });
        }
    }
    //Delete a row from invoice table
    function deleteRow(t) {
        var a = $("#addinvoiceItem2 > div").length;
        if (1 == a) {
            alert("There only one row you can't delete.");
            return false;
        } else {
            var e = t.parentNode.parentNode.parentNode.parentNode;
            e.remove(t);
        }
    }

    $('.default_box').on('click', function() {
        var sl = $(this).parent().parent().find(".autocomplete_hidden_value").val();
        $(this).val(sl);
    });
</script>
@endsection