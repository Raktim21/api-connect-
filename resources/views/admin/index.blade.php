@extends('admin.layouts.app')
@section('title', 'Overview')

@section('css')
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('admin_dashboard/assets/css/vendors/chartist.css') }}"> --}}
<link rel="stylesheet" type="text/css" href="{{ asset('admin_dashboard/assets/css/vendors/datatables.css') }}">
@endsection

@section('admin.dashboard','active')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <div class="d-md-flex d-sm-block">
                    {{-- <div class="form-group d-flex mb-0">
                        <h5>Users</h5>
                    </div> --}}
                    <div class="flex-grow-1 text-end">
                        <form action="{{ route('admin.dashboard') }}" method="GET">
                            <div class="btn-group">
                                <input class="form-control" type="text" name="search" placeholder="Search" style="boarder:1px solid black" value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> 
            <div class="table-responsive table-hover">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الرقم الاستدلالي</th>
                            <th>الرقم الاستدلالي الفرعي</th>
                            <th>اسم المستورد</th>
                            <th>رقم المستورد</th>
                            <th>تاريخ الانتهاء</th>
                            <th>فرعي</th>
                            <th>نوع المستورد</th>
                            <th>الرسالة</th>
                  
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($final_datas as $final_data)         
                                <tr>
                                    <td>{{ $final_data['busid'] }}</td>
                                    <td>{{ $final_data['busidbrnch'] }}</td>
                                    <td>{{ $final_data['arbcimprname'] }}</td>
                                    <td>{{ $final_data['hqimprnbr'] }}</td>
                                    <td>{{ Carbon\Carbon::parse($final_data['busidexprddt'])->format('d-m-Y h:i a' ) }}</td>
                                    <td>{{ $final_data['commrgstrtntype'] }}</td>
                                    <td>{{ $final_data['imprexprtype'] }}</td>
                                    <td>{{ $final_data['msg'] }}</td>
                                </tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


@section('js')
{{-- <script src="{{ asset('admin_dashboard/assets/js/config.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/chartist/chartist.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/chartist/chartist-plugin-tooltip.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/knob/knob.min.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/knob/knob-chart.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/apex-chart/apex-chart.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/apex-chart/stock-prices.js') }}"></script>

<script src="{{ asset('admin_dashboard/assets/js/chart/chartjs/chart.min.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart/chartjs/chart.custom.js') }}"></script>

<script src="{{ asset('admin_dashboard/assets/js/dashboard/default.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/dashboard/dashboard_2.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/chart-widget.js') }}"></script> --}}

<script src="{{ asset('admin_dashboard/assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin_dashboard/assets/js/datatable/datatables/datatable.custom.js') }}"></script>




@endsection
