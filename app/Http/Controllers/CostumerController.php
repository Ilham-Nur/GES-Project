<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostumerController extends Controller
{
    public function index() {


        return view('masterdata.costumer.indexmastercostumer');
    }

    public function getlistCostumer(Request $request)
    {
        $txSearch = '%' . strtoupper(trim($request->txSearch)) . '%';

        $q = "SELECT id,
                    nama_pembeli,
                    alamat,
                    no_wa,
                    sisa_poin,
                    category
                FROM tbl_pembeli
        ";

        // dd($q);

        $data = DB::select($q);

        $output = '<table class="table align-items-center table-flush table-hover" id="tableCostumer">
                        <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. Telp</th>
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                <tbody>';
        foreach ($data as $item) {

            $categoryCell = ($item->category == 'VIP')
            ? '<td><span class="badge badge-primary">VIP</span></td>'
            : '<td class="">' . ($item->category ?? '-') . '</td>';

            $showPointButton = ($item->category == 'VIP')
            ? '<a class="btn btn-sm btn-primary text-white" data-poin="' .$item->sisa_poin .'">Show point</a>'
            : '';

            $output .=
                '
                <tr>
                    <td class="">' . ($item->nama_pembeli ?? '-') .'</td>
                    <td class="">' . ($item->alamat ?? '-') .'</td>
                    <td class="">' . ($item->no_wa ?? '-') .'</td>
                    ' . $categoryCell . '
                   <td>
                         ' . $showPointButton . '
                        <a  class="btn btnUpdateCustomer btn-sm btn-secondary text-white" data-id="' .$item->id .'" data-nama="' .$item->nama_pembeli .'" data-alamat="' .$item->alamat .'" data-notelp="' .$item->no_wa .'" data-category="' .$item->category .'"><i class="fas fa-edit"></i></a>
                        <a  class="btn btnDestroyCustomer btn-sm btn-danger text-white" data-id="' .$item->id .'" ><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            ';
        }

        $output .= '</tbody></table>';
         return $output;

    }

    public function addCostumer(Request $request)
    {

        $namacostumer = $request->input('namaCostmer');
        $alamatcostumer = $request->input('alamatCustomer');
        $notlponcostumer = $request->input('noTelpon');
        $categorycostumer = $request->input('CategoryCustomer');


        // dd($namacostumer, $alamatcostumer, $notlponcostumer, $categorycostumer);

        try {
            DB::table('tbl_pembeli')->insert([
                'nama_pembeli' => $namacostumer,
                'no_wa' => $notlponcostumer,
                'alamat' => $alamatcostumer,
                'category' => $categorycostumer,

            ]);

            // Mengembalikan respons JSON jika berhasil
            return response()->json(['status' => 'success', 'message' => 'Pelanggan berhasil ditambahkan'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menambahkan pelanggan: ' . $e->getMessage()], 500);
        }


    }


    public function updateCostumer(Request $request)
    {
        $id = $request->input('id');
        $namacostumer = $request->input('namaCostmer');
        $alamatcostumer = $request->input('alamatCustomer');
        $notlponcostumer = $request->input('noTelpon');
        $categorycostumer = $request->input('CategoryCustomer');

        try {
            DB::table('tbl_pembeli')
            ->where('id', $id)
            ->update([
               'nama_pembeli' => $namacostumer,
                'no_wa' => $notlponcostumer,
                'alamat' => $alamatcostumer,
                'category' => $categorycostumer,
            ]);

            // Mengembalikan respons JSON jika berhasil
            return response()->json(['status' => 'success', 'message' => 'Data Pelanggan berhasil diupdate'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal Mengupdate Data Pelanggan: ' . $e->getMessage()], 500);
        }

    }

    public function destroyCostumer(Request $request)
    {
        $id = $request->input('id');

        try {
            DB::table('tbl_pembeli')
                ->where('id', $id)
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'Data Pelanggan berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}