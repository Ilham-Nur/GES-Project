@extends('layout.main')

@section('title', 'About')

@section('main')
<div class="modal fade" id="modalGambar" tabindex="-1" role="dialog"
     aria-labelledby="modalGambarTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGambarTitle">Gambar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mt-3">
                    @if($aboutData && $aboutData->Image_AboutUs)
                        <img src="{{ asset('storage/images/' . $aboutData->Image_AboutUs) }}" width="400px">
                    @else
                        <p>No image available</p>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!---Container Fluid-->
<div class="container-fluid" id="container-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">About</h1>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="m-0 font-weight-bold text-primary">About</h6>
                    <div id="containerAbout" class="table-responsive px-3">
                    </div>
                    <div class="mt-3">
                        <label for="imageAbout" class="form-label fw-bold p-1">Image</label>
                        <input type="file" class="form-control" id="imageAbout" value="">
                        <div id="imageAboutError" class="text-danger mt-1 d-none">Silahkan isi Gambar</div>
                    </div>
                    <div class="input-group pt-2 mt-3">
                        <label for="parafAbout" class="form-label fw-bold p-3">Content</label>
                        <textarea id="parafAbout" class="form-control" aria-label="With textarea">{{ $aboutData->Paraf_AboutUs ?? '' }}</textarea>
                    </div>
                    <div id="parafAboutError" class="text-danger mt-1 d-none">Silahkan isi</div>
                    <button type="button" class="btn btn-primary mt-3" id="saveAbout">
                        <span class="pr-3"><i class="fas fa-save"></i></span> Save
                    </button>
                    <button type="button" class="btn btn-secondary mt-3" data-toggle="modal" data-target="#modalPreview" id="#modalCenter">
                        <span class=""><i class="fas fa-eye"></i></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="m-0 font-weight-bold text-primary">About</h6>
                    <div id="containerAbout" class="table-responsive px-3">
                    </div>
                    <div class="mt-3">
                        <label for="imageAbout" class="form-label fw-bold p-1">Image</label>
                        <input type="file" class="form-control" id="imageAbout" value="">
                        <div id="imageAboutError" class="text-danger mt-1 d-none">Silahkan isi Gambar</div>
                    </div>
                    <div class="input-group pt-2 mt-3">
                        <label for="parafAbout" class="form-label fw-bold p-3">Content</label>
                        <textarea id="parafAbout" class="form-control" aria-label="With textarea">{{ $aboutData->Paraf_AboutUs ?? '' }}</textarea>
                    </div>
                    <div id="parafAboutError" class="text-danger mt-1 d-none">Silahkan isi</div>
                    <button type="button" class="btn btn-primary mt-3" id="saveAbout">
                        <span class="pr-3"><i class="fas fa-save"></i></span> Save
                    </button>
                    <button type="button" class="btn btn-secondary mt-3" data-toggle="modal" data-target="#modalPreview" id="#modalCenter">
                        <span class=""><i class="fas fa-eye"></i></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!---Container Fluid-->
@endsection

@section('script')
<script>
$(document).ready(function() {
    $(document).on('click', '#saveAbout', function(e) {
        e.preventDefault(); // Prevent default button behavior

        // Ambil nilai input
        var parafAbout = $('#parafAbout').val().trim();
        var imageAbout = $('#imageAbout')[0].files[0];

        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        var isValid = true;

        // Validasi input
        if (parafAbout === '') {
            $('#parafAboutError').removeClass('d-none');
            isValid = false;
        } else {
            $('#parafAboutError').addClass('d-none');
        }
        if (!imageAbout) {
            $('#imageAboutError').removeClass('d-none');
            isValid = false;
        } else {
            $('#imageAboutError').addClass('d-none');
        }

        // Jika semua input valid, lanjutkan aksi simpan
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
                    formData.append('parafAbout', parafAbout);
                    formData.append('imageAbout', imageAbout);
                    formData.append('_token', csrfToken);

                    $.ajax({
                        type: "POST",
                        url: "{{ route('addAbout') }}",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success"
                                }).then(() => {
                                    // Refresh the page or update content
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal Menambahkan Data",
                                    text: response.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function(xhr, status, error) {
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
                text: "Tolong periksa input yang kosong",
                icon: "warning"
            });
        }
    });
});
</script>
@endsection