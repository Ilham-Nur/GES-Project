<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\JournalController;

use App\Models\Asset;
use App\Models\COA;
use App\Models\Jurnal;
use App\Models\JurnalItem;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use DateTime;
use Log;

use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
{
    protected $jurnalController;

    public function __construct(JournalController $jurnalController)
    {
        $this->jurnalController = $jurnalController;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Asset::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('asset_name', function ($row) {
                    return $row->asset_name;
                })
                ->editColumn('acquisition_date', function ($row) {
                    return Carbon::parse($row->acquisition_date)->format('d M Y');
                })
                ->editColumn('acquisition_price', function ($row) {
                    return number_format($row->acquisition_price, 0, '.', ',');
                })

                ->addColumn('action', function($row){
                    $action = '
                        <a href="' . route('asset.show', $row->id) . '" class="btn btn-sm btn-primary text-white">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a class="btn btnDestroyAsset btn-sm btn-danger text-white" data-id="' . $row->id . '"><i class="fas fa-trash"></i></a>';
                    return $action;
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting.asset.indexasset');
    }

    public function create()
    {
        $account = COA::where('set_as_group', 0)->get();

        return view('accounting.asset.indexbuatasset', compact('account'));
    }


    public function store(Request $request)
    {
        try {
            Log::info("Memulai proses tambah Asset dengan data: ", $request->all());
            // Validate the incoming request
            $validatedData = $request->validate([
                'asset_code' => 'nullable|string|max:255',
                'asset_name' => 'required|string|max:255',
                'acquisition_date' => 'required|date',
                'acquisition_price' => 'required|string|max:255',
                'depreciation_date' => 'required|date',
                'residue_value' => 'required|string|max:255',
                'estimated_age' => 'required|numeric',
                'depreciation_account' => 'nullable|exists:tbl_coa,id',
                'accumulated_account' => 'nullable|exists:tbl_coa,id',
            ]);

            DB::beginTransaction();
            $depreciationDate = $request->input('depreciation_date');
            $acquisitionDate = $request->input('acquisition_date');

            $formattedDep = (new DateTime($depreciationDate))->format('Y-m-d');
            $formattedAcq = (new DateTime($depreciationDate))->format('Y-m-d');

            $asset = new Asset();

            $asset->asset_code = $request->input('asset_code');
            $asset->asset_name = $request->input('asset_name');
            $asset->acquisition_date = $formattedAcq;
            $asset->acquisition_price = $request->input('acquisition_price');
            $asset->depreciation_date = $formattedDep;
            $asset->residue_value = $request->input('residue_value');
            $asset->estimated_age = $request->input('estimated_age');
            $asset->depreciation_account = $request->input('depreciation_account');
            $asset->accumulated_account = $request->input('accumulated_account');
            $asset->save();
            Log::info("Membuat jurnal untuk Asset " . $request->input('asset_name'));

            $this->createJournalForAsset($request, $asset);

            DB::commit();
            Log::info("Sukses menambahkan Asset: " . $request->input('asset_name'));

            return redirect()->back()->with('success', 'Asset berhasil ditambahkan');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Gagal menambahkan Asset: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan Asset gagal ditambahkan']);
        }
    }

    public function createJournalForAsset($request, $asset)
    {
        try {
            // Extract necessary data from the request and asset
            $request->merge(['code_type' => 'JU']);
            $noJournal = $this->jurnalController->generateNoJurnal($request)->getData()->no_journal;
            $jurnalDate = Carbon::parse($asset->depreciation_date)->endOfMonth()->format('Y-m-d');
            $noRef = $asset->asset_code ? $asset->asset_code : '-';
            $price = intval(str_replace(',', '', $asset->acquisition_price));
            $residue = intval(str_replace(',', '', $asset->residue_value));
            $age = $asset->estimated_age;
            $totalPerMonth = ($price - $residue) / $age;

            // Create Jurnal
            $jurnal = new Jurnal();
            $jurnal->no_journal = $noJournal;
            $jurnal->tipe_kode = 'JU';
            $jurnal->tanggal = $jurnalDate;
            $jurnal->no_ref = $noRef;
            $jurnal->status = 'Approve';
            $jurnal->description = "Jurnal untuk Asset " . $asset->asset_name;
            $jurnal->totaldebit = $totalPerMonth;
            $jurnal->totalcredit = $totalPerMonth;
            $jurnal->asset_id = $asset->id;
            $jurnal->save();

            // Debit Jurnal Item
            $jurnalItemDebit = new JurnalItem();
            $jurnalItemDebit->jurnal_id = $jurnal->id;
            $jurnalItemDebit->code_account = $asset->depreciation_account;
            $jurnalItemDebit->description = "Jurnal untuk Asset " . $asset->asset_name;
            $jurnalItemDebit->debit = $totalPerMonth;
            $jurnalItemDebit->credit = 0;
            $jurnalItemDebit->save();

            // Credit Jurnal Item
            $jurnalItemCredit = new JurnalItem();
            $jurnalItemCredit->jurnal_id = $jurnal->id;
            $jurnalItemCredit->code_account = $asset->accumulated_account;
            $jurnalItemCredit->description = "Jurnal untuk Asset " . $asset->asset_name;
            $jurnalItemCredit->debit = 0;
            $jurnalItemCredit->credit = $totalPerMonth;
            $jurnalItemCredit->save();

            Log::info("Jurnal untuk Asset " . $asset->asset_name . " berhasil dibuat.");


        } catch (Exception $e) {
            Log::error("Gagal membuat jurnal untuk Asset: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan Asset gagal ditambahkan']);
        }
    }


    public function show($id)
    {
        $asset = Asset::findOrFail($id);
        $dep_account = COA::where('id', $asset->depreciation_account)->first();
        $acc_account = COA::where('id', $asset->accumulated_account)->first();
        return view('accounting.asset.indexshowasset', [
            'assets' => $asset,
            'depreciation' => $dep_account,
            'accumulated' => $acc_account,
        ]);
    }
    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $asset = Asset::findOrFail($id);
            $jurnal = Jurnal::where('asset_id', $id)->get();
            if ($jurnal->isNotEmpty()) {

                foreach ($jurnal as $journal) {
                    JurnalItem::where('jurnal_id', $journal->id)->delete();
                    $journal->delete();
                }
            }
            $asset->delete();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



}
