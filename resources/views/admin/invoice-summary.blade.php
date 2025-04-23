@extends('admin.layouts.app')
@section('title', 'Invoice Summary')

@section('css')
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('admin_dashboard/assets/css/vendors/chartist.css') }}"> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('admin_dashboard/assets/css/vendors/datatables.css') }}">
    <style>
        .loading-screen {
            width: 100%;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            border-radius: 10px;
        }

        .table-wrapper {
            position: relative;
        }

        .in-progress {
            position: absolute;
            top: 1px;
            left: 50%;
            transform: translateX(-50%);
            background: #e3cfcf;
            padding: 8px;
            z-index: 999999;
            display: none;
            border: 2px solid transparent;
            border-radius: 5px;
            animation: animateBorder 1.5s infinite;
        }

        @keyframes animateBorder {
            0% {
                border-color: transparent;
            }

            25% {
                border-color: #e8ff50;
            }

            50% {
                border-color: #ffff47;
            }

            75% {
                border-color: #51ff00;
            }

            100% {
                border-color: transparent;
            }
        }
    </style>
@endsection

@section('admin.importer.invoice.summary', 'active')

@section('content')
    <div class="row">

        @if (Auth::user()->role  == '1')
        <div class="col-xl-12 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-light">
                    <h3>Log</h3>
                </div>
                <div class="card-body">

                    <table class="table">
                        <thead>
                            <th>User</th>
                            <th>Count</th>
                        </thead>
                        <tbody id="body-parse-log" >
                            @if ($parse_log)
                                @foreach ($parse_log as $value)
                                    <tr>
                                        <td>{{$value->name}}</td>
                                        <td>{{$value->total_count}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="2" >No record found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12" style="display: flex;justify-content: end; margin-bottom: 20px; gap: 20px;">
                            <button id="download-btn" onclick='exportTableToCSV();' class="btn btn-warning">Download</button>
                            <form>
                                <input type="file" name="file_1" id="file_1" style="display: none;"
                                    accept="application/pdf" />
                                <button type="button" id="pdf-upload-btn" class="btn btn-primary"
                                    onclick="document.getElementById('file_1').click()">Upload</button>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive table-wrapper">
                        <table class="display table" id="data-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>HS Code</th>
                                    <th>Country of Origin</th>
                                    <th>Currency</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>

                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="8">

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="in-progress">

                        </div>
                    </div>

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

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table_body_id = '#table-body';

        $('#file_1').on('change', function() {
            addLoaderInTable(table_body_id);
            var file = this.files[0];
            var filePageNo = 1;

            if (file) {
                processFile(file, filePageNo);
            }
        });

        function processFile(file, filePageNo) {
            var formData = new FormData();
            formData.append('file', file);
            formData.append('page_no', filePageNo);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "{{ url('extract-text-from-pdf') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    removeLoaderInTable();
                    populateTable(response.result);

                    var currentPageNo = parseInt(response.current_page_no);
                    var totalPages = parseInt(response.total_pages);
                    if (currentPageNo < totalPages) {
                        showProgress(
                            `PDF is being processed page ${currentPageNo}/${totalPages} . Please be patient...`
                            );
                        filePageNo = currentPageNo + 1;
                        processFile(file, filePageNo, totalPages);
                    } else {
                        hideProgress();
                        // sortTableByHsCode();
                        // groupTableRowsByHsCode();
                        addPdfParseLog(file.name, 'success', totalPages);
                    }
                },
                error: function(xhr) {
                    alert("Error uploading file, Please try again!");
                    removeLoaderInTable();
                },
                complete: function() {

                }
            });
        }

        function populateTable(data) {
            let tableBody = document.getElementById("table-body");
            data.forEach(item => {
                let tr = `
            <tr data-hs-code="${item.HS_Code}" >
                <td class="td-itam" >${item.Item}</td>
                <td class="td-hs_code" >${item.HS_Code}</td>
                <td class="td-Country_of_Origin" >${item.Country_of_Origin}</td>
                <td class="td-Currency" >${item.Currency ?? '-'}</td>
                <td class="td-Quantity" >${item.Quantity}</td>
                <td class="td-Unit_Price" >${item.Unit_Price ?? 0.00}</td>
                <td class="td-Total_Price" >${item.Total_Price ?? 0.00}</td>
            </tr>
        `;
                tableBody.innerHTML += tr;
            });

        }

        function addDownloadBtn() {
            $(table_body_id).append(`
        <tr>
            <td colspan="8" ><button id="download-btn" onclick='downloadContent();' class="btn btn-primary" >Download</button></td>
        </tr>
    `);
        }

    function exportTableToCSV() {
        var csv = [];
        var rows = document.querySelectorAll("#data-table tr");
    
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");
    
            for (var j = 0; j < cols.length; j++) {

                var data = cols[j].innerText.replace(/"/g, '""');
                if (data.includes(",") || data.includes('"') || data.includes("\n")) {
                    data = '"' + data + '"';
                }
                row.push(data);
            }
    
            csv.push(row.join(","));
        }
    
        // Create CSV file and download
        var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        var downloadLink = document.createElement("a");
        downloadLink.download = "invoice_summary.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }


        function downloadContent() {
            let csv = [];
            $("#data-table tr").each(function() {
                let row = [];
                $(this).find("th, td").each(function() {
                    row.push($(this).text());
                });
                csv.push(row.join(","));
            });

            let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "table_data.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        function addLoaderInTable(table_body_id) {
            var tr = `<tr class="loader-row" >
                <td colspan="8">
                    <div class="loading-screen text-center text-dark">
                        Extracting Details...
                    </div>
                </td>
            </tr>`;
            $(table_body_id).html(tr);
            $('#pdf-upload-btn').prop('disabled', true);
        }

        function removeLoaderInTable() {
            $('#pdf-upload-btn').prop('disabled', false);
            $('.loader-row').hide();
            $('#file_1').val('');
        }


        function sortTableByHsCode() {
            showProgress("Sorting data by HS Code...");
            var tbody = $("#table-body");
            var rows = tbody.find("tr").toArray().sort(function(a, b) {
                var hsA = $(a).data("hs-code") || "";
                var hsB = $(b).data("hs-code") || "";
                hsA = hsA.toString().substring(0, 6);
                hsB = hsB.toString().substring(0, 6);
                return hsA.localeCompare(hsB);
            });
            tbody.empty().append(rows);
            hideProgress();
        }

        function showProgress(text) {
            $('.in-progress').html(text);
            $('.in-progress').css('display', 'flex');
        }

        function hideProgress() {
            $('.in-progress').css('display', 'none');
        }

        function highlightHsCodeGroups() {
            var tbody = $("#table-body");
            var rows = tbody.find("tr");

            rows.css({
                "border-top": "",
                "border-bottom": ""
            });

            var lastHsCode = null;
            var firstRow = null;

            rows.each(function(index) {
                var currentHsCode = $(this).data("hs-code");

                if (currentHsCode !== lastHsCode) {
                    if (firstRow) {
                        $(firstRow).css("border-top", "4px solid #ff0000");
                        $(rows[index - 1]).css("border-bottom", "4px solid #ff0000");
                    }
                    firstRow = this;
                }
                lastHsCode = currentHsCode;
            });

            if (firstRow) {
                $(firstRow).css("border-top", "4px solid #ff0000");
                $(rows[rows.length - 1]).css("border-bottom", "4px solid #ff0000");
            }
        }

        function groupTableRowsByHsCode() {
            showProgress(`Grouping the data by Hs Code...`);
            let tbody = $("#table-body");
            let rows = tbody.find("tr");
            let groupedData = {};

            rows.each(function() {
           
                if ($(this).hasClass("loader-row")) {
                    return;
                }
                // let hsCode = $(this).data("hs-code");
                let hsCode = ($(this).data("hs-code") + "").trim().replace(/\./g, '');
                let item = $(this).find(".td-itam").text().trim();
                let country = $(this).find(".td-Country_of_Origin").text().trim();
                let currency = $(this).find(".td-Currency").text().trim();

                let quantity = parseFloat($(this).find(".td-Quantity").text().trim().replace(/,/g, '')) || 0;
                let unitPrice = parseFloat($(this).find(".td-Unit_Price").text().trim().replace(/,/g, '')) || 0;
                let totalPrice = parseFloat($(this).find(".td-Total_Price").text().trim().replace(/,/g, '')) || 0;


                if (!groupedData[hsCode]) {
                    groupedData[hsCode] = {
                        item: item,
                        hsCode: hsCode,
                        country: country,
                        currency: currency !== "-" ? currency : "",
                        quantity: 0,
                        unitPrice: 0,
                        totalPrice: 0,
                    };
                }

                groupedData[hsCode].quantity += quantity;
                groupedData[hsCode].unitPrice += unitPrice;
                groupedData[hsCode].totalPrice += totalPrice;

                if (!groupedData[hsCode].currency && currency !== "-") {
                    groupedData[hsCode].currency = currency;
                }
            });
  
            tbody.empty();
            $.each(groupedData, function(hsCode, data) {
                let row = `<tr data-hs-code="${hsCode}">
                <td class="td-itam">${data.item}</td>
                <td class="td-hs_code">${data.hsCode}</td>
                <td class="td-Country_of_Origin">${data.country}</td>
                <td class="td-Currency">${data.currency || "-"}</td>
                <td class="td-Quantity">${data.quantity}</td>
                <td class="td-Unit_Price">${data.unitPrice.toLocaleString()}</td>
                <td class="td-Total_Price">${data.totalPrice.toLocaleString()}</td>
            </tr>`;
                if (hsCode && data.item) {
                    tbody.append(row);
                }
            });
            
            hideProgress();
        }

        function addPdfParseLog(file_name, status, total_page) {

            $.ajax({
                type: "POST",
                url: "{{ url('add-pdf-parse-log') }}",
                data: {
                    file_name: file_name,
                    status: status,
                    total_page: total_page,
                },
                success: function(response) {
                    if(response.success){
                        var log = response.parse_log;
                        var tr = '';
                        log.forEach(function(value, index) {
                            tr += `<tr> <td>${value.name}</td> <td>${value.total_count}</td> </tr>`;
                        })
                        $('#body-parse-log').html(tr);
                    }
                }
            });
        }
    </script>

@endsection
