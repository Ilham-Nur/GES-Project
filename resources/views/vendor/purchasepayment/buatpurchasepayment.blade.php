@extends('layout.main')

@section('title', 'Buat Payment')

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
            <h1 class="h3 mb-0 text-gray-800">Buat Payment</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Vendor</li>
                <li class="breadcrumb-item"><a href="{{ route('purchasePayment') }}">Payment</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat Payment</li>
            </ol>
        </div>

        <a class="btn btn-primary mb-3" href="{{ route('purchasePayment') }}">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="col-6">
                                <div class="mt-3">
                                    <label for="Invoice" class="form-label fw-bold">Invoice</label>
                                    <select class="form-control select2" id="selectInvoice">
                                        <option value="" selected disabled>Pilih Invoice</option>
                                        @foreach ($listInvoice as $invoice)
                                            <option value="{{ $invoice->invoice_no }}">{{ $invoice->invoice_no }}</option>
                                        @endforeach
                                    </select>
                                    <div id="errInvoicePayment" class="text-danger mt-1 d-none">Silahkan Pilih Invoice</div>
                                </div>
                                <div class="mt-3">
                                    <label for="" class="form-label fw-bold">Tanggal Payment</label>
                                    <input type="input" class="form-control" id="tanggalPayment" value="">
                                    <div id="errTanggalPayment" class="text-danger mt-1 d-none">Silahkan isi Tanggal
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label for="amountPayment" class="form-label fw-bold">Payment Amount</label>
                                    <input type="number" class="form-control" id="payment" name="" value=""
                                        placeholder="Masukkan nominal pembayaran" required>
                                    <div id="errAmountPayment" class="text-danger mt-1 d-none">Silahkan isi Amount
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label for="paymentMethod" class="form-label fw-bold">Metode Pembayaran</label>
                                    <select class="form-control select2" id="selectMethod">
                                        <option value="" selected disabled>Pilih Metode Pembayaran</option>
                                        @foreach ($coas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->code_account_id }} -
                                                {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="errMethodPayment" class="text-danger mt-1 d-none">Silahkan Pilih Metode</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <h5 class="fw-bold mt-3">Preview Invoice</h5>
                                <div id="invoicePreview" class="border p-4 rounded mt-3 shadow-sm"
                                    style="background-color: #f9f9f9;">
                                    <p><strong class="text-primary">Nomor Invoice :</strong> <span
                                            id="previewInvoiceNumber">-</span></p>
                                    <p><strong class="text-primary">Tanggal Invoice :</strong> <span
                                            id="previewInvoiceDate">-</span></p>
                                    <p><strong class="text-primary">Status Invoice :</strong> <span
                                            id="previewInvoiceStatus">-</span></p>
                                    <p><strong class="text-primary">Jumlah Amount :</strong> <span id="previewInvoiceAmount"
                                            class="fw-bold text-success">-</span></p>
                                    <p><strong class="text-primary">Total Sudah Bayar :</strong> <span id="previewTotalPaid"
                                            class="fw-bold text-info">-</span></p>
                                    <p><strong class="text-primary">Sisa Pembayaran :</strong> <span
                                            id="previewRemainingPayment" class="fw-bold text-danger">-</span></p>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="col-12 mt-4 mb-5">
                        <div class="col-4 float-right">
                            <button id="buatPayment" class="btn btn-primary p-3 float-right mt-3" style="width: 100%;">Buat
                                Payment</button>
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
        var today = new Date();
        $('#tanggalPayment').datepicker({
            format: 'dd MM yyyy',
            todayBtn: 'linked',
            todayHighlight: true,
            autoclose: true,
        }).datepicker('setDate', today);

        $('.select2').select2();

        $('#selectInvoice').on('change', function() {
            var invoiceNo = $(this).val();

            if (invoiceNo) {

                $.ajax({
                    url: "{{ route('getSupInvoiceAmount') }}",
                    type: 'GET',
                    data: {
                        no_invoice: invoiceNo
                    },
                    success: function(response) {
                        if (response.success) {

                            console.log(response);

                            $('#previewInvoiceNumber').text(response.invoice.invoice_no);
                            $('#previewInvoiceAmount').text(response.invoice
                                .total_harga);
                            $('#previewInvoiceDate').text(response.invoice
                                .tanggal_bayar);
                            $('#previewInvoiceStatus').text(response.invoice
                                .status_bayar
                            );
                            $('#previewTotalPaid').text(response.invoice
                                .total_bayar);
                            $('#previewRemainingPayment').text(response.invoice
                                .sisa_bayar);
                        } else {
                            alert('Data tidak ditemukan');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Error handling
                        console.log(xhr.responseText);
                    }
                });
            }
        });

        $('#buatPayment').click(function(e) {
            e.preventDefault();

            var invoice = $('#selectInvoice').val();
            var tanggalPayment = $('#tanggalPayment').val();
            var paymentAmount = $('#payment').val();
            var paymentMethod = $('#selectMethod').val();

            let valid = true;
            if (!invoice) {
                $('#errInvoicePayment').removeClass('d-none');
                valid = false;
            }
            if (!tanggalPayment) {
                $('#errTanggalPayment').removeClass('d-none');
                valid = false;
            }
            if (!paymentAmount) {
                $('#errAmountPayment').removeClass('d-none');
                valid = false;
            }
            if (!paymentMethod) {
                $('#errMethodPayment').removeClass('d-none');
                valid = false;
            }

            if (valid) {
                $.ajax({
                    url: "{{ route('paymentSup') }}",
                    method: 'POST',
                    data: {
                        invoice: invoice,
                        tanggalPayment: tanggalPayment,
                        paymentAmount: paymentAmount,
                        paymentMethod: paymentMethod,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage("success", "Payment berhasil dibuat").then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message // Menampilkan pesan error dari response
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        let responseJSON = xhr.responseJSON;
                        if (responseJSON && responseJSON.message) {
                            // Menampilkan pesan error jika ada
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan!',
                                text: responseJSON.message // Pesan error dari backend
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan!',
                                text: 'Error tidak diketahui terjadi'
                            });
                        }
                    }
                });
            }
        });
    </script>
@endsection
