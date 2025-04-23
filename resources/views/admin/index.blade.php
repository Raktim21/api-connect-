@extends('admin.layouts.app')
@section('title', 'Overview')

@section('css')
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('admin_dashboard/assets/css/vendors/chartist.css') }}"> --}}
<link rel="stylesheet" type="text/css" href="{{ asset('admin_dashboard/assets/css/vendors/datatables.css') }}">
<style>
    .sidebar-import {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        transition: right 0.3s ease;
        z-index: 1050;
        padding: 20px;
        overflow-y: auto;
    }

    .sidebar-import.open {
        right: 0;
    }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        display: none;
    }

    .sidebar-overlay.open {
        display: block;
    }

    #importLoader {
        border: 4px solid #f3f3f3;
        border-radius: 50%;
        border-top: 4px solid transparent;
        width: 20px;
        height: 20px;
        margin-right: 10px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
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
                    <div class="flex-grow-1 text-end d-flex justify-content-end">
                        @if (Auth::user()->role === 1)
                            <button id="openImportSidebar" class="btn btn-success me-2">
                                <i class="fa fa-upload me-2"></i>Import
                            </button>
                        @endif
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

<!-- Import Sidebar -->
@if (Auth::user()->role === 1)
    <div id="importSidebar" class="sidebar-import">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Import Data</h5>
            <button id="closeImportSidebar" class="btn btn-sm btn-circle btn-outline-secondary">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="mb-3">
            <label for="importFile" class="form-label">Select Excel File</label>
            <input class="form-control" type="file" id="importFile" accept=".xlsx,.xls,.csv" required>
            <div class="form-text">Please upload an Excel file with Importer CRs in the first column</div>
            <p id="importError" class="text-danger mt-2" style="display: none;"></p>
        </div>

        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary d-flex justify-content-center" id="downloadBtn">
                <div class="d-flex align-items-center justify-content-center">
                    <span id="progressText" class="d-flex align-items-center justify-content-center"><i class="fa fa-download me-2"></i>Download the Excel</span>
                </div>
            </button>

        </div>
    </div>

<div id="sidebarOverlay" class="sidebar-overlay"></div>
@endif

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

<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<!-- handle export excel -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openBtn = document.getElementById('openImportSidebar');
        const closeBtn = document.getElementById('closeImportSidebar');
        const sidebar = document.getElementById('importSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const fileInput = document.getElementById('importFile');
        const downloadBtn = document.getElementById('downloadBtn');
        const stopBtn = document.createElement('button');
        const errorMsg = document.getElementById('importError');
        const progressText = document.getElementById('progressText');

        let stopRequested = false;

        stopBtn.innerText = 'Stop';
        stopBtn.className = 'btn btn-danger mt-2';
        stopBtn.style.display = 'none';
        downloadBtn.parentElement.appendChild(stopBtn);

        stopBtn.addEventListener('click', () => {
            stopRequested = true;
            stopBtn.innerText = 'Stopping...';
            setTimeout(() => {
                stopBtn.style.display = 'none';
                stopBtn.innerText = 'Stop';
                downloadBtn.disabled = false;
                progressText.innerHTML = `<i class="fa fa-download me-2"></i>Download the Excel`;
            }, 2000);
        });

        openBtn?.addEventListener('click', () => {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        });

        closeBtn?.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });

        downloadBtn?.addEventListener('click', async () => {
            const file = fileInput.files[0];
            if (!file) {
                showError('Please select a file.');
                return;
            }

            stopRequested = false;
            downloadBtn.disabled = true;
            stopBtn.style.display = 'block';
            progressText.innerHTML = `<div id="importLoader"></div> Processing...`;
            errorMsg.style.display = 'none';
            errorMsg.innerText = '';

            const reader = new FileReader();
            reader.onload = async (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {
                        type: 'array'
                    });
                    const sheet = workbook.Sheets[workbook.SheetNames[0]];
                    const rows = XLSX.utils.sheet_to_json(sheet, {
                        header: 1
                    });

                    const importerIDs = rows.slice(1).map(row => row[0]).filter(Boolean);

                    if (!importerIDs.length) {
                        showError('No Importer IDs found.');
                        resetButton();
                        return;
                    }

                    const allData = [];

                    for (let i = 0; i < importerIDs.length; i += 10) {
                        if (stopRequested) {
                            resetButton();
                            return;
                        }

                        const chunk = importerIDs.slice(i, i + 10);
                        progressText.innerHTML = `<div id="importLoader"></div> Processing (${i + 1}/${importerIDs.length})...`;

                        const res = await fetch("{{ route('admin.importer_list.api') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                importer_ids: chunk
                            })
                        });

                        if (!res.ok) throw new Error('Request failed');

                        const data = await res.json();
                        allData.push(...data);

                        await new Promise(resolve => setTimeout(resolve, 300)); // delay 300ms
                    }

                    if (!allData.length) {
                        showError('No matching data found.');
                        resetButton();
                        return;
                    }

                    const exportSheet = XLSX.utils.json_to_sheet(allData);
                    const exportWB = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(exportWB, exportSheet, 'Importers');
                    XLSX.writeFile(exportWB, 'imported_data.xlsx');

                    resetButton(true);

                    setTimeout(() => {
                        downloadBtn.disabled = false;
                        stopBtn.style.display = 'none';
                        progressText.innerHTML = `<i class="fa fa-download me-2"></i>Download the Excel`;
                        stopBtn.innerText = 'Stop';
                        stopBtn.style.display = 'none';
                        errorMsg.style.display = 'none';
                        errorMsg.innerText = '';
                        fileInput.value = '';

                    }, 2000);
                } catch (err) {
                    console.error(err);
                    showError('Something went wrong during processing.');
                    resetButton();
                }
            };

            reader.readAsArrayBuffer(file);
        });

        function resetButton(success = false) {
            stopRequested = false;
            stopBtn.style.display = 'none';
            downloadBtn.disabled = false;
            progressText.innerHTML = success ?
                `<i class="fa fa-check-circle me-2"></i> Done!` :
                `<i class="fa fa-download me-2"></i> Download the Excel`;
        }

        function showError(msg) {
            errorMsg.innerText = msg;
            errorMsg.style.display = 'block';
        }
    });
</script>




@endsection
