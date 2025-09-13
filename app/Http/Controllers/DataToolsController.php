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
use App\Models\user;
use DataTables;
use Auth;
use Hash;
use Redirect;
use DB;

class DataToolsController extends Controller
{

    function datatools(): object {
        $merk = DB::table('mst_merk')->where('is_active', 1)->get();
        $utility = DB::table('mst_utility')->where('is_active', 1)->get();
        $unit = DB::table('mst_unit')->where('is_active', 1)->get();
        $location = DB::table('mst_location')->where('is_active', 1)->get();
        $data = array(
            'merk' => $merk,
            'utility' => $utility,
            'unit' => $unit,
            'location' => $location,
            'title' => 'Data Tools',
        );

        return view('DataTools.list')->with($data);
    }

    function listdata(Request $request): object{
        $query = DB::table('mst_tools as mt')
            ->select(
                'mt.id as tool_id',
                'mt.kode as tool_code',
                'mt.name as tool_name',
                'mt.image as tool_image',
                'mm.name as merk_name',
                'mu.name as utility_name',
                'munit.name as unit_name',
                'ml.name as location_name',
                'mt.price as tool_price',
                'mt.stok as tool_stock',
                'mt.is_active as tool_is_active',
                'us.name as user_update',
                'mt.last_update as last_updated'
            )
            ->leftJoin('mst_merk as mm', 'mt.id_merk', '=', 'mm.id')
            ->leftJoin('mst_utility as mu', 'mt.id_utility', '=', 'mu.id')
            ->leftJoin('mst_unit as munit', 'mt.id_unit', '=', 'munit.id')
            ->leftJoin('mst_location as ml', 'mt.id_location', '=', 'ml.id')
            ->leftJoin('users as us', 'mt.update_by', '=', 'us.id')
            ->where('mt.is_active', 1);

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->input('datasearch')) {
                    $search = $request->input('datasearch');
                    $query->where(function ($q) use ($search) {
                        $q->where('mt.kode', 'like', "%".$search."%")
                          ->orWhere('mt.name', 'like', "%".$search."%")
                          ->orWhere('mm.name', 'like', "%".$search."%")
                          ->orWhere('mu.name', 'like', "%".$search."%")
                          ->orWhere('munit.name', 'like', "%".$search."%")
                          ->orWhere('ml.name', 'like', "%".$search."%")
                          ->orWhere('mt.price', 'like', "%".$search."%")
                          ->orWhere('mt.stok', 'like', "%".$search."%")
                          ->orWhere('us.name', 'like', "%".$search."%")
                          ->orWhere('us.name', 'like', "%".$search."%")
                          ->orWhere('mt.last_update', 'like', "%".$search."%");
                    });
                }
            })
            ->addColumn('kode_image', function ($row) {
                $toolsImage = $row->tool_image;
                return '
                    <div class="d-flex align-items-center gap-6">
                        <img src="' . asset('assets/img/tools/'.$toolsImage) . '" width="45" class="rounded-circle" />
                        <h6 class="mb-0">' . e($row->tool_code) . '</h6>
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center">
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-secondary d-flex align-items-center me-3 pe-3" data-name="edit" data-item="' . $row->tool_id . '">
                            <i class="ti ti-pencil fs-4 me-2"></i>
                            Edit
                        </button>
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-danger d-flex align-items-center pe-3" data-name="delete" data-item="' . $row->tool_id . '">
                            <i class="ti ti-trash fs-4 me-2"></i>
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action','kode_image'])
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
            $storagePath = public_path('assets/img/tools');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0775, true);
                Log::info('Storage folder created:', ['path' => $storagePath]);
            }

            // Simpan file ke folder public/assets/img/tools
            $file->move($storagePath, $uniqueName);
            Log::info('File moved successfully:', ['path' => $storagePath . '/' . $uniqueName]);

            return response()->json([
                'path' => asset('assets/img/tools/' . $uniqueName),
                'name' => $uniqueName,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error during file upload:', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    function generate(Request $request): object{
        $id_utility = $request->id_utility;
        $id_location = $request->id_location;

        $utility = DB::table('mst_utility')->where('id', $id_utility)->first();

        $jml = DB::table('mst_tools')
                ->where('id_location', $id_location)
                ->where('kode', 'like', '%'.$utility->kode.'%')
                ->count();

        $kode = $utility->kode.str_pad(($jml+1), 3, '0', STR_PAD_LEFT) . "\n";
        $arr['kode'] = $kode;
        return response($arr);
    }

    function save(Request $request): object{
        try {
            // Validasi data
            $request->validate([
                'kode' => 'required|string|max:50|unique:mst_tools,kode',
                'name' => 'required|string|max:100',
                'id_merk' => 'required|integer',
                'id_unit' => 'required|integer',
                'id_location' => 'required|integer',
                'price' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
            ]);

            // Insert data ke database
            DB::table('mst_tools')->insert([
                'kode' => $request->kode,
                'name' => $request->name,
                'image' => $request->image ?? 'default.jpg',
                'id_merk' => $request->id_merk,
                'id_utility' => $request->id_utility,
                'id_unit' => $request->id_unit,
                'id_location' => $request->id_location,
                'price' => $request->price,
                'stok' => $request->stok,
                'is_active' => 1,
                'update_by' => Auth::id(),
            ]);

            return response()->json(['message' => 'Data berhasil disimpan!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function show(Request $request): object{
        $id = $request->id;
        $query = DB::table('mst_tools')->select('*')->where('id', $id)->first();

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
                'id' => 'required|integer',
                'name' => 'required|string|max:100',
                'id_merk' => 'required|integer',
                'id_unit' => 'required|integer',
                'id_location' => 'required|integer',
                'price' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
            ]);

            $id = $request->id;

            $data = array(
                'name' => $request->name,
                'image' => $request->image ?? 'default.jpg',
                'id_merk' => $request->id_merk,
                'id_utility' => $request->id_utility,
                'id_unit' => $request->id_unit,
                'id_location' => $request->id_location,
                'price' => $request->price,
                'stok' => $request->stok,
                'update_by' => Auth::id(),
            );

            // Update data ke database
            DB::table('mst_tools')->where('id', $id)->update($data);

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

            $data = array(
                'is_active' => 0,
                'update_by' => Auth::id(),
            );

            // Update data ke database
            DB::table('mst_tools')->where('id', $id)->update($data);

            return response()->json(['message' => 'Data berhasil delete!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
