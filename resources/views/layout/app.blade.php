<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    {{-- <link href="img/logo/logo.png" rel="icon"> --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('/logo.svg') }}">

    <title>PT. GES | @yield('title')</title>
    <link href="{{ asset('RuangAdmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('RuangAdmin/vendor/bootstrap/css/bootstrap.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('RuangAdmin/css/ruang-admin.min.css') }}" rel="stylesheet">
    <link href="{{ asset('RuangAdmin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('RuangAdmin/vendor/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('RuangAdmin/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('css/monthSelect.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inputTags.css') }}">
    <link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
    @stack('styles')

</head>

<body id="page-top">

    @yield('content')

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="{{ asset('RuangAdmin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('RuangAdmin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('RuangAdmin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('RuangAdmin/js/ruang-admin.min.js') }}"></script>
    <script src="{{ asset('RuangAdmin/vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('RuangAdmin/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('RuangAdmin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('RuangAdmin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src=" {{ asset('RuangAdmin/vendor/select2/dist/js/select2.min.js') }}"></script>
    <script src=" {{ asset('RuangAdmin/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/sweetalert2.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/flatpickr.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/monthSelect.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.js') }}"></script>
    <script src="{{ asset('js/index.js') }}"></script>
    <script src="js/signature_pad.umd.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        function showMessage(type, message) {
            if (!type || type === '' || !message || message === '') {
                return;
            }
            return Swal.fire({
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 2000
            })

        }


        $(document).ready(function() {
            /**
             * Function to load unpaid invoice notifications
             */
            function loadNotifications() {
                $.ajax({
                    url: '{{ route('unpaidInvoices') }}', // Ganti dengan route Anda
                    method: 'GET',
                    success: function(data) {
                        const notificationContainer = $('#invoice-notifications');
                        const badgeCounter = $('#unpaid-invoice-count');
                        notificationContainer.empty();

                        if (data.length === 0) {
                            badgeCounter.hide();
                            notificationContainer.html(
                                '<p class="dropdown-item py-2 text-center small text-gray-500">No unpaid invoices</p>'
                            );
                            return;
                        }

                        badgeCounter.text(data.length).show();

                        data.forEach(function(invoice) {
                            // Konversi total_sisa_bayar ke angka
                            const rawAmount = invoice.total_sisa_bayar.replace(/\./g, '')
                                .replace(',', '.');
                            const amountDue = parseFloat(rawAmount);

                            if (!isNaN(amountDue)) {
                                // Format angka dengan Intl.NumberFormat
                                const formattedAmountDue = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(amountDue);

                                // Tambahkan ke container
                                const notificationItem = `
                                    <div class="dropdown-item d-flex align-items-center">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-danger">
                                                <i class="fas fa-file-invoice-dollar text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div>
                                                <strong>${invoice.nama_pembeli} (${invoice.marking})</strong>
                                            </div>
                                            <div class="mt-1">
                                                <span>Total tagihan : ${formattedAmountDue}</span>
                                            </div>
                                            <div class="small text-gray-500">Tagihan telah melewati 2 bulan</div>
                                        </div>
                                    </div>
                                `;
                                notificationContainer.append(notificationItem);
                            } else {
                                console.error(`Invalid total_sisa_bayar for invoice: `,
                                    invoice);
                            }
                        });
                    },
                    error: function() {
                        console.error('Failed to load unpaid invoices');
                        $('#invoice-notifications').html(
                            '<p class="dropdown-item py-2 text-center small text-danger">Failed to load invoices</p>'
                        );
                    }
                });
            }

            /**
             * Function to load quota notifications
             */
            function loadKuotaNotifications() {
                $.ajax({
                    url: '{{ route('topupNotification') }}', // Ganti dengan route Anda
                    method: 'GET',
                    success: function(data) {
                        const notificationContainer = $('#kuota-notifications');
                        const badgeCounter = $('#unpaid-kuota-count');
                        notificationContainer.empty();

                        let totalNotifications = 0;

                        if (data.low_quota && data.low_quota.length > 0) {
                            data.low_quota.forEach(function(item) {
                                if (item.customer) {
                                    const notificationItem = `
                                <div class="dropdown-item d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div>
                                                <strong>${item.customer.nama_pembeli} (${item.customer.marking})</strong>
                                            </div>
                                            <div class="mt-1">
                                                 <span>Saldo kuota Dibawah 20% (${item.balance}) Silahkan melakukan Isi ulang</span>
                                            </div>
                                    </div>
                                </div>
                            `;
                                    notificationContainer.append(notificationItem);
                                    totalNotifications++;
                                }
                            });
                        }

                        // Tampilkan notifikasi mendekati expired (nearing_expiry)
                        if (data.nearing_expiry && data.nearing_expiry.length > 0) {
                            data.nearing_expiry.forEach(function(item) {
                                if (item.customer) {
                                    const notificationItem = `
                                <div class="dropdown-item d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-danger">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div>
                                            <strong>${item.customer.nama_pembeli} (${item.customer.marking})</strong>
                                        </div>
                                         <div class="mt-1">
                                                 <span>Kuota akan expired Pada Tanggal :(${formatDate(item.expired_date)})</span>
                                            </div>
                                    </div>
                                </div>
                            `;
                                    notificationContainer.append(notificationItem);
                                    totalNotifications++;
                                }
                            });
                        }

                        // Perbarui badge counter
                        if (totalNotifications > 0) {
                            badgeCounter.text(totalNotifications)
                                .show(); // Tampilkan badge jika ada notifikasi
                        } else {
                            badgeCounter.hide(); // Sembunyikan badge jika tidak ada notifikasi
                            notificationContainer.html(
                                '<p class="dropdown-item py-2 text-center small text-gray-500">Tidak ada notifikasi kuota</p>'
                            );
                        }
                    },
                    error: function() {
                        console.error('Gagal memuat notifikasi kuota');
                        $('#kuota-notifications').html(
                            '<p class="dropdown-item py-2 text-center small text-danger">Failed to load kuota notifications</p>'
                        );
                    }
                });
            }

            /**
             * Helper function to format date
             */
            function formatDate(dateString) {
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                return new Date(dateString).toLocaleDateString('id-ID', options);
            }

            // Load notifications on page load
            loadNotifications();
            loadKuotaNotifications();

            // Optional: Refresh notifications periodically (e.g., every 60 seconds)
            // setInterval(function() {
            //     loadNotifications();
            //     loadKuotaNotifications();
            // }, 60000); // 60,000 ms = 60 seconds
        });

        $(document).ready(function() {
            // Trigger modal saat link 'Company' diklik
            $('#triggerModal').on('click', function() {
                // Tampilkan modal
                $('#companyModal').modal('show');

                // Ambil daftar perusahaan menggunakan AJAX
                $.ajax({
                    url: '/get-companies', // Endpoint untuk mengambil daftar perusahaan
                    method: 'GET',
                    success: function(data) {
                        // Reset dropdown
                        $('#selectCompany').empty();
                        // $('#selectCompany').append(
                        //     '<option value="">Select a Company</option>'
                        // );

                        // Ambil active_company_id dari respons
                        var activeCompanyId = data.active_company_id;

                        // Iterasi perusahaan dan tambahkan opsi ke dropdown
                        data.companies.forEach(function(company) {
                            var selected = parseInt(company.id) === parseInt(
                                activeCompanyId) ? 'selected' : '';
                            $('#selectCompany').append(
                                '<option value="' + company.id + '" ' + selected +
                                '>' + company.name + '</option>'
                            );
                        });
                    },
                    error: function(error) {
                        console.log('Error:', error);
                    }
                });
            });

            $('#saveCompanyBtn').on('click', function() {
                var companyId = $('#selectCompany').val();
                if (companyId) {

                    $.ajax({
                        url: '/set-active-company',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            active_company_id: companyId
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#companyModal').modal('hide');
                                location
                                    .reload();
                                showMessage('success', 'Company selected successfully!');
                            } else {
                                showMessage('error',
                                    'Failed to select company. Please try again.');
                            }
                        },
                        error: function(error) {
                            console.log('Error:', error);
                            showMessage('error', 'An error occurred. Please try again.');
                        }
                    });
                } else {
                    showMessage('warning', 'Please select a company.');
                }
            });
        });
    </script>

    @yield('script')
</body>

</html>
