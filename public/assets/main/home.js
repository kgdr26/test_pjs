$(document).ready(function () {
    $(".select2-add").select2({
        allowClear: false,
        dropdownParent: $("#modal_add")
    });

    $('[data-name="tp_date_pengambilan"]').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        container: '.form-card'
    });

    $('[data-name="tp_date_penitipan"]').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        container: '.form-card'
    });

    $(".select2-edit").select2({
        allowClear: false,
        dropdownParent: $("#modal_edit")
    });

    $('#list_table').DataTable({
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'listdatapenitipan',
            type: 'POST',
            data: function (d) {
                d.datasearch     = d.search.value;
            },
        },
        columns: [
            { data: 'DT_RowIndex', title: 'No', orderable: false, searchable: false },
            { data: 'foto', title: 'Foto', orderable: false, searchable: false },
            { data: 'tp_kode', title: 'Kode' },
            { data: 'tp_name_hewan', title: 'Nama Hewan' },
            { data: 'mjh_name', title: 'Jenis Hewan' },
            { data: 'tp_name_pemilik', title: 'Nama Pemilik' },
            { data: 'tp_tlp_pemilik', title: 'No Tlp' },
            { data: 'tp_email_pemilik', title: 'Email' },
            { data: 'tp_date_penitipan', title: 'Tgl Penitipan' },
            { data: 'tp_date_pengambilan', title: 'Tgl Pengambilan' },
            { data: 'biaya', title: 'Price', render: $.fn.dataTable.render.number('.', '', 0, 'Rp. ') },
            { data: 'tp_status', title: 'Status' },
            { data: 'action', title: 'Action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']]
    });

});

Dropzone.autoDiscover = false;
var myDropzone = new Dropzone("#imageDropzone", {
    url: "upload_image",
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    acceptedFiles: "image/png,image/jpg,image/jpeg",
    maxFiles: 1,
    addRemoveLinks: true,
    dictRemoveFile: "Remove file",
    init: function () {

        var dropzoneInstance = this;

        // Fungsi untuk menetapkan gambar manual ke Dropzone
        dropzoneInstance.setExistingImage = function (imageUrl, imageName) {
            this.removeAllFiles(true);

            var mockFile = { name: imageName, size: 12345 }; // Mock file
            this.emit("addedfile", mockFile);
            this.emit("thumbnail", mockFile, imageUrl);
            this.emit("complete", mockFile);
            mockFile.previewElement.classList.add("dz-success", "dz-complete");
            $('[data-name="tp_foto"]').val(imageName); // Set input value jika diperlukan
        };

        this.on("addedfile", function (file) {
            if (this.files.length > 1) {
                this.removeFile(this.files[0]); // Hapus file pertama
            }
        });

        this.on("removedfile", function (file) {
            $(file.previewElement).find(".dz-error-message").hide();
            // console.log("File removed:", file.name);
        });

        this.on("success", function (file, response) {
            // console.log("File uploaded successfully:", response.name);
            $('[data-name="tp_foto"]').val(response.name);
        });

        this.on("error", function (file, message) {
            $(file.previewElement).find(".dz-error-message").hide();
            console.error("Error uploading file:", message);
            message = JSON.stringify(message);
            myError(message);
        });
    },
});

$('button[data-name="add"]').on('click', function () {
    myDropzone.removeAllFiles(true);

    $('[data-name="save-edit"').hide();
    $('[data-name="save-add"').show();
    $('#show_kode').hide();

    $('.form-add :input').each(function () {
        if ($(this).is(':text')) {
            $(this).val('');
        } else if ($(this).is('select')) {
            $(this).prop('selectedIndex', 0);
            $(this).trigger('select');
        } else if ($(this).is(':hidden  ')) {
            $(this).val('');
        }
    });
    $('#modal_add').modal('show');
});

$('[data-name="save-add"]').on('click', function () {
    var formData = {
        tp_name_hewan: $('[data-name="tp_name_hewan"]').val(),
        tp_jenis_hewan: $('[data-name="tp_jenis_hewan"]').val(),
        tp_name_pemilik: $('[data-name="tp_name_pemilik"]').val(),
        tp_tlp_pemilik: $('[data-name="tp_tlp_pemilik"]').val(),
        tp_email_pemilik: $('[data-name="tp_email_pemilik"]').val(),
        tp_date_penitipan: $('[data-name="tp_date_penitipan"]').val(),
        tp_foto: $('[data-name="tp_foto"]').val(),

        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: 'save_data',
        type: 'POST',
        data: formData,
        success: function (response) {
            $('#list_table').DataTable().ajax.reload();
            $('#modal_confirm').modal('hide');
            $('.form-card :input').each(function () {
                if ($(this).is(':text')) {
                    $(this).val('');
                } else if ($(this).is('select')) {
                    $(this).prop('selectedIndex', 0);
                    $(this).trigger('change');
                } else if ($(this).is(':hidden  ')) {
                    $(this).val('');
                }
            });
            toastr.success("Tool data added successfully !", "Success Save Data.");
            $('#modal_add').modal('hide');
        },
        error: function (xhr) {
            if (xhr.responseJSON) {
                // kalau ada validasi error
                if (xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    // tampilkan error per field
                    $.each(errors, function (field, messages) {
                        console.log(field + " : " + messages[0]);
                        myError(messages[0]); // ambil pesan pertama tiap field
                    });
                } else {
                    // kalau ada error umum
                    console.log(xhr.responseJSON.message);
                    myError(xhr.responseJSON.message);
                }
            } else {
                // fallback kalau gak ada responseJSON
                console.log("Terjadi kesalahan tidak terduga");
                myError("Terjadi kesalahan tidak terduga");
            }
        }
    });

});

$(document).on('click', '[data-name="edit"]', function () {
    $('[data-name="save-add"').hide();
    $('[data-name="save-edit"').show();
    $('#show_kode').show();
    myDropzone.removeAllFiles(true);
    var baseUrl = $('meta[name="base-url"]').attr('content');
    var id = $(this).data('item');
    $.ajax({
        url: 'show_data',
        type: 'POST',
        data: {
            id: id,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            // console.log(response);

             $('[data-name="tp_name_hewan"]').val(response.data.tp_name_hewan);
             $('[data-name="tp_jenis_hewan"]').val(response.data.tp_jenis_hewan).trigger("change");
             $('[data-name="tp_name_pemilik"]').val(response.data.tp_name_pemilik);
             $('[data-name="tp_tlp_pemilik"]').val(response.data.tp_tlp_pemilik);
             $('[data-name="tp_email_pemilik"]').val(response.data.tp_email_pemilik);
             $('[data-name="tp_date_penitipan"]').val(response.data.tp_date_penitipan);
             $('[data-name="tp_foto"]').val(response.data.tp_foto);
            $('[data-name="tp_id"]').val(response.data.tp_id);
            $('[data-name="tp_kode"]').val(response.data.tp_kode);
              

            var link_image = baseUrl + '/assets/img/' + response.data.tp_foto;

            myDropzone.setExistingImage(link_image, response.data.tp_foto);

            $('#modal_add').modal('show');
        },
        error: function (xhr) {
            var message = JSON.stringify(xhr);
            myError(message.statusText);
        }
    });
});

$('[data-name="save-edit"]').on('click', function () {
    var formData = {
        tp_id: $('[data-name="tp_id"]').val(),
        tp_name_hewan: $('[data-name="tp_name_hewan"]').val(),
        tp_jenis_hewan: $('[data-name="tp_jenis_hewan"]').val(),
        tp_name_pemilik: $('[data-name="tp_name_pemilik"]').val(),
        tp_tlp_pemilik: $('[data-name="tp_tlp_pemilik"]').val(),
        tp_email_pemilik: $('[data-name="tp_email_pemilik"]').val(),
        tp_date_penitipan: $('[data-name="tp_date_penitipan"]').val(),
        tp_foto: $('[data-name="tp_foto"]').val(),

        _token: $('meta[name="csrf-token"]').attr('content')
    };

    // console.log(formData);

    $.ajax({
        url: 'save_edit',
        type: 'POST',
        data: formData,
        success: function (response) {
            $('#list_table').DataTable().ajax.reload();
            $('#modal_confirm').modal('hide');
            $('.form-card :input').each(function () {
                if ($(this).is(':text')) {
                    $(this).val('');
                } else if ($(this).is('select')) {
                    $(this).prop('selectedIndex', 0);
                    $(this).trigger('change');
                } else if ($(this).is(':hidden  ')) {
                    $(this).val('');
                }
            });
            toastr.success("Tool data added successfully !", "Success Save Data.");
            $('#modal_add').modal('hide');
        },
        error: function (xhr) {
            if (xhr.responseJSON) {
                // kalau ada validasi error
                if (xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    // tampilkan error per field
                    $.each(errors, function (field, messages) {
                        console.log(field + " : " + messages[0]);
                        myError(messages[0]); // ambil pesan pertama tiap field
                    });
                } else {
                    // kalau ada error umum
                    console.log(xhr.responseJSON.message);
                    myError(xhr.responseJSON.message);
                }
            } else {
                // fallback kalau gak ada responseJSON
                console.log("Terjadi kesalahan tidak terduga");
                myError("Terjadi kesalahan tidak terduga");
            }
        }
    });

});


$(document).on('click', '[data-name="delete"]', function () {
    var id = $(this).data('item');

    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this tools!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete data!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'save_delete',
                type: 'POST',
                data: {
                    id: id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // console.log(formatRupiah(response.data.price));
                    $('#list_table').DataTable().ajax.reload();
                    toastr.success("Tool data delete successfully !", "Success Save Data.");
                },
                error: function (xhr) {
                    var message = JSON.stringify(xhr);
                    myError(message.statusText);
                }
            });
        }
    })
});