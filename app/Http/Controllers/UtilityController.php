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

class UtilityController extends Controller
{

    function datautility(): object {
        $data = array(
            'title' => 'Data Utility',
        );

        return view('DataUtility.list')->with($data);
    }

    function listdata(Request $request): object{
        $query = DB::table('mst_utility as mm')
            ->select(
                'mm.id',
                'mm.kode',
                'mm.name',
                'mm.last_update',
                'us.name as user_update'
            )
            ->leftJoin('users as us', 'mm.update_by', '=', 'us.id')
            ->where('mm.is_active', 1);

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->input('datasearch')) {
                    $search = $request->input('datasearch');
                    $query->where(function ($q) use ($search) {
                        $q->where('mm.kode', 'like', "%".$search."%")
                          ->orWhere('mm.name', 'like', "%".$search."%")
                          ->orWhere('mm.last_update', 'like', "%".$search."%")
                          ->orWhere('us.name', 'like', "%".$search."%");
                    });
                }
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center">
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-secondary d-flex align-items-center me-3 pe-3" data-name="edit" data-item="' . $row->id . '">
                            <i class="ti ti-pencil fs-4 me-2"></i>
                            Edit
                        </button>
                        <button type="button" class="justify-content-center w-100 btn btn-sm mb-1 btn-danger d-flex align-items-center pe-3" data-name="delete" data-item="' . $row->id . '">
                            <i class="ti ti-trash fs-4 me-2"></i>
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    function save(Request $request): object{
        try {
            // Validasi data
            $request->validate([
                'kode' => 'required|string|max:100',
                'name' => 'required|string'
            ]);

            // Insert data ke database
            DB::table('mst_utility')->insert([
                'kode' => $request->kode,
                'name' => $request->name,
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
        $query = DB::table('mst_utility')->select('*')->where('id', $id)->first();

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
                'kode' => 'required|string|max:100',
                'name' => 'required|string',
            ]);

            $id = $request->id;

            $data = array(
                'kode' => $request->kode,
                'name' => $request->name,
                'update_by' => Auth::id(),
            );

            // Update data ke database
            DB::table('mst_utility')->where('id', $id)->update($data);

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
            DB::table('mst_utility')->where('id', $id)->update($data);

            return response()->json(['message' => 'Data berhasil delete!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
