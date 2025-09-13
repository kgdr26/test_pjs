@if (Route::currentRouteName() == 'home')
    <script src="{{asset('assets/thems/js/dropzone.min.js')}}"></script>
    <script src="{{asset('assets/thems/js/sweetalert2.min.js')}}"></script>
    <script src="{{asset('assets/thems/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/thems/js/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/thems/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/thems/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/thems/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('assets/main/home.js')}}"></script>
@endif


<script>
    function formatRupiah(angka, prefix = 'Rp ') {
        angka = angka.toString();
        let numberString = angka.replace(/[^,\d]/g, ''), // Hapus karakter selain angka dan koma
            split = numberString.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        // Tambahkan titik jika angka memiliki ribuan
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix + rupiah;
    }

    function parseRupiahToInt(rupiah) {
        if (!rupiah) return 0; // Jika nilai kosong, kembalikan 0
        return parseInt(rupiah.replace(/[^,\d]/g, '').replace(',', ''), 10);
    }
</script>
