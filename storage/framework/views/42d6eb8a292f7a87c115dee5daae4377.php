<?php $__env->startSection('title', 'Report | Ledger'); ?>

<?php $__env->startSection('main'); ?>
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
                        <a class="btn btn-success mr-1" style="color:white;" id="Print"><span class="pr-2"><i
                                    class="fas fa-solid fa-print mr-1"></i></span>Print</a>
                        <a href="<?php echo e(route('ledger.exportExcel')); ?>?startDate=<?php echo e(request('startDate')); ?>&endDate=<?php echo e(request('endDate')); ?>&filterCode=<?php echo e(request('filterCode')); ?>"
                            class="btn btn-success">
                            Export to Excel
                        </a>
                    </div>
                    <div class="d-flex mb-4 mr-3 float-left">
                        <button class="btn btn-primary ml-2 mr-2" id="filterTanggal">Filter Tanggal</button>
                        <select class="form-control select2multiple" id="filterCode" multiple="multiple"
                            style="width: 600px;">
                            <?php $__currentLoopData = $listCode; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $Code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($Code->id); ?>"><?php echo e($Code->code_account_id); ?> - <?php echo e($Code->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function () {
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
                url: "<?php echo e(route('getLedger')); ?>",
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

        $('#filterCode').change(function () {
            getLedger();
        });


        $('.select2multiple').select2({
            placeholder: "Pilih Akun",
            allowClear: true
        });


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
            getLedger();
            $('#modalFilterTanggal').modal('hide');
        });

        $('#Print').on('click', function (e) {
            e.preventDefault
            window.location.href = '<?php echo e(route('ledger.pdf')); ?>';
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\ilono\OneDrive\Desktop\Project SAC\pt-ges-project\resources\views/Report/Ledger/indexledger.blade.php ENDPATH**/ ?>