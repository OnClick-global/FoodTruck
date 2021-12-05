@extends('layouts.admin.app')

@section('title','Add new Register Coupon')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i
                            class="tio-add-circle-outlined"></i> {{__('messages.add')}} {{__('messages.new')}} {{__('messages.coupon')}}
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.register-coupons.store')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="exampleFormControlInput1">{{__('messages.title')}}</label>
                                        <input type="text" name="title" class="form-control"
                                               placeholder="{{__('messages.new_coupon')}}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4     col-6">
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="exampleFormControlInput1">{{__('messages.code')}}</label>
                                        <input type="text" name="code" class="form-control"
                                               placeholder="{{\Illuminate\Support\Str::random(8)}}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-6" hidden>
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="discount_annual">{{__('messages.discount_annual')}}</label>
                                        <input type="number" max="100" name="discount_annual" id="discount_annual" value="100"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3 col-6" hidden>
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="discount_percentage">{{__('messages.discount_percentage')}}</label>
                                        <input type="number" name="discount_percentage" id="discount_percentage" value="100"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{__('messages.submit')}}</button>
                        </form>
                    </div>
                </div>

            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('messages.coupon')}} {{__('messages.list')}}<span class="badge badge-soft-dark ml-2"
                                                                                   id="itemCount">{{$coupons->total()}}</span>
                        </h5>
                        <form id="dataSearch">
                        @csrf
                        <!-- Search -->
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch" type="search" name="search" class="form-control"
                                       placeholder="{{__('messages.search_here')}}"
                                       aria-label="{{__('messages.search_here')}}">
                            </div>
                            <!-- End Search -->
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom" id="table-div">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,

                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging":false,
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th>{{__('messages.#')}}</th>
                                <th>{{__('messages.title')}}</th>
                                <th>{{__('messages.code')}}</th>
                                <th hidden>{{__('messages.discount_annual')}}</th>
                                <th hidden>{{__('messages.discount_percentage')}}</th>
                                <th>{{__('messages.action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($coupons as $key=>$coupon)
                                <tr>
                                    <td>{{$key+$coupons->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{$coupon['title']}}
                                    </span>
                                    </td>
                                    <td>{{$coupon['code']}}</td>
                                    <td hidden >{{$coupon['discount_annual']}}</td>
                                    <td hidden >{{$coupon['discount_percentage']}}</td>

                                    <td>
                                        @if($coupon['expire'] == 0)
                                            <a class="btn btn-sm btn-white"
                                               href="{{route('admin.register-coupons.update',[$coupon['id']])}}"
                                               title="{{__('messages.edit')}} {{__('messages.coupon')}}"><i
                                                    class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn-white" href="javascript:"
                                               onclick="form_alert('coupon-{{$coupon['id']}}','Want to delete this coupon ?')"
                                               title="{{__('messages.delete')}} {{__('messages.coupon')}}"><i
                                                    class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.register-coupons.delete',[$coupon['id']])}}"
                                                  method="post" id="coupon-{{$coupon['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        @else
                                            <h3 class="text-danger"> Expired </h3>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <table>
                            <tfoot>
                            {!! $coupons->links() !!}
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script>

        $('#dataSearch').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.register-coupons.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#table-div').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
