<?php $__env->startSection('title', 'Report | Top Up Report'); ?>

<?php $__env->startSection('main'); ?>

<div class="container-fluid" id="container-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Top Up Report</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Report</li>
            <li class="breadcrumb-item active" aria-current="page">Top Up Report</li>
        </ol>
    </div>
    <?php if($errors->has('error')): ?>
        <div class="alert alert-danger">
            <?php echo e($errors->first('error')); ?>

        </div>
    <?php endif; ?>
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
                                <label for="customer" class="form-label fw-bold">Customer:</label>
                                <select class="form-control select2" id="customer">
                                    <option value="" selected disabled>Pilih Customer</option>
                                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->marking); ?> -
                                            <?php echo e($customer->nama_pembeli); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="mt-3">
                                <label for="Tanggal" class="form-label fw-bold">Pilih Tanggal: ( Kosongkan jika ingin
                                    munculkan data bulan ini )</label>
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
                        <a class="btn btn-success mr-1" style="color:white;" id="Print"><span class="pr-2"><i
                                    class="fas fa-print"></i></span>Print</a>
                    </div>
                    <div class="d-flex mb-2 mr-3 mb-4">
                        <button class="btn btn-primary ml-2" id="filterTanggal">Filter</button>
                        <button type="button" class="btn btn-outline-primary ml-2" id="btnResetDefault"
                            onclick="window.location.reload()">
                            Reset
                        </button>
                    </div>
                    <div id="containerTopup" class="table-responsive px-3">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function () {
        const loadSpin = `<div class="d-flex justify-content-center align-items-center mt-5">
            <div class="spinner-border d-flex justify-content-center align-items-center text-primary" role="status"></div>
        </div> `;

        const getTopUpReport = () => {
            const txtSearch = $('#txSearch').val();
            const filterStatus = $('#filterStatus').val();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            const customer = $('#customer').val();


            $.ajax({
                url: "<?php echo e(route('getTopUpReport')); ?>",
                method: "GET",
                data: {
                    txSearch: txtSearch,
                    status: filterStatus,
                    startDate: startDate,
                    endDate: endDate,
                    customer: customer,

                },
                beforeSend: () => {
                    $('#containerTopup').html(loadSpin)
                }
            })
                .done(res => {
                    $('#containerTopup').html(res)

                })
        }

        getTopUpReport();

        flatpickr("#startDate", {
            dateFormat: "d M Y",
            onChange: function (selectedDates, dateStr, instance) {

                $("#endDate").flatpickr({
                    dateFormat: "d M Y",
                    minDate: dateStr
                });
            }
        });

        flatpickr("#endDate", {
            dateFormat: "d MM Y",
            onChange: function (selectedDates, dateStr, instance) {
                var startDate = new Date($('#startDate').val());
                var endDate = new Date(dateStr);
                if (endDate < startDate) {
                    showwMassage(error,
                        "Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.");
                    $('#endDate').val('');
                }
            }
        });

        $(document).on('click', '#filterTanggal', function (e) {
            $('#modalFilterTanggal').modal('show');
        });

        $('#saveFilterTanggal').click(function () {
            getTopUpReport();
            $('#modalFilterTanggal').modal('hide');
        });
        $(document).on('click', '#Print', function (e) {
            let id = $(this).data('id');
            let startDate = $('#startDate').val();
            let endDate = $('#endDate').val();
            let customer = $('#customer').val();

            console.log("Button #Print clicked");
            console.log("ID:", id);
            console.log("Start Date:", startDate);
            console.log("End Date:", endDate);
            console.log("Customer:", customer);

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
                url: "<?php echo e(route('topUpReport.pdf')); ?>",
                data: {
                    id: id,
                    startDate: startDate,
                    endDate: endDate,
                    nama_pembeli: customer
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

                    let errorMessage = 'Gagal Export Topup Report';
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
        $('#exportBtn').on('click', function () {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
            var customer = $('#customer').val();

            var now = new Date();
            var day = String(now.getDate()).padStart(2, '0');
            var month = now.toLocaleString('default', { month: 'long' });
            var year = now.getFullYear();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            var seconds = String(now.getSeconds()).padStart(2, '0');

            var filename = `Topup Report_${day} ${month} ${year} ${hours}:${minutes}:${seconds}.xlsx`;

            $.ajax({
                url: "<?php echo e(route('exportTopupReport')); ?>",
                type: 'GET',
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    nama_pembeli: customer
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data) {
                    var blob = new Blob([data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                },
                error: function () {
                    Swal.fire({
                        title: "Export failed!",
                        icon: "error"
                    });
                }
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\GES\GES-Project\resources\views/Report/TopUpReport/indextopupreport.blade.php ENDPATH**/ ?>