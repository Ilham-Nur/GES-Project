@extends('layout.main')

@section('title', 'Profile')

@section('main')

    <div class="modal fade" id="modalFilterTanggal" tabindex="-1" role="dialog" aria-labelledby="modalFilterTanggalTitle"
        aria-hidden="true">
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
                                <label for="pembayaranStatus" class="form-label fw-bold">Pilih Tanggal:</label>
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

    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Profile</h1>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="profileForm">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label for="name">Name</label>
                                <input id="name" class="form-control" type="text" name="name"
                                    value="{{ old('name', auth()->user()->name) }}" required autofocus>
                                <span id="name-error" class="text-danger"></span> <!-- Error display for name -->
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" class="form-control" type="email" name="email"
                                    value="{{ old('email', auth()->user()->email) }}" required>
                                <span id="email-error" class="text-danger"></span> <!-- Error display for email -->
                            </div>

                            <div class="form-group">
                                <label for="password">New Password (optional)</label>
                                <input id="password" class="form-control" type="password" name="password">
                                <span id="password-error" class="text-danger"></span> <!-- Error display for password -->
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password</label>
                                <input id="password_confirmation" class="form-control" type="password"
                                    name="password_confirmation">
                            </div>

                            @if (in_array(Auth::user()->role, ['customer']))
                                <div class="form-group">
                                    <div id="alamatContainer">
                                        @foreach ($alamatList as $index => $alamat)
                                            <div class="mt-3 alamat-item">
                                                <label for="alamatCustomer" class="form-label fw-bold">Alamat</label>
                                                <textarea class="form-control" name="alamatCustomer[]" placeholder="Masukkan alamat" rows="3">{{ $alamat->alamat }}</textarea>

                                                @if ($errors->has("alamatCustomer.$index"))
                                                    <div class="text-danger mt-1">
                                                        {{ $errors->first("alamatCustomer.$index") }}</div>
                                                @endif

                                                <button type="button" class="btn btn-danger btn-sm mt-2 remove-alamat-btn">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        @endforeach

                                        @if ($alamatList->isEmpty())
                                            <div class="mt-3 alamat-item">
                                                <label for="alamatCustomer" class="form-label fw-bold">Alamat</label>
                                                <textarea class="form-control" name="alamatCustomer[]" placeholder="Masukkan alamat" rows="3"></textarea>
                                                @error('alamatCustomer.*')
                                                    <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                <button type="button"
                                                    class="btn btn-danger btn-sm mt-2 remove-alamat-btn"
                                                    style="display: none;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" id="addAlamatButton" class="btn btn-secondary mt-3">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary mt-2">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Updated User Points Section with clearer Top-Up and current price -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">User kuota</h4>
                        <p class="card-text">Here is an overview of your current kuota and other relevant details.</p>

                        <div class="mb-3">
                            <div class="text-center">
                                <h1 id="pointValue" class="display-3 font-weight-bold text-primary">
                                    {{ $listPoin !== null ? (floor($listPoin) == $listPoin ? number_format($listPoin, 0) : rtrim(rtrim(number_format($listPoin, 2), '0'), '.')) : '0' }}
                                    Kuota</h1>
                                <p class="text-muted">Kuota</p>
                            </div>
                        </div>

                        <!-- Indikator Harga Berlaku -->
                        <div class="mb-3">
                            <p class="text-muted"></p>
                        </div>

                        <!-- Progress Bar to Show Poin Usage -->
                        {{-- <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="70"
                                aria-valuemin="0" aria-valuemax="100">100% Used</div>
                        </div> --}}

                        <!-- Top-Up Points Section with prominent button -->
                        {{-- <div class="rewards-section">
                            <h4>Top-Up Points</h4>
                            <div class="reward-item d-flex align-items-center">
                                <p class="pt-2">Ingin Mengisi Poin Untuk User Ini?</p>
                                <button class="btn-topUp ml-3 btn btn-lg btn-primary" data-toggle="modal"
                                    data-target="#topupModal">Top Up</button>
                            </div>
                        </div> --}}

                        <!-- Riwayat Penggunaan Poin Terakhir -->
                        {{-- <div class="mb-3">
                            <h5>Riwayat Penggunaan Terakhir</h5>
                            <ul>
                                <li>10 Poin digunakan pada 2024-10-20</li>
                                <li>5 Poin digunakan pada 2024-10-18</li>
                                <li>15 Poin digunakan pada 2024-10-15</li>
                            </ul>
                        </div> --}}

                    </div>
                </div>
            </div>

            <!-- Updated History Points Section -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h4>History Poin</h4>
                            <div class="sectionbutton">
                                <button class="btn btn-primary" id="filterTanggal">Filter Tanggal</button>
                                <button type="button" class="btn btn-outline-primary ml-2" id="btnResetDefault"
                                    onclick="window.location.reload()">
                                    Reset
                                </button>
                            </div>
                        </div>
                        <div class="tabelsection">
                            <table id="pointsHistoryTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer Name</th>
                                        <th>Marking</th>
                                        <th>Kuota</th>
                                        <th>Price per Kg</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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
            let table = $('#pointsHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('getPointsHistory') }}",
                    method: 'GET',
                    data: function(d) {
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                        d.status = $('#filterStatus').val();
                        d.txSearch = $('#txSearch').val();
                    },
                    error: function(xhr, error, thrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load payment data. Please try again!',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'marking',
                        name: 'marking'
                    },
                    {
                        data: 'points',
                        name: 'points'
                    },
                    {
                        data: 'price_per_kg',
                        name: 'price_per_kg'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'type_badge',
                        name: 'type_badge',
                        orderable: false,
                        searchable: false
                    },
                ],
                columnDefs: [{
                        targets: [0, 6],
                        className: 'text-center'
                    },
                    {
                        targets: 6,
                        className: 'text-center'
                    }
                ],
                order: [],
                lengthChange: false,
                pageLength: 7,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                    info: "_START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    emptyTable: "No data available in table",
                    loadingRecords: "Loading...",
                    zeroRecords: "No matching records found"
                }
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
                table.ajax.reload();
                $('#modalFilterTanggal').modal('hide');
            });
        });
    </script>
    <script>
        toggleRemoveButton();

        function toggleRemoveButton() {
            const alamatItems = $('#alamatContainer').children('.alamat-item');
            if (alamatItems.length > 1) {
                $('.remove-alamat-btn').show();
            } else {
                $('.remove-alamat-btn').hide();
            }
        }

        $('#addAlamatButton').click(function() {
            let alamatContainer = $('#alamatContainer');

            let newAlamat = `<div class="mt-3 alamat-item">
                            <label for="alamatCustomer" class="form-label fw-bold">Alamat</label>
                            <textarea class="form-control" name="alamatCustomer[]" placeholder="Masukkan alamat" rows="3"></textarea>
                            <div class="text-danger mt-1 alamat-error d-none">Silahkan isi alamat customer</div>
                            <button type="button" class="btn btn-danger btn-sm mt-2 remove-alamat-btn"><i class="fas fa-trash-alt"></i></button>
                        </div>`;
            alamatContainer.append(newAlamat);
            toggleRemoveButton();
        });

        $(document).on('click', '.remove-alamat-btn', function() {
            $(this).closest('.alamat-item').remove();
            toggleRemoveButton();
        });

        $(document).ready(function() {
            $('#profileForm').on('submit', function(event) {
                event.preventDefault();

                $.ajax({
                    url: "{{ route('profile.update') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        clearErrors();

                        if (response.success) {
                            showMessage("success", response.message).then(() => {
                                location.reload();
                                $('#name').val(response.updatedData.name);
                                $('#email').val(response.updatedData.email);
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            if (errors.password) {
                                $('#password-error').text(errors.password[0]);
                            }

                            if (errors.name) {
                                $('#name-error').text(errors.name[0]);
                            }

                            if (errors.email) {
                                $('#email-error').text(errors.email[0]);
                            }

                            if (errors['alamatCustomer.*']) {
                                $('.alamat-error').each(function(index) {
                                    $(this).text(errors['alamatCustomer.*'][index] ||
                                        '').removeClass('d-none');
                                });
                            }

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong. Please try again later.',
                                showConfirmButton: true
                            });
                        }
                    }
                });
            });

            function clearErrors() {
                $('#password-error').text('');
                $('#name-error').text('');
                $('#email-error').text('');
                $('.alamat-error').text('').addClass('d-none');
            }
        });
    </script>
@endsection
