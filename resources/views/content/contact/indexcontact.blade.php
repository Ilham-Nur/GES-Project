@extends('layout.main')

@section('title', 'Content | Contact')

@section('main')

<!---Container Fluid-->
<div class="container-fluid" id="container-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contact</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Content</li>
            <li class="breadcrumb-item active" aria-current="page">Contact</li>
        </ol>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div id="containerContact" class="table-responsive px-3">
                    </div>
                    <div class="mt-3">
                        <label for="emailContact" class="form-label fw-bold">Email</label>
                        <input type="text" class="form-control" id="emailContact"  value="{{ isset($contactData->email) ? $contactData->email : '' }}"
                            placeholder="Masukkan email">
                        <div id="emailContactError" class="text-danger mt-1 d-none">Silahkan isi Email</div>
                    </div>
                    <div class="mt-3">
                        <label for="phoneContact" class="form-label fw-bold">Phone (main)</label>
                        <input type="text" class="form-control" id="phoneContact"  value="{{ isset($contactData->phone) ? $contactData->phone : '' }}"
                            placeholder="Masukkan phone ">
                        <div id="phoneContactError" class="text-danger mt-1 d-none">Silahkan isi Nomor</div>
                    </div>
                    <div class="mt-3">
                        <label for="phonesContact" class="form-label fw-bold">Phone (second)</label>
                        <input type="text" class="form-control" id="phonesContact"  value="{{ isset($contactData->phones) ? $contactData->phones : '' }}"
                            placeholder="Masukkan phone ">
                        <div id="phonesContactError" class="text-danger mt-1 d-none">Silahkan isi Nomor</div>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" id="saveContact">
                        <span class="pr-3"><i class="fas fa-save"></i></span> Save
                    </button>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="m-0 font-weight-bold text-primary p-2">Preview</h6>
                    <div class="preview" id="previewContainer"
                        style="border:1px solid black; height: auto; border-radius:10px;">
                        @if($contactData)
                                <p style="margin-left:30px;">{{ $contactData->email ?? '' }}</p>
                                <p style="margin-left:30px;">{{ $contactData->phone ?? '' }}</p>
                                <p style="margin-left:30px;">{{ $contactData->phones ?? '' }}</p>
                        @else
                            <p class="p-3">No content available</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="ckeditor/ckeditor5.js"></script>
<!---Container Fluid-->
@endsection
@section('script')
<script>
    $(document).ready(function () {
        $(document).on('click', '#saveContact', function (e) {
            e.preventDefault();

            var emailContact = $('#emailContact').val().trim();
            var phoneContact = $('#phoneContact').val().trim();
            var phonesContact = $('#phoneContact').val().trim();
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            var isValid = true;

            if (alamatContact === '') {
                $('#alamatContactError').removeClass('d-none');
                isValid = false;
            } else {
                $('#alamatContactError').addClass('d-none');
            }
            if (emailContact === '') {
                $('#emailContactError').removeClass('d-none');
                isValid = false;
            } else {
                $('#emailContactError').addClass('d-none');
            }
            if (phoneContact === '') {
                $('#phoneContactError').removeClass('d-none');
                isValid = false;
            } else {
                $('#phoneContactError').addClass('d-none');
            }
            if (phonesContact === '') {
                $('#phonesContactError').removeClass('d-none');
                isValid = false;
            } else {
                $('#phonesContactError').addClass('d-none');
            }



            
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
                        var formData = new FormData();
                        formData.append('emailContact', emailContact);
                        formData.append('phoneContact', phoneContact);
                        formData.append('phonesContact', phonesContact);
                        formData.append('_token', csrfToken);
                        Swal.fire({
                            title: 'Loading...',
                            text: 'Please wait while we process your data contact.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            type: "POST",
                            url: "{{ route('addContact') }}",
                            data: formData,
                            contentType: false,
                            processData: false,
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
                                if (response.status === 'success') {

                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: response.message,
                                        icon: "success"
                                    }).then(() => {
                                        var previewContainer = $('#previewContainer');
                                        previewContainer.html('');
                                        if (response.data.emailContact) {
                                            previewContainer.append('<p style="margin-left:30px;">' + response.data.emailContact + '</p>');
                                        }
                                      
                                        if (response.data.phoneContact) {
                                            previewContainer.append('<p style="margin-left:30px;">' + response.data.phoneContact + '</p>');
                                        }
                                      
                                        if (response.data.phonesContact) {
                                            previewContainer.append('<p style="margin-left:30px;">' + response.data.phonesContact + '</p>');
                                        }
                                      
                                      
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Gagal Menambahkan Data",
                                        text: response.message,
                                        icon: "error"
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Terjadi kesalahan: " + error,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: "Periksa Input",
                    text: "Tolong periksa input yang kosong atau tidak valid",
                    icon: "warning"
                });
            }
        });

    });

    document.addEventListener("DOMContentLoaded", function () {
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    });



</script>
@endsection