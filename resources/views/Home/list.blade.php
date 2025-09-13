@extends('main')
@section('content')

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="font-weight-medium  mb-0">Home</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a class="text-muted text-decoration-none" href="#">Home </a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="datatables">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title">List Data Penitipan</h4>

                    <div>
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-secondary d-flex align-items-center me-3" data-name="add">
                            <i class="ti ti-plus fs-4 me-2"></i>
                            Add
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="list_table" class="table table-striped table-bordered text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Kode</th>
                                <th>Nama Hewan</th>
                                <th>Jenis Hewan</th>
                                <th>Nama Pemilik</th>
                                <th>No Tlp</th>
                                <th>Email</th>
                                <th>Tgl Penitipan</th>
                                <th>Tgl Pengambilan</th>
                                <th>Biaya</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Add --}}
<div id="modal_add" class="modal fade" tabindex="-1" aria-labelledby="add-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-info text-white">
                <h4 class="modal-title text-white" id="add-modal">
                    Form Data
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body form-add">
                <div class="row">
                    <div class="col-md-4 col-xl-4 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-7">Image</h4>
                                <form action="{{route('upload_image')}}" id="imageDropzone" class="dropzone dz-clickable mb-2">
                                    <div class="dz-default dz-message">
                                    <button class="dz-button" type="button">Drop Thumbnail here to upload</button>
                                    </div>
                                </form>
                                <p class="fs-2 text-center mb-0">
                                    Set the product thumbnail image. Only *.png, *.jpg and *.jpeg image files
                                    are accepted.
                                </p>
                                <input type="hidden" data-name="tp_foto">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-xl-8 col-sm-12">
                        <div class="card">
                            <div class="card-body ">

                                <div class="form-group mb-3">
                                    <label for="tp_name_hewan" class="form-label">Nama Hewan</label>
                                    <input type="text" class="form-control" id="tp_name_hewan" data-name="tp_name_hewan" aria-describedby="tp_name_hewan" placeholder="Name Hewan">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tp_jenis_hewan" class="form-label">Jenis Hewan</label>
                                    <select class="select2-add form-control" id="tp_jenis_hewan" data-name="tp_jenis_hewan">
                                        <option>-- Select Location --</option>
                                        @foreach ($jns_hewan as $key => $val)
                                            <option value="{{$val->mjh_id}}">{{$val->mjh_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tp_name_pemilik" class="form-label">Nama Pemilik</label>
                                    <input type="text" class="form-control" id="tp_name_pemilik" data-name="tp_name_pemilik" aria-describedby="tp_name_pemilik" placeholder="Name Hewan">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tp_tlp_pemilik" class="form-label">Tlp Pemilik</label>
                                    <input type="text" class="form-control" id="tp_tlp_pemilik" data-name="tp_tlp_pemilik" aria-describedby="tp_tlp_pemilik" placeholder="Tlp Pemilik">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tp_email_pemilik" class="form-label">Email Pemilik</label>
                                    <input type="text" class="form-control" id="tp_email_pemilik" data-name="tp_email_pemilik" aria-describedby="tp_email_pemilik" placeholder="Email Pemilik">
                                </div>

                                <div class="form-group mb-3" id="show_kode">
                                    <label for="tp_kode" class="form-label">Kode Penitipan</label>
                                    <input type="text" class="form-control" id="tp_kode" data-name="tp_kode" aria-describedby="tp_kode" placeholder="Total Biaya" disabled>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tp_date_penitipan" class="form-label">Tanggal Menitipkan</label>
                                    <input type="text" class="form-control" id="tp_date_penitipan" data-name="tp_date_penitipan" aria-describedby="tp_date_penitipan" placeholder="Tanggal Menitipkan">
                                </div>

                                <input type="hidden" data-name="tp_id">


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn bg-info-subtle text-info" data-name="save-add">
                    Save Add
                </button>

                <button type="button" class="btn bg-info-subtle text-info" data-name="save-edit" style="display: none">
                    Save changes
                </button>
            </div>
        </div>
    </div>
</div>
{{-- End Modal Add --}}

@stop
