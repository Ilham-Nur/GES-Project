@extends('layout.main')

@section('title', 'Edit Payment')

@section('main')

    <style>
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #cccccc;
        }

        .divider::before {
            margin-right: .25em;
        }

        .divider::after {
            margin-left: .25em;
        }

        .divider span {
            padding: 0 10px;
            font-weight: bold;
            color: #555555;
        }

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
            <h1 class="h3 mb-0 text-gray-800">Edit Payment</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Customer</li>
                <li class="breadcrumb-item"><a href="{{ route('payment') }}">Payment</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Payment</li>
            </ol>
        </div>

        <a class="btn btn-primary mb-3" href="{{ route('payment') }}">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card mb-4">
            <div class="card-body">
                <!-- Input Form -->
                <div class="row">
                    <!-- Column 1 -->
                    <div class="col-lg-6">
                        <div class="form-group mt-3">
                            <label for="KodePayment" class="form-label fw-bold">Kode</label>
                            <input type="text" class="form-control" id="KodePayment" placeholder="Masukkan Kode anda"
                                disabled>
                            <div id="errKodePayment" class="text-danger mt-1 d-none">Silahkan isi Kode</div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="Marking" class="form-label fw-bold">Marking</label>
                            <select class="form-control select2" id="selectMarking" disabled>
                                @foreach ($listMarking as $markingList)
                                    <option value="{{ $markingList->marking }}"
                                        {{ $markingList->marking == $payment->pembeli->marking ? 'selected' : '' }}>
                                        {{ $markingList->marking }} ({{ $markingList->nama_pembeli }})
                                    </option>
                                @endforeach
                            </select>
                            <div id="errMarkingPayment" class="text-danger mt-1 d-none">Silahkan Pilih Marking</div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="Invoice" class="form-label fw-bold">Invoice</label>
                            <select class="form-control" id="selectInvoice" multiple disabled></select>
                            <div id="errInvoicePayment" class="text-danger mt-1 d-none">Silahkan Pilih Invoice</div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="tanggalPayment" class="form-label fw-bold">Tanggal Payment</label>
                            <input style="background-color: white" type="text" class="form-control" id="tanggalPayment">
                            <div id="errTanggalPayment" class="text-danger mt-1 d-none">Silahkan isi Tanggal</div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="tanggalPaymentBuat" class="form-label fw-bold">Tanggal Buat</label>
                            <input style="background-color: white" type="text" class="form-control"
                                id="tanggalPaymentBuat">
                            <div id="errTanggalPaymentBuat" class="text-danger mt-1 d-none">Silahkan isi Tanggal</div>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="col-lg-6">
                        <div class="form-group mt-3">
                            <label for="paymentMethod" class="form-label fw-bold">Metode Pembayaran</label>
                            <select class="form-control select2" id="selectMethod">
                                <option value="" selected disabled>Pilih Metode Pembayaran</option>
                                @foreach ($savedPaymentAccounts as $account)
                                    <option value="{{ $account->coa_id }}"
                                        {{ $account->coa_id == $payment->paymentMethod->id ? 'selected' : '' }}>
                                        {{ $account->code_account_id }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>

                            <div id="errMethodPayment" class="text-danger mt-1 d-none">Silahkan Pilih Metode</div>
                        </div>

                        <div class="form-group mt-3 d-none" id="section_poin">
                            <label for="amountPoin" class="form-label fw-bold">Poin (Kg)</label>
                            <input type="number" class="form-control" id="amountPoin" placeholder="Masukkan nominal poin">
                            <div id="erramountPoin" class="text-danger mt-1 d-none">Silahkan isi nominal Poin</div>
                            <button type="button" class="btn btn-primary mt-2" id="submitAmountPoin">Hitung
                                Payment</button>
                        </div>

                        <div class="form-group mt-3">
                            <label for="amountPayment" class="form-label fw-bold">Payment Amount</label>
                            <input type="number" class="form-control" id="payment"
                                placeholder="Masukkan nominal pembayaran" required>
                            <div id="errAmountPayment" class="text-danger mt-1 d-none">Silahkan isi Amount</div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="discountPayment" class="form-label fw-bold">Discount</label>
                            <input type="number" class="form-control" id="discountPayment" placeholder="Masukkan discount"
                                required>
                        </div>

                        <div class="input-group mt-3">
                            <label for="keteranganPayment" class="form-label fw-bold p-1">Keterangan</label>
                        </div>
                        <textarea id="keteranganPayment" class="form-control" aria-label="With textarea" placeholder="Masukkan keterangan"
                            rows="4"></textarea>

                        <input type="hidden" id="grandtotal">
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Invoice -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="fw-bold">Preview Invoice</h5>
                <div id="invoicePreview" class="border p-4 rounded shadow-sm" style="background-color: #f9f9f9;">
                    <p><strong class="text-primary">Nomor Invoice :</strong> <span id="previewInvoiceNumber">-</span></p>
                    <p><strong class="text-primary">Total Berat (Kg) :</strong> <span id="previewTotalWeight">-</span></p>
                    <p><strong class="text-primary">Total Dimensi (m³) :</strong> <span
                            id="previewTotalDimension">-</span>
                    </p>
                    <p><strong class="text-primary">Jumlah Amount :</strong> <span id="previewInvoiceAmount"
                            class="fw-bold text-success">-</span></p>
                    <p><strong class="text-primary">Total Sudah Bayar :</strong> <span id="previewTotalPaid"
                            class="fw-bold text-info">-</span></p>
                    <p><strong class="text-primary">Sisa Pembayaran :</strong> <span id="previewRemainingPayment"
                            class="fw-bold text-danger">-</span></p>
                </div>
            </div>
        </div>

        <!-- Detail Invoice -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="fw-bold">Jurnal Manual Payment</h5>
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Code Account</th>
                            <th>Tipe Account</th>
                            <th>Description</th>
                            <th>Nominal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="items-container"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <button type="button" class="btn btn-primary" id="add-item-button">Add Item</button>
                            </td>
                            <td>
                                <label>Total Payment:</label>
                                <input type="text" class="form-control" id="total_payment" name="total_payment"
                                    value="" disabled>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div id="tableError" class="alert alert-danger d-none">
                    Harap isi semua kolom di tabel sebelum melanjutkan.
                </div>
            </div>
        </div>

        <div class="text-center my-4">
            <button id="editPayment" class="btn btn-primary p-3 w-50">Edit Payment</button>
        </div>
    </div>
    <!---Container Fluid-->


@endsection
@section('script')
    <script>
        $(document).ready(function() {
            var payment = @json($payment);

            let paymentInvoice = payment.payment_invoices
            $('#selectMarking').on('change', function() {
                const marking = $(this).val();
                let invoiceIds = paymentInvoice.map(invoice => invoice.invoice_id);

                if (marking) {
                    loadInvoicesByMarking(marking, invoiceIds);
                }
            });

            $('.select2').select2();
            $('#selectInvoice').select2({
                placeholder: "Pilih Invoice",
                allowClear: true,
                width: 'resolve',
                closeOnSelect: false
            });

            function loadInvoicesByMarking(marking, invoiceIds) {
                $.ajax({
                    url: "{{ route('getInvoiceByMarkingEdit') }}",
                    type: 'GET',
                    data: {
                        marking,
                        invoiceIds
                    },
                    success: function(response) {
                        const $selectInvoice = $('#selectInvoice').empty();
                        let selectedInvoices = [];

                        if (response.success) {
                            response.invoices.forEach(no_invoice => {
                                $selectInvoice.append(
                                    `<option value="${no_invoice}">${no_invoice}</option>`
                                );
                                selectedInvoices.push(
                                    no_invoice);
                            });

                            $selectInvoice.val(selectedInvoices).trigger('change');
                        } else {
                            $selectInvoice.append(
                                '<option value="" disabled>No invoices available</option>'
                            );
                        }
                    },
                    error: function() {
                        showMessage('error!', 'Gagal memuat invoice.');
                    }
                });
            }


            $('#selectInvoice').on('change', function() {
                const invoiceNo = $(this).val();
                if (invoiceNo) {
                    loadInvoiceDetails(invoiceNo);
                }
            });

            function loadInvoiceDetails(invoiceNo) {
                $.ajax({
                    url: "{{ route('getInvoiceAmount') }}",
                    type: 'GET',
                    data: {
                        no_invoice: invoiceNo
                    },
                    success: function(response) {
                        const invoice = response.summary;
                        $('#previewInvoiceNumber').text(invoice.no_invoice.replace(/;/g, ', '));
                        $('#previewInvoiceAmount').text(invoice.total_harga);
                        $('#previewTotalPaid').text(invoice.total_bayar);
                        $('#previewTotalWeight').text(invoice.total_berat);
                        $('#previewTotalDimension').text(invoice.total_dimensi);
                        $('#previewRemainingPayment').text(invoice.sisa_bayar);
                    },
                    error: function() {
                        showMessage('error', 'Terjadi kesalahan saat memuat data invoice.');
                    }
                });
            }

            let kuotaid = @json($kuotaid);
            kuotaid = String(kuotaid);
            $('#selectMethod').on('change', function() {
                const selectedMethod = $(this).val();
                const sectionPoin = $('#section_poin');
                const paymentInput = $('#payment');
                const discountInput = $('#discountPayment');

                if (selectedMethod === kuotaid) {
                    sectionPoin.removeClass("d-none");
                    paymentInput.prop("disabled", true);
                    discountInput.prop("disabled", true);
                    paymentInput.val("");
                    discountInput.val("");
                } else {
                    sectionPoin.addClass("d-none");
                    paymentInput.prop("disabled", false);
                    discountInput.prop("disabled", false);
                    $('#amountPoin').val("");
                    // paymentInput.val("");
                    // discountInput.val("");
                }
            });

            if (payment) {
                payment.payment_customer_items.forEach(function(item) {
                    const newRow = `
                            <tr>
                                <td>
                                    <select class="form-control select2-single" name="account" style="width: 30vw;" required>
                                        <option value="">Pilih Akun</option>
                                        @foreach ($coas as $coa)
                                            <option value="{{ $coa->id }}" ${item.coa_id === {{ $coa->id }} ? 'selected' : ''}>
                                                {{ $coa->code_account_id }} - {{ $coa->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="tipeAccount" id="tipeAccount" required>
                                        <option value="" disabled>Pilih Akun</option>
                                        <option value="Credit" ${item.tipe === 'Credit' ? 'selected' : ''}>Credit</option>
                                        <option value="Debit" ${item.tipe === 'Debit' ? 'selected' : ''}>Debit</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control"name="item_desc" value="${item.description}" placeholder="Input Description" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control"  name="nominal"  value="${item.nominal}" placeholder="0.00" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger removeItemButton mt-1">Remove</button>
                                </td>
                            </tr>`;
                    $('#items-container').append(newRow);
                });

                $('.select2-single').select2();
                updateTotals();
            }

            $('#add-item-button').click(function() {
                const newRow = `
                <tr>
                    <td>
                        <select class="form-control select2singgle" name="account" style="width: 30vw;" required>
                            <option value="">Pilih Akun</option>
                            @foreach ($coas as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->code_account_id }} - {{ $coa->name }}</option>
                            @endforeach
                        </select>
                    </td>
                     <td>
                         <select class="form-control" name="tipeAccount" id="tipeAccount" required>
                            <option value="" disabled>Pilih Akun</option>
                            <option value="Credit">Credit</option>
                            <option value="Debit">Debit</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="item_desc" placeholder="Input Description" required>
                    </td>

                    <td>
                        <input type="number" class="form-control" name="nominal" value="0" placeholder="0.00" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger removeItemButton mt-1">Remove</button>
                    </td>
                </tr>`;
                $('#items-container').append(newRow);
                $('.select2singgle').last().select2();
                updateTotals();
            });

            function updateTotals() {
                let totalDebit = 0;
                $('#items-container tr').each(function() {
                    const debitValue = parseFloat($(this).find('input[name="nominal"]').val()) || 0;
                    totalDebit += debitValue;
                });

                const paymentAmount = parseFloat($('#payment').val()) || 0;
                const discountPayment = parseFloat($('#discountPayment').val()) || 0;

                const grandTotal = paymentAmount - discountPayment;
                $('#total_payment').val(totalDebit);
                $('#grandtotal').val(grandTotal);
            }

            $(document).on('click', '.removeItemButton', function() {
                $(this).closest('tr').remove();
                updateTotals();
            });

            $(document).on('input', 'input[name="nominal"]', function() {
                updateTotals();
            });

            $('#payment, #discountPayment').on('input change', function() {
                updateTotals();
            });

            $('#KodePayment').val(payment.kode_pembayaran).trigger('change');
            // $('#tanggalPayment').val(payment.payment_date).trigger('change');
            // $('#tanggalPaymentBuat').val(payment.payment_buat).trigger('change');
            $('#discountPayment').val(Math.floor(payment.discount)).trigger('change');
            $('#keteranganPayment').val(payment.Keterangan).trigger('change');
            let hasKuota = paymentInvoice.some(item => item.kuota && parseFloat(item.kuota) > 0);
            if (!hasKuota) {
                // Jika semua kuota bernilai null
                let totalAmount = paymentInvoice.reduce((total, item) => {
                    return total + parseFloat(item.amount);
                }, 0);

                // Tambahkan discount jika ada
                if (payment.discount) {
                    totalAmount += parseFloat(payment.discount);
                }

                let roundedTotalAmount = Math.round(totalAmount + Number.EPSILON);
                $('#payment').val(roundedTotalAmount).trigger('change');

            } else {
                // Jika ada kuota yang tidak null
                let totalPoin = paymentInvoice.reduce((total, item) => {
                    return total + (item.kuota ? parseFloat(item.kuota) : 0);
                }, 0);
                $('#amountPoin').val(totalPoin).trigger('change');

                let totalAmount = paymentInvoice.reduce((total, item) => {
                    return total + parseFloat(item.amount);
                }, 0);
                let roundedTotalAmount = Math.round(totalAmount + Number.EPSILON);
                $('#payment').val(roundedTotalAmount).trigger('change');
            }

            $('#selectMarking').trigger('change');
            $('#selectMethod').trigger('change');

            $('#submitAmountPoin').on('click', function() {
                const invoiceNo = $('#selectInvoice').val();
                const amountPoin = $('#amountPoin').val();
                let previousPoin = paymentInvoice.reduce((total, item) => {
                    return total + parseFloat(item.kuota);
                }, 0);

                if (!amountPoin) {
                    showMessage('error', 'Silahkan masukkan nominal poin terlebih dahulu.');
                    return;
                }
                if (!invoiceNo) {
                    showMessage('error', 'Silakan pilih nomor invoice terlebih dahulu.');
                    $('#amountPoin').val('');
                    $('#payment').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('amountPoin') }}",
                    type: 'GET',
                    data: {
                        amountPoin,
                        invoiceNo,
                        previousPoin
                    },
                    success: function(response) {
                        if (response.total_nominal) {
                            $('#payment').val(response.total_nominal);
                            updateTotals();
                        } else {
                            showMessage('error', 'Data tidak ditemukan');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.error ||
                            'Error tidak diketahui terjadi';
                        showMessage('error', errorMsg);
                        $('#payment').val('');
                    }
                });
            });

            // Inisialisasi Flatpickr untuk "tanggalPayment" dengan format DateTime
            flatpickr("#tanggalPayment", {
                enableTime: true,
                dateFormat: "d F Y H:i",
                minuteIncrement: 1,
                time_24hr: true,
                defaultDate: payment.payment_date ? new Date(payment.payment_date) : null,
            });

            // Inisialisasi Flatpickr untuk "tanggalPaymentBuat" dengan format DateTime
            flatpickr("#tanggalPaymentBuat", {
                enableTime: true,
                dateFormat: "d F Y H:i",
                minuteIncrement: 1,
                time_24hr: true,
                defaultDate: payment.payment_buat ? new Date(payment.payment_buat) : null,
            });

            $('#editPayment').click(function(e) {
                e.preventDefault();
                const paymentId = payment.id;

                $("#errKodePayment, #errMarkingPayment, #errInvoicePayment, #errTanggalPayment, #errTanggalPayment, #errMethodPayment, #erramountPoin, #errAmountPayment")
                    .addClass("d-none");

                let isValid = true;

                if (!$("#KodePayment").val().trim()) {
                    $("#errKodePayment").removeClass("d-none");
                    isValid = false;
                }

                if (!$("#selectMarking").val()) {
                    $("#errMarkingPayment").removeClass("d-none");
                    isValid = false;
                }

                if (!$("#selectInvoice").val()) {
                    $("#errInvoicePayment").removeClass("d-none");
                    isValid = false;
                }

                if (!$("#tanggalPayment").val().trim()) {
                    $("#errTanggalPayment").removeClass("d-none");
                    isValid = false;
                }
                if (!$("#tanggalPaymentBuat").val().trim()) {
                    $("#errTanggalPaymentBuat").removeClass("d-none");
                    isValid = false;
                }

                if (!$("#selectMethod").val()) {
                    $("#errMethodPayment").removeClass("d-none");
                    isValid = false;
                }

                if (!$("#section_poin").hasClass("d-none") && !$("#amountPoin").val().trim()) {
                    $("#erramountPoin").removeClass("d-none");
                    isValid = false;
                }

                if (!$("#payment").val().trim()) {
                    $("#errAmountPayment").removeClass("d-none");
                    isValid = false;
                }

                let items = [];
                $('#items-container tr').each(function() {
                    let account = $(this).find('select[name="account"]').val();
                    let itemDesc = $(this).find('input[name="item_desc"]').val();
                    let nominal = $(this).find('input[name="nominal"]').val();
                    let tipeAccount = $(this).find('select[name="tipeAccount"]').val();

                    if (!account || !itemDesc || !nominal || !tipeAccount) {
                        isValid = false;
                    }

                    if (!isValid) {
                        $('#tableError').removeClass('d-none');
                    } else {
                        $('#tableError').addClass('d-none');
                    }

                    items.push({
                        account: account,
                        tipeAccount: tipeAccount,
                        item_desc: itemDesc,
                        nominal: nominal
                    });
                });

                if (isValid) {
                    Swal.fire({
                        title: "Apakah Kamu Yakin?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#5D87FF',
                        cancelButtonColor: '#49BEFF',
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {

                            const data = {
                                paymentId: paymentId,
                                kode: $('#KodePayment').val(),
                                invoice: $('#selectInvoice').val(),
                                marking: $('#selectMarking').val(),
                                tanggalPayment: $('#tanggalPayment').val(),
                                tanggalPaymentBuat: $('#tanggalPaymentBuat').val(),
                                paymentAmount: parseFloat($('#payment').val()) || 0,
                                discountPayment: parseFloat($('#discountPayment').val()) || 0,
                                paymentMethod: $('#selectMethod').val(),
                                amountPoin: $('#amountPoin').val(),
                                keterangan: $('#keteranganPayment').val(),
                                totalAmmount: $('#grandtotal').val(),
                                items: items,
                                _token: '{{ csrf_token() }}'
                            };

                            $.ajax({
                                url: "{{ route('editpayment.update') }}",
                                method: 'POST',
                                data: data,
                                beforeSend: function() {
                                    $('#editPayment').prop('disabled', true).text(
                                        'Proses...');
                                },
                                success: function(response) {
                                    $('#editPayment').prop('disabled', false).text(
                                        'Buat Payment');
                                    if (response.success) {
                                        showMessage('success',
                                            'Payment berhasil dibuat').then(() =>
                                            location.reload());
                                    } else {
                                        showMessage('error', response.message);
                                    }
                                },
                                error: function(xhr) {
                                    $('#editPayment').prop('disabled', false).text(
                                        'Buat Payment');
                                    const errorMsg = xhr.responseJSON?.message ||
                                        'Error tidak diketahui terjadi';
                                    showMessage('error', errorMsg);
                                }
                            });
                        };
                    });

                }
            });
        });
    </script>
@endsection
