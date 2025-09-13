<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use \Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Hash;
use Redirect;
use DB;

class HomeController extends Controller
{

    function home(): object {
        $jns_hewan = DB::table('mst_jenis_hewan')->get();
        $data = array(
            'title' => 'Home',
            'jns_hewan' => $jns_hewan
        );

        return view('Home.list')->with($data);
    }

    function listdata(Request $request): object{
        $query = DB::table('trx_penitipan as tp')
            ->select(
                'tp.*',
                'mjh.mjh_name',
                 DB::raw("
                    TIMESTAMPDIFF(
                        DAY,
                        tp.tp_date_penitipan,
                        CASE 
                            WHEN tp.tp_status = 1 THEN NOW()
                            WHEN tp.tp_status = 2 THEN tp.tp_date_pengambilan
                        END
                    ) * mjh.mjh_biaya_perhari as biaya
                ")
            )
            ->leftJoin('mst_jenis_hewan as mjh', 'mjh.mjh_id', '=', 'tp.tp_jenis_hewan');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->input('datasearch')) {
                    $search = $request->input('datasearch');
                    $query->where(function ($q) use ($search) {
                        $q->where('tp.kode', 'like', "%".$search."%")
                          ->orWhere('tp.tp_name_hewan', 'like', "%".$search."%")
                          ->orWhere('tp.tp_name_pemilik', 'like', "%".$search."%")
                          ->orWhere('tp.tp_name_hewan', 'like', "%".$search."%")
                          ->orWhere('tp.tp_tlp_pemilik', 'like', "%".$search."%")
                          ->orWhere('tp.tp_email_pemilik', 'like', "%".$search."%")
                          ->orWhere('tp.tp_date_penitipan', 'like', "%".$search."%")
                          ->orWhere('tp.tp_date_pengambilan', 'like', "%".$search."%")
                          ->orWhere('mjh.mjh_name', 'like', "%".$search."%");
                    });
                }
            })
            ->addColumn('foto', function ($row) {
                $foto = $row->tp_foto;
                return '
                    <div class="d-flex align-items-center gap-6">
                        <img src="' . asset('assets/img/'.$foto) . '" width="45" class="rounded-circle" />
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center">
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-primary d-flex align-items-center me-3 pe-3" data-name="ambil" data-item="' . $row->tp_id . '">
                            <i class="ti ti-pencil fs-4 me-2"></i>
                            Ambil
                        </button>
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-secondary d-flex align-items-center me-3 pe-3" data-name="edit" data-item="' . $row->tp_id . '">
                            <i class="ti ti-pencil fs-4 me-2"></i>
                            Edit
                        </button>
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-danger d-flex align-items-center pe-3" data-name="delete" data-item="' . $row->tp_id . '">
                            <i class="ti ti-trash fs-4 me-2"></i>
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action','foto'])
            ->make(true);
    }


    function upload(Request $request): object{
        try {
            if (!$request->hasFile('file')) {
                Log::error('No file uploaded');
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $file = $request->file('file');
            Log::info('File details:', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            // Periksa ekstensi file
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                Log::error('File extension is empty');
                return response()->json(['error' => 'File extension is missing'], 400);
            }

            // Buat nama file unik
            $uniqueName = Str::uuid() . '.' . $extension;
            Log::info('Generated file name:', ['name' => $uniqueName]);

            // Periksa folder penyimpanan
            $storagePath = public_path('assets/img');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0775, true);
                Log::info('Storage folder created:', ['path' => $storagePath]);
            }

            // Simpan file ke folder public/assets/img/tools
            $file->move($storagePath, $uniqueName);
            Log::info('File moved successfully:', ['path' => $storagePath . '/' . $uniqueName]);

            return response()->json([
                'path' => asset('assets/img/' . $uniqueName),
                'name' => $uniqueName,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error during file upload:', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    protected function generateKode($tanggalPenitipan, $jenisId)
    {
        // format tanggal jadi YYMMDD
        $tanggal = Carbon::parse($tanggalPenitipan)->format('ymd');

        // ambil nama jenis hewan
        $jenis = DB::table('mst_jenis_hewan')
            ->where('mjh_id', $jenisId)
            ->value('mjh_name');

        // hitung jumlah data pada tanggal & jenis yg sama
        $count = DB::table('trx_penitipan')->count();

        // nomor urut = jumlah data + 1
        $noUrut = str_pad($count + 1, 2, '0', STR_PAD_LEFT);

        // hasil kode
        return $tanggal . '/' . strtoupper($jenis) . '/' . $noUrut;
    }

    function save(Request $request): object{
        try {
            // Validasi data
            $request->validate([
                'tp_name_hewan' => 'required|string|max:100',
                'tp_jenis_hewan' => 'required|integer',
                'tp_name_pemilik' => 'required|string|max:100',
                'tp_tlp_pemilik' => 'required|string|max:15',
                'tp_email_pemilik' => 'required|email|max:255',
                'tp_date_penitipan' => 'required|date',
                'tp_date_pengambilan' => 'required|date',
            ]);

            $tpKode = $this->generateKode($request->tp_date_penitipan, $request->tp_jenis_hewan);
            

            // Insert data ke database
            DB::table('trx_penitipan')->insert([
                'tp_kode'       => $tpKode,
                'tp_name_hewan' => $request->tp_name_hewan,
                'tp_jenis_hewan' => $request->tp_jenis_hewan,
                'tp_name_pemilik' => $request->tp_name_pemilik,
                'tp_tlp_pemilik' => $request->tp_tlp_pemilik,
                'tp_email_pemilik' => $request->tp_email_pemilik,
                'tp_date_penitipan' => $request->tp_date_penitipan,
                'tp_date_pengambilan' => $request->tp_date_pengambilan,
                'tp_status' => 1,
                'tp_foto' => $request->tp_foto ?? 'default.jpg',
            ]);

            return response()->json(['message' => 'Data berhasil disimpan!'], 200);
        } catch (\Exception $e) {
            return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], 500);
        }
    }

    function show(Request $request): object{
        $id = $request->id;
        $query = DB::table('trx_penitipan')
            ->select(
                'trx_penitipan.*',
                'mjh.mjh_name',
                DB::raw("TIMESTAMPDIFF(DAY, trx_penitipan.tp_date_penitipan, trx_penitipan.tp_date_pengambilan) * mjh.mjh_biaya_perhari as biaya")
            )
            ->leftJoin('mst_jenis_hewan as mjh', 'mjh.mjh_id', '=', 'trx_penitipan.tp_jenis_hewan')
            ->where('tp_id', $id)->first();

        try {
            return response()->json(['data' => $query], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function edit(Request $request): object{
        try {
            // Validasi data
            $request->validate([
                'tp_name_hewan' => 'required|string|max:100',
                'tp_jenis_hewan' => 'required|integer',
                'tp_name_pemilik' => 'required|string|max:100',
                'tp_tlp_pemilik' => 'required|string|max:15',
                'tp_email_pemilik' => 'required|email|max:255',
                'tp_date_penitipan' => 'required|date',
                'tp_date_pengambilan' => 'required|date',
            ]);

            $id = $request->tp_id;

            $data = array(
                'tp_name_hewan' => $request->tp_name_hewan,
                'tp_jenis_hewan' => $request->tp_jenis_hewan,
                'tp_name_pemilik' => $request->tp_name_pemilik,
                'tp_tlp_pemilik' => $request->tp_tlp_pemilik,
                'tp_email_pemilik' => $request->tp_email_pemilik,
                'tp_date_penitipan' => $request->tp_date_penitipan,
                'tp_date_pengambilan' => $request->tp_date_pengambilan,
                'tp_status' => 1,
                'tp_foto' => $request->tp_foto ?? 'default.jpg',
            );

            // echo '<pre>';
            // echo print_r($data);
            // echo '</pre>';
            // die;


            // Update data ke database
            DB::table('trx_penitipan')->where('tp_id', $id)->update($data);

            return response()->json(['message' => 'Data berhasil diubah!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function delete(Request $request): object{
        try {
            // Validasi data
            $request->validate([
                'id' => 'required|integer',
            ]);

            $id = $request->id;

            // Update data ke database
            DB::table('trx_penitipan')->where('tp_id', $id)->delete();

            return response()->json(['message' => 'Data berhasil delete!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
