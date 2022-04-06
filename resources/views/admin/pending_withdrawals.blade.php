@extends('admin.layout')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h4>
                {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i>
                {{ trans('misc.withdrawals') }} <i class="fa fa-angle-right margin-separator"></i>
                Pending-withdrawals
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
                                <h4>Pending-withdrawals</h4>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive no-padding">
                                <table id="pendingWithdrawal" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Amount</th>
                                            <th>Gateway</th>
                                            <th>Account</th>
                                            <th>{{ trans('admin.date') }}</th>
                                            <th>{{ trans('admin.user') }}</th>
                                            <th>{{ trans('admin.status') }}</th>
                                            <th>Approve</th>
                                            <th>Reject</th>
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
                                {{-- <input type="text" class="form-control" id="reason"> --}}
                                <textarea class="form-control" id="reason" cols="30" rows="10" required></textarea>
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

            $('#pendingWithdrawal').DataTable({
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
                    'url': "{{ url('panel/admin/withdrawals-pending-list') }}",
                    'type': 'POST',
                    'data': {
                        "_token": "{{ csrf_token() }}",
                    }
                },
                'columns': [{
                        data: 'sl'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'gateway'
                    },
                    {
                        data: 'account'
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
                        data: 'reject'
                    }
                ],
            });


            function preloader_ajax() {
                $('.page-loader-wrapper').show();
            }

            $(document).ajaxStop(function(e) {
                $('.page-loader-wrapper').hide();
            });

            $(document).on('click', '#approved', function() {
                preloader_ajax();
                var action_id = $(this).data("id");
                var ajax_url = "{{ url('panel/admin/withdrawals-pending/update') }}";
                $.ajax({
                    url: ajax_url,
                    dataType: 'json',
                    type: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": action_id,
                        "status": 'paid',
                    },
                    success: function(response) {
                        toastr.success(response.message, response.title);
                        $('#pendingWithdrawal').DataTable().ajax.reload(null, false);
                    }
                });
            });

            $(document).on('click', '#rejected', function() {
                var action_id = $(this).data("id");
                $('#reason').val('');
                $('#action_id').val(action_id);
                $('#reasonModal').modal('show');
            });

            $(document).on('click', '#saveReason', function() {
                var ajax_url = "{{ url('panel/admin/withdrawals-pending/update') }}";
                var reason = $('#reason').val();
                var action_id = $('#action_id').val();
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
                        "id": action_id,
                        "status": 'rejected',
                        "reason": reason,
                    },
                    success: function(response) {
                        $('#reasonModal').modal('hide');
                        toastr.warning(response.message, response.title);
                        $('#pendingWithdrawal').DataTable().ajax.reload(null, false);
                    }
                });
            })

        });
    </script>
@endsection
