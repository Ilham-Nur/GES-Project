@extends('layout.main')

@section('title', 'Report | Asset Report')

@section('main')

    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Asset Report</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item active" aria-current="page">Asset Report</li>
            </ol>
        </div>
        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif
        <div class="modal fade" id="modalFilterTanggal" tabindex="-1" role="dialog"
            aria-labelledby="modalFilterTanggalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFilterTanggalTitle">Filter</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="mt-3">
                                    <label for="Tanggal" class="form-label fw-bold">Pilih Tanggal: ( Kosongkan jika ingin munculkan bulan ini )</label>
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
                            <button class="btn btn-primary mr-2" id="exportBtn">Export Excel</button>
                            <a class="btn btn-success mr-1" style="color:white;" id="Print"><span class="pr-2"><i class="fas fa-print"></i></span>Print</a>
                        </div>
                        <div class="d-flex mb-2 mr-3 mb-4">
                            <button class="btn btn-primary ml-2" id="filterTanggal">Filter</button>
                            <button type="button" class="btn btn-outline-primary ml-2" id="btnResetDefault"
                                onclick="window.location.reload()">
                                Reset
                            </button>
                        </div>
                        <div id="containerSoa" class="table-responsive px-3">

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

            const getAssetReport = () => {
                const txtSearch = $('#txSearch').val();
                const filterStatus = $('#filterStatus').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                const customer = $('#customer').val();
                
                $.ajax({
                        url: "{{ route('getAssetReport') }}",
                        method: "GET",
                        data: {
                            txSearch: txtSearch,
                            status: filterStatus,
                            startDate: startDate,
                            endDate: endDate,
                            customer: customer,
                        },
                        beforeSend: () => {
                            $('#containerSoa').html(loadSpin)
                        }
                    })
                    .done(res => {
                        $('#containerSoa').html(res)

                    })
            }

            getAssetReport();

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
                getAssetReport();
                $('#modalFilterTanggal').modal('hide');
            });
            $(document).on('click', '#Print', function (e) {
            let id = $(this).data('id');
            let startDate = $('#startDate').val();
            let endDate = $('#endDate').val();

            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: "GET",
                url: "{{ route('assetReport.pdf') }}",
                data: {
                    id: id,
                    startDate: startDate,
                    endDate: endDate,
                },
                success: function (response) {
                    Swal.close();

                    if (response.url) {
                        window.open(response.url, '_blank');
                    } else if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error
                        });
                    }
                },
                error: function (xhr) {
                    Swal.close();

                    let errorMessage = 'Gagal Export asset';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        });
            $('#exportBtn').on('click', function() {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();

            var now = new Date();
            var day = String(now.getDate()).padStart(2, '0');
            var month = now.toLocaleString('default', { month: 'long' });
            var year = now.getFullYear();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            var seconds = String(now.getSeconds()).padStart(2, '0');

            var filename = `Asset Report_${day} ${month} ${year} ${hours}:${minutes}:${seconds}.xlsx`;

            $.ajax({
                url: "{{ route('exportReport') }}",
                type: 'GET',
                data: {
                    startDate: startDate,
                    endDate: endDate
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
