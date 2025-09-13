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

class OutboundController extends Controller
{

    function outbound(): object {
        $pic = DB::table('mst_pic')->where('is_active', 1)->get();
        $merk = DB::table('mst_merk')->where('is_active', 1)->get();
        $utility = DB::table('mst_utility')->where('is_active', 1)->get();
        $unit = DB::table('mst_unit')->where('is_active', 1)->get();
        $location = DB::table('mst_location')->where('is_active', 1)->get();
        $tools = DB::table('mst_tools')->where('is_active', 1)->get();
        $data = array(
            'pic' => $pic,
            'merk' => $merk,
            'utility' => $utility,
            'unit' => $unit,
            'location' => $location,
            'tools' => $tools,
            'title' => 'Outbound Tools',
        );

        return view('Outbound.list')->with($data);
    }

    function listdata(Request $request): object{
        $query = DB::table('trx_outbound as ti')
            ->select(
                'ti.id_transaction',
                'ti.date',
                'ti.no_pr',
                'ti.no_mr',
                'ti.qty',
                'ti.stok_old',
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
                'mpd.name as pic_deliver_name',
                'mpa.name as pic_accept_name',
                'ti.last_update as last_updated'
            )
            ->leftJoin('mst_tools as mt', 'ti.id_tools', '=', 'mt.id')
            ->leftJoin('mst_merk as mm', 'mt.id_merk', '=', 'mm.id')
            ->leftJoin('mst_utility as mu', 'mt.id_utility', '=', 'mu.id')
            ->leftJoin('mst_unit as munit', 'mt.id_unit', '=', 'munit.id')
            ->leftJoin('mst_location as ml', 'mt.id_location', '=', 'ml.id')
            ->leftJoin('users as us', 'ti.update_by', '=', 'us.id')
            ->leftJoin('mst_pic as mpd', 'ti.pic_deliver', '=', 'mpd.id')
            ->leftJoin('mst_pic as mpa', 'ti.pic_accept', '=', 'mpa.id')
            ->where('mt.is_active', 1);

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->input('datasearch')) {
                    $search = $request->input('datasearch');
                    $query->where(function ($q) use ($search) {
                        $q->where('mt.kode', 'like', "%".$search."%")
                          ->orWhere('ti.id_transaction', 'like', "%".$search."%")
                          ->orWhere('ti.date', 'like', "%".$search."%")
                          ->orWhere('ti.no_pr', 'like', "%".$search."%")
                          ->orWhere('ti.no_mr', 'like', "%".$search."%")
                          ->orWhere('ti.qty', 'like', "%".$search."%")
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
            ->rawColumns(['action','kode_image'])
            ->make(true);
    }

    function outbound_data_tools(Request $request): object{
        $id = $request->id_tools;
        $query = DB::table('mst_tools')->select('*')->where('id', $id)->first();

        try {
            return response()->json(['data' => $query], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }


    function generate(Request $request): object{
        $request->validate([
            'id_tools' => 'required',
            'pic_deliver' => 'required',
            'pic_accept' => 'required',
            'qty' => 'required',
            'date' => 'required',
        ]);

        $query = [];
        $query['tools'] = DB::table('mst_tools as mt')
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
            ->where('mt.id', $request->id_tools)->first();

            $query['pic_deliver'] = DB::table('mst_pic')->select('*')->where('id', $request->pic_deliver)->first();
            $query['pic_accept'] = DB::table('mst_pic')->select('*')->where('id', $request->pic_accept)->first();

            $jml = DB::table('trx_outbound')->count();
            $query['id_transaction'] = 'TO-BBN.'.str_pad(($jml+1), 3, '0', STR_PAD_LEFT) . "\n";

        try {
            return response()->json(['data' => $query], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    function save(Request $request): object{
        $request->validate([
            'id_transaction' => 'required',
            'date' => 'required',
            'pic_deliver' => 'required',
            'pic_accept' => 'required',
            'no_pr' => 'required',
            'no_mr' => 'required',
            'id_tools' => 'required',
            'qty' => 'required',
        ]);

        $tools = DB::table('mst_tools')->where('id', $request->id_tools)->first();

        try {
            DB::table('trx_outbound')->insert([
                'id_transaction' => $request->id_transaction,
                'date' => $request->date,
                'pic_deliver' => $request->pic_deliver,
                'pic_accept' => $request->pic_accept,
                'no_pr' => $request->no_pr,
                'no_mr' => $request->no_mr,
                'id_tools' => $request->id_tools,
                'qty' => $request->qty,
                'stok_old' => $tools->stok,
                'is_active' => 1,
                'update_by' => Auth::id(),
            ]);

            $id = $request->id_tools;
            $stok = $tools->stok-$request->qty;
            $data = array(
                'stok' => $stok,
                'update_by' => Auth::id(),
            );

            // Update data ke database
            DB::table('mst_tools')->where('id', $id)->update($data);

            return response()->json(['message' => 'Data berhasil disimpan!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
