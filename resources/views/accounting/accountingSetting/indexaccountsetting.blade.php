@extends('layout.main')

@section('title', 'Accounting | Accounting Setting')

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
            <h1 class="h3 mb-0 text-gray-800">Accounting Setting</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Accounting</li>
                <li class="breadcrumb-item active" aria-current="page">Accounting Setting</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg">
                <div class="card mb-4">
                    <div class="card-body">
                        <input type="hidden" id="idData" value="{{ $createdAtStatus ?? '' }}">
                        <div id="containerBooking" class="table-responsive px-3">
                            <div class="mt-3">
                                <label for="salesAccount" class="form-label fw-bold">Sales Account</label>
                                <select class="form-control select2singgle" id="Sales" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->sales_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="receivableSalesAccount" class="form-label fw-bold">Receivable Sales
                                    Account</label>
                                <select class="form-control select2singgle" id="Receivable" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->receivable_sales_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="returnAccount" class="form-label fw-bold">Customer Sales Return Account</label>
                                <select class="form-control select2singgle" id="Return" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->customer_sales_return_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="discountAccount" class="form-label fw-bold">Customer Paymennt Kuota Margin</label>
                                <select class="form-control select2singgle" id="Discount" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->discount_sales_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-3">
                                <label for="purchaseProfitAccount" class="form-label fw-bold">Setting Kredit untuk Top Up</label>
                                <select class="form-control select2singgle" id="PurchaseRate" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->purchase_profit_rate_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-3">
                                <label for="supplierAccount" class="form-label fw-bold">Supplier Purchase Return
                                    Account</label>
                                <select class="form-control select2singgle" id="Supplier" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->supplier_purchase_return_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-3">
                                <label for="salesProfitAccount" class="form-label fw-bold">Discount Payment Account</label>
                                <select class="form-control select2singgle" id="ProfitRate" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}"
                                            {{ $accountSettings && $accountSettings->sales_profit_rate_account_id == $coa->id ? 'selected' : '' }}>
                                            {{ $coa->code_account_id }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        <div class="mt-3">
                        <label for="PaymentAcoount" class="form-label fw-bold">Payment Account</label>
                            <select class="form-control" id="PaymentAccount" style="width:100%;" multiple="multiple">
                                <option value="">Pilih Akun</option>
                                @foreach ($coas as $coa)
                                    <option value="{{ $coa->id }}" 
                                        @if(in_array($coa->id, $savedPaymentAccounts)) selected @endif>
                                        {{ $coa->code_account_id }} - {{ $coa->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div id="containerBooking" class="table-responsive px-3">
                            <div class="d-flex justify-content-center mb-2 mr-3">
                                <button id="saveButton" class="btn btn-primary p-3 float-right mt-3" style="width: 30%;"
                                    disabled>Save</button>
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
            $('.select2singgle').select2();
            const $saveButton = $('#saveButton');
            const selectedValues = {};

            const accountSettingId = $('#idData').val();

            $('#PaymentAccount').select2({
                placeholder: 'Pilih Akun',
                allowClear: true
            });
            $('select').on('change', function() {
                const dropdownName = $(this).attr('name');
                const selectedValue = $(this).val();
                selectedValues[dropdownName] = selectedValue;
                $saveButton.removeClass('btn-secondary').addClass('btn-primary').prop('disabled', false);
            });

            $('#saveButton').on('click', function(e) {
            e.preventDefault();

            const Data = {
                idData: $('#idData').val(),
                sales_account_id: $('#Sales').val(),
                receivable_sales_account_id: $('#Receivable').val(),
                customer_sales_return_account_id: $('#Return').val(),
                discount_sales_account_id: $('#Discount').val(),
                sales_profit_rate_account_id: $('#ProfitRate').val(),
                sales_loss_rate_account_id: $('#LossRate').val(),
                purchase_account_id: $('#Purchase').val(),
                debt_account_id: $('#Debt').val(),
                supplier_purchase_return_account_id: $('#Supplier').val(),
                discount_purchase_account_id: $('#DiscountPurchase').val(),
                purchase_profit_rate_account_id: $('#PurchaseRate').val(),
                purchase_loss_rate_account_id: $('#PurchaseLoss').val(),
                coa_id: $("#PaymentAccount").val()  
            };

            Swal.fire({
                title: 'Memproses...',
                text: 'Harap tunggu, sedang memperbarui data.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/account-settings/store',  
                type: 'POST',
                data: Data,  
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.close();
                    showMessage("success", "Data berhasil disimpan").then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        title: 'Terjadi Kesalahan!',
                        text: xhr.responseJSON.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

    </script>
    @endsection
