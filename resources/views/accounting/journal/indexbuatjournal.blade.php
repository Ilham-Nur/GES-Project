@extends('layout.main')

@section('title', 'Accounting | Journal')

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


<!---Container Fluid-->
<div class="container-fluid" id="container-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Journal</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Accounting</li>
            <li class="breadcrumb-item"><a href="{{ route('journal') }}">Journal</a></li>
            <li class="breadcrumb-item active" aria-current="page">Buat Journal</li>
        </ol>
    </div>
    <a class="btn btn-primary mb-3" href="{{ route('journal') }}">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>

    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-12">
                            <div class="mt-3 col-6">
                                <label for="" class="form-label fw-bold">Tanggal</label>
                                <input type="text" class="form-control" id="tanggalJournal" value="">
                                <div id="errTanggalJournal" class="text-danger mt-1 d-none">Silahkan isi tanggal
                                </div>
                            </div>
                            <div class="my-3 ml-3">
                                <label for="Tipe" class="form-label fw-bold">Tipe Kode</label>
                                <div class="input-container">
                                    <input type="radio" id="type1" name="code_type" value="BKM">
                                    <label for="type1" class="input-label mr-3">BKM</label>
                                    <input type="radio" id="type2" name="code_type" value="BKK">
                                    <label for="type2" class="input-label mr-3">BKK</label>
                                    <input type="radio" id="type3" name="code_type" value="JU">
                                    <label for="type3" class="input-label mr-3">JU</label>
                                    <input type="radio" id="type4" name="code_type" value="AP">
                                    <label for="type4" class="input-label mr-3">AP</label>
                                    <input type="radio" id="type5" name="code_type" value="AR">
                                    <label for="type5" class="input-label mr-3">AR</label>
                                    <input type="radio" id="type6" name="code_type" value="CN">
                                    <label for="type6" class="input-label mr-3">CN</label>
                                    <input type="radio" id="type7" name="code_type" value="DN">
                                    <label for="type7" class="input-label">DN</label>
                                </div>
                                <div id="errCodeType" class="text-danger mt-1 d-none">Silahkan pilih tipe kode</div>
                            </div>
                            <div class="col-6">
                                <label for="noJournal" class="form-label fw-bold">No.Journal</label>
                                <input type="text" class="form-control" id="noJournal" value=""
                                    placeholder="Masukkan No Journal">
                                <div id="noJournalError" class="text-danger mt-1 d-none">Silahkan isi No Journal
                                </div>
                            </div>
                            <div class="mt-3 col-6">
                                <label for="noRef" class="form-label fw-bold">No.Ref</label>
                                <input type="text" class="form-control" id="noRef" value=""
                                    placeholder="Masukkan No Ref">
                                <div id="noRefError" class="text-danger mt-1 d-none">Silahkan isi No Ref</div>
                            </div>
                            <div class="mt-3 col-6">
                                <label for="descriptionJournal" class="form-label fw-bold">Description</label>
                                <textarea class="form-control" id="descriptionJournal" rows="3"
                                    placeholder="Masukkan Description"></textarea>
                                <div id="descriptionJournalError" class="text-danger mt-1 d-none">Silahkan isi
                                    Description</div>
                            </div>
                            <table class="table mt-3">
                                <thead>
                                    <tr>
                                        <th>Code Account</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Memo</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="items-container">
                                    <!-- Two default rows without the "Remove" button initially -->
                                    <tr>
                                        <td>
                                            <select class="form-control select2singgle" name="account"
                                                style="width: 15vw;" required>
                                                <option value="">Pilih Akun</option>
                                                @foreach ($coas as $coa)
                                                    <option value="{{ $coa->id }}">
                                                        {{ $coa->code_account_id }} - {{ $coa->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="item_desc"
                                                placeholder="Input Description" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="debit" value="0"
                                                placeholder="0.00" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="credit" value="0"
                                                placeholder="" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="memo" placeholder="">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger removeItemButton mt-1"
                                                style="display:none;">Remove</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select class="form-control select2singgle" name="account"
                                                style="width: 15vw;" required>
                                                <option value="">Pilih Akun</option>
                                                @foreach ($coas as $coa)
                                                    <option value="{{ $coa->id }}">
                                                        {{ $coa->code_account_id }} - {{ $coa->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="item_desc"
                                                placeholder="Input Description" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="debit" value="0"
                                                placeholder="" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="credit" value="0"
                                                placeholder="" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="memo" placeholder="">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger removeItemButton mt-1"
                                                style="display:none;">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-primary" id="add-item-button">Add
                                                Item</button>
                                        </td>
                                        <td></td>
                                        <td>
                                            <label>Total:</label>
                                            <input type="text" class="form-control-flush" id="total_debit"
                                                name="total_debit" value="" disabled>
                                        </td>
                                        <td>
                                            <label>Total:</label>
                                            <input type="text" class="form-control-flush" id="total_credit"
                                                name="total_credit" value="" disabled>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="col-12 mt-4">
                                <div class="col-4 float-right">
                                    <button id="approveJournal" class="btn btn-success p-3 float-right mt-3"
                                        style="width: 80%;">Approve</button>
                                    <button id="buatJournal" class="btn btn-primary p-3 float-right mt-3"
                                        style="width: 80%;">Simpan Draft</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!---Container Fluid-->

@endsection
@section('script')
<script>
    $(document).ready(function () {

        var today = new Date();
        $('#tanggalJournal').datepicker({
            format: 'dd MM yyyy',
            todayBtn: 'linked',
            todayHighlight: true,
            autoclose: true,
        }).datepicker('setDate', today);


        function checkAndHideBKKOption() {
            const urlParams = new URLSearchParams(window.location.search);
            const codeType = urlParams.get('code_type');

            if (codeType === 'BKK') {
                $('#type2').hide();
                $('label[for="type2"]').hide();
                $('#type2').prop('checked', true);

                $('#type3').hide();
                $('label[for="type3"]').hide();

                $('#type1').hide();
                $('label[for="type1"]').hide();

                $('#type4').hide();
                $('label[for="type4"]').hide();

                $('#type5').hide();
                $('label[for="type5"]').hide();

                $('#type6').hide();
                $('label[for="type6"]').hide();

                $('#type7').hide();
                $('label[for="type7"]').hide();

                $('label[for="Tipe"]').hide();

                $('#errCodeType').hide();

                function generateNoJournalBKK() {
                    $.ajax({
                        url: "{{ route('generateNoJournalBKK') }}",
                        type: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            $('#noJournal').val('Loading...');
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                $('#noJournal').val(response.no_journal);
                            } else {
                                alert('noJournal generation failed.');
                            }
                        },
                        error: function (xhr, status, error) {
                            showMessage("error", "Terjadi kesalahan: " + error);
                        },
                        complete: function () {
                            $('#noJournal').find('.spinner-border').remove();
                        }
                    });
                }
                generateNoJournalBKK();
                $('.select2').select2();

            } else if (codeType === 'Jurnal') {
                $('#type2').hide();
                $('label[for="type2"]').hide();

                $('#type1').hide();
                $('label[for="type1"]').hide();

                $('input[name="code_type"]').change(function () {
                    const selectedType = $('input[name="code_type"]:checked').val();

                    if (selectedType) {
                        $.ajax({
                            url: "{{ route('generateNoJurnal') }}",
                            method: 'GET',
                            data: {
                                code_type: selectedType,
                            },
                            success: function (response) {
                                $('#noJournal').val(response.no_journal);
                            },
                            error: function (xhr) {

                            }
                        });
                    } else {
                        $('#errMessage').removeClass('d-none');
                    }
                });

            } else if (codeType === 'BKM') {
                $('#type2').hide();
                $('label[for="type2"]').hide();

                $('#type3').hide();
                $('label[for="type3"]').hide();

                $('#type1').hide();
                $('label[for="type1"]').hide();
                $('#type1').prop('checked', true);

                $('#type4').hide();
                $('label[for="type4"]').hide();

                $('#type5').hide();
                $('label[for="type5"]').hide();

                $('#type6').hide();
                $('label[for="type6"]').hide();

                $('#type7').hide();
                $('label[for="type7"]').hide();

                $('label[for="Tipe"]').hide();

                $('#errCodeType').hide();

                function generateNoJournalBKM() {
                    $.ajax({
                        url: "{{ route('generateNoJournalBKM') }}",
                        type: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            $('#noJournal').val('Loading...');
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                $('#noJournal').val(response.no_journal);
                            } else {
                                alert('noJournal generation failed.');
                            }
                        },
                        error: function (xhr, status, error) {
                            showMessage("error", "Terjadi kesalahan: " + error);
                        },
                        complete: function () {
                            $('#noJournal').find('.spinner-border').remove();
                        }
                    });
                }
                generateNoJournalBKM();
                $('.select2').select2();
            }

        }
        checkAndHideBKKOption();

        function updateTotals() {
            var totalDebit = 0;
            var totalCredit = 0;
            $('#items-container tr').each(function () {
                var debitValue = parseFloat($(this).find('input[name="debit"]').val()) || 0;
                var creditValue = parseFloat($(this).find('input[name="credit"]').val()) || 0;

                totalDebit += debitValue;
                totalCredit += creditValue;
            });

            $('#total_debit').val(totalDebit.toFixed(0));
            $('#total_credit').val(totalCredit.toFixed(0));
        }
        $('.select2singgle').select2();
        $('#add-item-button').click(function () {
            var newRow = `
    <tr>
        <td>
            <select class="form-control select2singgle" name="account" style="width: 15vw;" required>
                <option value="">Pilih Akun</option>
                @foreach ($coas as $coa)
                    <option value="{{ $coa->id }}">
                        {{ $coa->code_account_id }} - {{ $coa->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="item_desc" placeholder="Input Description" required>
        </td>
        <td>
            <input type="number" class="form-control" name="debit" value="0" placeholder="0.00" required>
        </td>
        <td>
            <input type="number" class="form-control" name="credit" value="0" placeholder="0.00" required>
        </td>
        <td>
            <input type="text" class="form-control" name="memo" placeholder="">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger removeItemButton mt-1">Remove</button>
        </td>
    </tr>
    `;
            $('#items-container').append(newRow);
            $('.select2singgle').last().select2();
            if ($('#items-container tr').length > 2) {
                $('.removeItemButton').show();
            }
            updateTotals();
        });

        $(document).on('click', '.removeItemButton', function () {
            var rowCount = $('#items-container tr').length;
            if (rowCount > 2) {
                $(this).closest('tr').remove();
            }

            rowCount = $('#items-container tr').length;

            if (rowCount === 2) {
                $('.removeItemButton').hide();
            }

            updateTotals();
        });

        $(document).on('input', 'input[name="debit"], input[name="credit"]', function () {
            updateTotals();
        });

        function valueJournal(status) {
            var journalData = {
                tanggalJournal: $('#tanggalJournal').val(),
                codeType: $('input[name="code_type"]:checked').val(),
                noJournal: $('#noJournal').val(),
                noRef: $('#noRef').val(),
                descriptionJournal: $('#descriptionJournal').val(),
                items: [],
                status: status
            };

            $('#items-container tr').each(function () {
                var rowData = {
                    account: $(this).find('select[name="account"]').val(),
                    item_desc: $(this).find('input[name="item_desc"]').val(),
                    debit: $(this).find('input[name="debit"]').val(),
                    credit: $(this).find('input[name="credit"]').val(),
                    memo: $(this).find('input[name="memo"]').val()
                };
                journalData.items.push(rowData);
            });

            journalData.totalDebit = $('#total_debit').val();
            journalData.totalCredit = $('#total_credit').val();
            return journalData;
        }


        $('#approveJournal').click(function () {
            var journal = valueJournal('Approve');


            $.ajax({
                url: "{{ route('storeJurnal') }}",
                type: 'POST',
                data: journal,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    showMessage("success", response.message)
                        .then(
                            () => {
                                location.reload();
                            });
                },
                error: function (xhr, status, error) {
                    console.error('Error approving journal:', error);
                    var errorMessage = xhr.responseJSON.error ||
                        'Terjadi kesalahan saat menyetujui jurnal.';
                    showMessage("error", errorMessage);
                }
            });
        });

        $('#buatJournal').click(function () {
            var journal = valueJournal('Draft');
            var tanggalJournal = $('#tanggalJournal').val();
            var noJournal = $('#noJournal').val();
            var noRef = $('#noRef').val();
            var descriptionJournal = $('#descriptionJournal').val();

            var isValid = true;


            if (tanggalJournal === '' || tanggalJournal === null) {
                $('#errTanggalJournal').removeClass('d-none');
                isValid = false;
            } else {
                $('#errTanggalJournal').addClass('d-none');
            }
            var selectedCodeType = $('input[name="code_type"]:checked').val();
            if (selectedCodeType === undefined) {
                $('#errCodeType').removeClass('d-none');
                isValid = false;
            } else {
                $('#errCodeType').addClass('d-none');
            }
            if (noJournal === '' || noJournal === null) {
                $('#noJournalError').removeClass('d-none');
                isValid = false;
            } else {
                $('#noJournalError').addClass('d-none');
            }
            // if (noRef === '' || noRef === null) {
            //     $('#noRefError').removeClass('d-none');
            //     isValid = false;
            // } else {
            //     $('#noRefError').addClass('d-none');
            // }
            if (descriptionJournal === '' || descriptionJournal === null) {
                $('#descriptionJournalError').removeClass('d-none');
                isValid = false;
            } else {
                $('#descriptionJournalError').addClass('d-none');
            }


            if (isValid) {
                $.ajax({
                    url: "{{ route('storeJurnal') }}",
                    type: 'POST',
                    data: journal,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        showMessage("success", response.message)
                            .then(
                                () => {
                                    location.reload();
                                });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error saving draft:', error);
                        var errorMessage = xhr.responseJSON.error ||
                            'Terjadi kesalahan saat membuat jurnal atau Cek data yang kosong.';
                        showMessage("error", errorMessage);
                    }
                });
            } else {
                showMessage("error", "Harap periksa kembali data yang diperlukan.");
            }
        });
    });
</script>
@endsection
