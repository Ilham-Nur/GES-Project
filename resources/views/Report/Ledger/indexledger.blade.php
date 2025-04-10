@extends('layout.main')

@section('title', 'Report | Ledger')

@section('main')
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid #d1d3e2;
            border-radius: 0.25rem;
            padding: 6px 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 27px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
    </style>

    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ledger</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item active" aria-current="page">Ledger</li>
            </ol>
        </div>
        <div class="modal fade" id="modalFilterTanggal" tabindex="-1" role="dialog"
            aria-labelledby="modalFilterTanggalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFilterTanggalTitle">Filter Tanggal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="mt-3">
                                    <label for="Tanggal" class="form-label fw-bold">Pilih Tanggal:</label>
                                    <div class="d-flex align-items-center">
                                        <input type="date" id="startDate" class="form-control"
                                            placeholder="Pilih tanggal mulai" style="width: 200px;">
                                        <span class="mx-2">sampai</span>
                                        <input type="date" id="endDate" class="form-control"
                                            placeholder="Pilih tanggal akhir" style="width: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                        <button type="button" id="saveFilterTanggal" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex mb-2 mr-3 float-right">
                            <!-- <a class="btn btn-success mr-1" style="color:white;" id="Print"><span class="pr-2"><i
                                        class="fas fa-solid fa-print mr-1"></i></span>Print</a> -->
                            <button class="btn btn-primary mr-2" id="btnExportLedger">Export Pdf</button>
                            <button class="btn btn-success mr-2" id="exportBtn">Export Excel</button>
                        </div>
                        <div class="d-flex mb-4 mr-3 float-left">
                            <button class="btn btn-primary ml-2 mr-2" id="filterTanggal">Filter Tanggal</button>
                            <select class="form-control select2multiple" id="filterCode" multiple="multiple"
                                style="width: 600px;">
                                @foreach ($listCode as $Code)
                                    <option value="{{ $Code->id }}">{{ $Code->code_account_id }} - {{ $Code->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary ml-2" id="btnResetDefault"
                                onclick="window.location.reload()">
                                Reset
                            </button>
                        </div>

                        <div id="containerledger" class="table-responsive px-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            const loadSpin = `<div class="d-flex justify-content-center align-items-center mt-5">
            <div class="spinner-border d-flex justify-content-center align-items-center text-primary" role="status"></div>
        </div> `;

            const getLedger = () => {
                const txtSearch = $('#txSearch').val();
                const filterStatus = $('#filterStatus').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                const filterCode = $('#filterCode').val();

                $.ajax({
                        url: "{{ route('getLedger') }}",
                        method: "GET",
                        data: {
                            txSearch: txtSearch,
                            status: filterStatus,
                            startDate: startDate,
                            endDate: endDate,
                            filterCode: filterCode,
                        },
                        beforeSend: () => {
                            $('#containerledger').html(loadSpin)
                        }
                    })
                    .done(res => {
                        $('#containerledger').html(res)

                    })
            }

            getLedger();

            $('#filterCode').change(function() {
                getLedger();
            });


            $('.select2multiple').select2({
                placeholder: "Pilih Akun",
                allowClear: true
            });


            flatpickr("#startDate", {
                dateFormat: "d M Y",
                onChange: function(selectedDates, dateStr, instance) {

                    $("#endDate").flatpickr({
                        dateFormat: "d M Y",
                        minDate: dateStr
                    });
                }
            });

            flatpickr("#endDate", {
                dateFormat: "d MM Y",
                onChange: function(selectedDates, dateStr, instance) {
                    var startDate = new Date($('#startDate').val());
                    var endDate = new Date(dateStr);
                    if (endDate < startDate) {
                        showwMassage(error,
                            "Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.");
                        $('#endDate').val('');
                    }
                }
            });

            $(document).on('click', '#filterTanggal', function(e) {
                $('#modalFilterTanggal').modal('show');
            });

            $('#saveFilterTanggal').click(function() {
                getLedger();
                $('#modalFilterTanggal').modal('hide');
            });

            $(document).on('click', '#btnExportLedger', function(e) {
                e.preventDefault();

                let startDate = $('#startDate').val();
                let endDate = $('#endDate').val();
                let filterCode = $('#filterCode').val() || []; // Jika kosong, tetap array
                let filterCodeParam = filterCode.map(encodeURIComponent).join('&code_account_id[]=');

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Buat URL dengan parameter untuk request langsung
                let url = "{{ route('exportLedgerPdf') }}" +
                        "?startDate=" + encodeURIComponent(startDate) +
                        "&endDate=" + encodeURIComponent(endDate) +
                        "&code_account_id[]=" + filterCodeParam;

                Swal.close();

                // Gunakan window.location.href untuk langsung mengunduh file
                window.open(url, '_blank');
            });

            $('#exportBtn').on('click', function() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                var filterCode = $('#filterCode').val();

                var now = new Date();
                var day = String(now.getDate()).padStart(2, '0');
                var month = now.toLocaleString('default', {
                    month: 'long'
                });
                var year = now.getFullYear();
                var hours = String(now.getHours()).padStart(2, '0');
                var minutes = String(now.getMinutes()).padStart(2, '0');
                var seconds = String(now.getSeconds()).padStart(2, '0');

                var filename = `Ledger_${day} ${month} ${year} ${hours}:${minutes}:${seconds}.xlsx`;

                $.ajax({
                    url: "{{ route('exportLedger') }}",
                    type: 'GET',
                    data: {
                        startDate: startDate,
                        endDate: endDate,
                        code_account_id: filterCode,
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        var blob = new Blob([data], {
                            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = filename;
                        link.click();
                    },
                    error: function() {
                        Swal.fire({
                            title: "Export failed!",
                            icon: "error"
                        });
                    }
                });
            });
        });
    </script>
@endsection
