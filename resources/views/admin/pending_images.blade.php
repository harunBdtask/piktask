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

            @if (Session::has('error_message'))
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <i class="fa fa-warning margin-separator"></i> {{ Session::get('error_message') }}
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

            @if (Session::has('warning_message'))
                <div class="alert alert-warning">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <i class="fa fa-warning margin-separator"></i> {{ Session::get('warning_message') }}
                </div>
            @endif

            @if (Session::has('info_message'))
                <div class="alert alert-info">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <i class="fa fa-warning margin-separator"></i> {{ Session::get('info_message') }}
                </div>
            @endif

            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-bd lobidrag">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h4>Pending-images</h4>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive no-padding">
                                <table id="pendingImage" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>{{ trans('admin.title') }}</th>
                                            <th>Extention</th>
                                            <th>Size</th>
                                            <th>{{ trans('admin.date') }}</th>
                                            <th>{{ trans('misc.uploaded_by') }}</th>
                                            <th>{{ trans('admin.status') }}</th>
                                            <th>Approve</th>
                                            <th>Reject</th>
                                            <th>{{ trans('admin.detail') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div><!-- /.box-body -->
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

            $('#pendingImage').DataTable({
                responsive: true,
                lengthChange: true,
                "paging": true,
                "aaSorting": false,
                searching: false,
                "columnDefs": [{
                    "bSortable": false,
                }, ],
                dom: "<'row'<'col-md-4'l><'col-md-4'B><'col-md-4'f>>rt<'bottom'<'row'<'col-md-6'i><'col-md-6'p>>><'clear'>",
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': "{{ url('panel/admin/pending-images-list') }}",
                    'type': 'POST',
                    'data': {
                        "_token": "{{ csrf_token() }}",
                        }
                },
                'columns': [{
                        data: 'sl'
                    },
                    {
                        data: 'image'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'extention'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'uploaded_by'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'approve'
                    },
                    {
                        data: 'rejected'
                    },
                    {
                        data: 'details'
                    }
                ],
            });


            // preload get data
            function preloader_ajax() {
                $('.page-loader-wrapper').show();
            }

            $(document).ajaxStop(function(e) {
                $('.page-loader-wrapper').hide();
            });

            $(document).on('click', '#rejected', function() {
                var action_id = $(this).data("id");
                $('#reason').val('');
                $('#action_id').val(action_id);
                $('#reasonModal').modal('show');
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
                        toastr.warning(response.message, response.status);
                        $('#pendingImage').DataTable().ajax.reload(null, false);
                    }
                });
            })

            $(document).on('click', '#approved', function() {
                preloader_ajax();
                var a = $(this).data("id");
                var ajax_url = "{{ url('panel/admin/pending-images/update') }}";
                $.ajax({
                    url: ajax_url,
                    dataType: 'json',
                    type: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "token_id": a,
                        "status": $('#status').val(),
                    },
                    success: function(response) {
                        if (response.status == 'rejected') {
                            // toastr.warning(response.message);
                            location.reload();
                            toastr.success(response.message, {
                                timeOut: 9500
                            });
                            // top.location.href="{{ url('panel/admin/pending-images') }}";//redirection
                        } else {
                            toastr.success(response.message, 'success');
                            $('#pendingImage').DataTable().ajax.reload(null, false);
                            // toastr.success(
                            //     response.message,
                            //     'success', {
                            //         timeOut: 1000,
                            //         fadeOut: 1000,
                            //         onHidden: function() {
                            //             location.reload();
                            //         }
                            //     }
                            // );
                            // toastr.success(response.message);
                            // location.reload();
                        }
                    }
                });
            });
            // $('#test').on('click', function(e) {
            //     preloader_ajax();
            // });
        
        });
    </script>
@endsection
