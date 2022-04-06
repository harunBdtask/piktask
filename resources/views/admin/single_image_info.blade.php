@extends('admin.layout')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h4>
                {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i>
                {{ trans_choice('misc.images_plural', 0) }} <i class="fa fa-angle-right margin-separator"></i>
                Pending-images
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
                            <h3 class="box-title">{{ trans('admin.detail') }}</h3>
                            <?php
                            if (!empty($data)) {
                                $data2 = DB::table('uploaded_files')
                                ->where('token_id', $data->token_id)
                                ->get();
                            }else{
                                session(['warning_message'=>'Rejected']);
                                header("Location: " . URL::to('panel/admin/pending-images'), true, 302);
                                exit();
                            }
                            
                            // print_r($data2);
                            ?>
                        </div>
                        <div class="form-horizontal">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="token_id" id="token_id" value="<?php echo $data->token_id; ?>">
                            <input type="hidden" name="uploaded_file_id" id="uploaded_file_id" value="<?php echo $data->uploaded_file_id; ?>">
                            @include('errors.errors-forms')
                            <div class="box-body">
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-6">
                                        <h3><?php echo $data->title; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-6">
                                        <img src="<?php echo 'https://piktask.sgp1.digitaloceanspaces.com/images/' . $data->original_file; ?>" alt="img" height="200px" width="200px">
                                    </div>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-6">
                                        <h4>It contains the following files:</h4>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $num = 0;
                            foreach ($data2 as $item) {
                                $num++; ?>
                                <div class="box-body">
                                    <div class="form-group">
                                        <div class="col-sm-2"></div>
                                        <div class="col-sm-6">
                                            <?php echo $num.'. '.$item->original_file?>
                                            <a href="<?php echo 'https://piktask.sgp1.digitaloceanspaces.com/images/' . $item->original_file; ?>" target="_blank"
                                                rel="noopener noreferrer" class="btn btn-primary"><i class="fa fa-download"></i> </a>
                                            <?php strtoupper($item->extension) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <!-- Start Box Body -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">{{ trans('admin.status') }}</label>
                                    <div class="col-sm-2">
                                        <select name="status" id="status" class="form-control">
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <div class="col-sm-5">
                                    <a href="{{ url('panel/admin/pending-images') }}"
                                        class="btn btn-default">{{ trans('admin.cancel') }}</a>
                                    <button id="save" class="btn btn-success pull-right">{{ trans('admin.save') }}</button>
                                </div>
                            </div><!-- /.box-footer -->
                        </div>
                    </div>
                </div>
            </div><!-- Your Page Content Here -->
            <!-- Modal -->
            <div class="modal fade" id="reasonModal" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Info</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action_id" id="action_id">
                            <div class="form-group">
                                <label for="reason" class="col-form-label">Reason:</label>
                                <select name="reason[]" class="multiple_select" multiple="multiple" style="width: 100%" id="reason">
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                    <option value="4">Four</option>
                                    <option value="5">Five</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" id="saveReason" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal End -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
@endsection



@section('javascript')
    <script type="text/javascript">


        $(document).ready(function() {
            $('.multiple_select').select2();
            // preload get data
            function preloader_ajax() {
                $('.page-loader-wrapper').show();
            }

            $(document).ajaxStop(function(e) {
                $('.page-loader-wrapper').hide();
            });

            $('#save').on('click', function(e) {
                var status = $('#status').val();
                if (status == 'rejected') {
                    var action_id = "{{ $data->token_id }}";
                    $('#reason').val('');
                    $('#action_id').val(action_id);
                    $('#reasonModal').modal('show');
                    // console.log(status);
                    // return false;
                }else{
                    preloader_ajax();
                    var ajax_url = "{{ url('panel/admin/pending-images/update') }}";
                    $.ajax({
                        url: ajax_url,
                        dataType: 'json',
                        type: 'post',
                        data: {
                            "_token"    : "{{ csrf_token() }}",
                            "token_id"  : "{{ $data->token_id }}",
                            "uploaded_file_id"  : "{{ $data->uploaded_file_id }}",
                            "status"    : $('#status').val(),
                        },
                        success: function(response) {
                            if (response.status == 'rejected') {
                                top.location.href=response.url;//redirection
                            } else {
                                toastr.success(
                                    response.message,
                                    'success', {
                                        timeOut: 1000,
                                        fadeOut: 1000,
                                        onHidden: function() {
                                            top.location.href = response.url;
                                        }
                                    }
                                );
                            }
                        }
                    });
                }
            });
            $(document).on('click', '#saveReason', function() {
                var ajax_url = "{{ url('panel/admin/pending-images/reject') }}";
                var action_id = $('#action_id').val();
                var reason = $('#reason').val();
                // console.log(action_id);
                // return false;
                if (reason == '') {
                    alert('Please Set Reason');
                    return false;
                }
                preloader_ajax();
                $.ajax({
                    url: ajax_url,
                    dataType: 'json',
                    type: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "token_id": action_id,
                        "status": 'rejected',
                        "reason_title": reason,
                    },
                    success: function(response) {
                        $('#reasonModal').modal('hide');
                        toastr.success(
                            response.message,
                            'success', {
                                timeOut: 1000,
                                fadeOut: 1000,
                                onHidden: function() {
                                    top.location.href = response.url;
                                }
                            }
                        );
                    }
                });
            })
        });
    </script>
@endsection

