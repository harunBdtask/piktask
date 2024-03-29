@extends('admin.layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h4>
           {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i> {{ trans('misc.withdrawals') }} ({{$data->total()}})
          </h4>

        </section>

        <!-- Main content -->
        <section class="content">

        	@if(Session::has('success_message'))
		    <div class="alert alert-success">
		    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">×</span>
								</button>
		      <i class="fa fa-check margin-separator"></i>  {{ Session::get('success_message') }}
		    </div>
		@endif

        	<div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">
                  		{{ trans('misc.withdrawals') }}
                  	</h3>
                </div><!-- /.box-header -->

                <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
               <tbody>

               	@if( $data->total() !=  0 && $data->count() != 0 )
                   <tr>
                      <th class="active">ID</th>
			   		  <th class="active">{{ trans('admin.user') }}</th>
			          <th class="active">{{ trans('admin.amount') }}</th>
			          <th class="active">{{ trans('misc.method') }}</th>
			          <th class="active">{{ trans('admin.status') }}</th>
			          <th class="active">{{ trans('admin.date') }}</th>
			          <th class="active">{{ trans('admin.actions') }}</th>
                    </tr><!-- /.TR -->

@foreach( $data as $withdrawal )

                    <tr>
                      <td>{{ $withdrawal->id }}</td>
                      <td>{{ $withdrawal->user()->username }}</td>
                      <td>{{ \App\Helper::amountFormatDecimal($withdrawal->amount) }}</td>
                      <td>{{ $withdrawal->gateway }}</td>
                      <td>
                        
                      	@if( $withdrawal->status == 'paid' )
                      	<span class="label label-success">{{trans('misc.paid')}}</span>
                        @elseif( $withdrawal->status == 'rejected' )
                        <span class="label label-danger">Rejected</span>
                      	@else
                      	<span class="label label-warning">{{trans('misc.pending_to_pay')}}</span>
                      	@endif
                      </td>
                      <td>{{ date('d M, Y', strtotime($withdrawal->date)) }}</td>
                      <td>
                        @if( $withdrawal->status == 'paid' )
                          <a href="{{ url('panel/admin/withdrawal',$withdrawal->id) }}" class="btn btn-xs btn-success" title="{{trans('admin.view')}}">
                            {{trans('admin.view')}}
                          </a>
                      	@endif
                      </td>

                    </tr><!-- /.TR -->
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
