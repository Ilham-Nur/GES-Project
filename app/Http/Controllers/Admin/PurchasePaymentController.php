<?php

namespace App\Http\Controllers\Admin;
use App\Exports\PaymentSupExport;
use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\PaymentSup;
use App\Models\PaymentSupInvoice;
use App\Models\SupInvoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\JournalController;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class PurchasePaymentController extends Controller
{

    protected $jurnalController;

    public function __construct(JournalController $jurnalController)
    {
        $this->jurnalController = $jurnalController;
    }

    public function index()
    {


        return view('vendor.purchasepayment.indexpurchasepayment');
    }


    public function getPaymentSupData(Request $request)
    {
        $query = DB::table('tbl_payment_sup as a')
            ->join('tbl_payment_invoice_sup as d', 'a.id', '=', 'd.payment_id')
            ->join('tbl_sup_invoice as b', 'd.invoice_id', '=', 'b.id')
            ->join('tbl_coa as c', 'a.payment_method_id', '=', 'c.id')
            ->select([
                'a.id',
                'a.kode_pembayaran',
                DB::raw("DATE_FORMAT(a.payment_date, '%d %M %Y') as tanggal_bayar"),
                'c.name as payment_method',
                DB::raw("SUM(d.amount) as total_amount")
            ])
            ->groupBy('a.id', 'a.kode_pembayaran', 'a.payment_date', 'c.name');

        if (!empty($request->status)) {
            $query->where('c.name', $request->status);
        }

        if (!empty($request->startDate) && !empty($request->endDate)) {
            $startDate = date('Y-m-d', strtotime($request->startDate));
            $endDate = date('Y-m-d', strtotime($request->endDate));
            $query->whereBetween('a.payment_date', [$startDate, $endDate]);
        }

        $query->orderBy('a.id', 'desc');
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return '<a class="btn btnviewPaymentDetails btn-primary btn-sm" data-id="' . $row->id . '"><i class="fas fa-eye text-white"></i><span class="text-white ml-1">Detail</span></a>
                <a class="btn btnEditPaymentPurchase btn-sm btn-secondary text-white" data-id="' . $row->id . '"><i class="fas fa-edit"></i></a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }




    public function addPurchasePayment()
    {

        $coas = COA::all();

        $listInvoice = SupInvoice::where('status_bayar', 'Belum Lunas')
            ->select('invoice_no')
            ->get();

        $listVendor = Vendor::select('id', 'name')
            ->get();

        return view('vendor.purchasepayment.buatpurchasepayment', [
            'listInvoice' => $listInvoice,
            'coas' => $coas,
            'listVendor' => $listVendor
        ]);
    }


    public function getSupInvoiceAmount(Request $request)
    {
        $invoiceSelect = $request->input('no_invoice');

        if (!$invoiceSelect || !is_array($invoiceSelect)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing invoice numbers',
            ], 400);
        }

        $invoices = DB::table('tbl_sup_invoice')
            ->select([
                'invoice_no',
                DB::raw("FORMAT(total_harga, 0) AS total_harga"),
                DB::raw("FORMAT(total_bayar, 0) AS total_bayar"),
                DB::raw("FORMAT(total_harga - total_bayar, 0) AS sisa_bayar"),
            ])
            ->whereIn('invoice_no', $invoiceSelect)
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoices not found',
            ]);
        }

        $invoiceNumbers = $invoices->pluck('invoice_no')->implode(';');
        $totalHarga = $invoices->sum(fn($item) => str_replace(',', '', $item->total_harga));
        $totalBayar = $invoices->sum(fn($item) => str_replace(',', '', $item->total_bayar));
        $sisaBayar = $invoices->sum(fn($item) => str_replace(',', '', $item->sisa_bayar));

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_numbers' => $invoiceNumbers,
                'total_harga' => number_format($totalHarga, 0, ',', '.'),
                'total_bayar' => number_format($totalBayar, 0, ',', '.'),
                'sisa_bayar' => number_format($sisaBayar, 0, ',', '.'),
            ],
        ]);
    }


    public function getInoviceByVendor(Request $request)
    {
        // // Get all request data
        // dd($request->all());

        $idVendor = $request->input('idVendor');
        $invoiceIds = $request->input('invoiceIds');
        // dd($invoiceIds);// Assuming invoiceIds is passed as an array

        if (!$idVendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor ID is required',
            ], 400);
        }

        if ($invoiceIds && count($invoiceIds) > 0) {
            $invoiceIds = PaymentSupInvoice::whereIn('invoice_id', $invoiceIds)
            ->pluck('invoice_id');

            $invoices = SupInvoice::whereIn('id', $invoiceIds)
                ->get(['id', 'invoice_no']);

            } else {
            $invoices = SupInvoice::where('vendor_id', $idVendor)
            ->where('status_bayar', 'Belum Lunas')
            ->get(['invoice_no']);
        }

        if ($invoices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No invoices found for this vendor',
            ]);
        }

        return response()->json([
            'success' => true,
            'invoices' => $invoices,
        ]);
    }




    public function store(Request $request)
    {


        // Validasi input
        $validated = $request->validate([
            'invoice' => 'required|array',
            // 'invoice.*' => 'required|string',
            'tanggalPayment' => 'required|date',
            'paymentAmount' => 'required|numeric',
            'paymentMethod' => 'required|integer',
            'keteranganPaymentSup' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.account' => 'required|integer',
            'items.*.item_desc' => 'required|string',
            'items.*.debit' => 'required|numeric',
        ]);


        DB::beginTransaction();

        try {
            $invoice = SupInvoice::where('invoice_no', $request->invoice)->firstOrFail();

            $vendor = Vendor::findOrFail($invoice->vendor_id);
            $vendorAccountId = $vendor->account_id;

            if (!$vendorAccountId) {
                throw new \Exception('Akun vendor tidak ditemukan.');
            }

            // $totalBayarBaru = $invoice->total_bayar + $request->paymentAmount;

            // if ($totalBayarBaru > $invoice->total_harga) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Total pembayaran melebihi total harga invoice.'
            //     ], 400);
            // }

            $tanggalPayment = Carbon::createFromFormat('d F Y', $request->tanggalPayment)->format('Y-m-d');
            $codeType = "VP";

            $currentYear = date('y');
            $lastPayment = PaymentSup::where('kode_pembayaran', 'like', $codeType . $currentYear . '%')
                ->orderBy('kode_pembayaran', 'desc')
                ->first();

            $currentYear = date('y');
            $lastSequence = PaymentSup::where('kode_pembayaran', 'like', $codeType . $currentYear . '%')
                ->max(DB::raw('CAST(SUBSTR(kode_pembayaran, -4) AS UNSIGNED)'));

            $newSequence = $lastSequence ? $lastSequence + 1 : 1;
            $newKodePembayaran = sprintf('%s%s%04d', $codeType, $currentYear, $newSequence);

            $payment = new PaymentSup();
            // $payment->invoice_id = $invoice->id;
            $payment->payment_date = $tanggalPayment;
            // $payment->amount = $request->paymentAmount;
            $payment->payment_method_id = $request->paymentMethod;
            $payment->kode_pembayaran = $newKodePembayaran;
            $payment->Keterangan = $request->keteranganPaymentSup;
            $payment->save();


            $totalPayment = $request->paymentAmount;
            $invoiceList = [];

            foreach ($request->invoice as $noInvoice) {
                $invoice = SupInvoice::where('invoice_no', $noInvoice)->firstOrFail();
                $remainingAmount = $invoice->total_harga - $invoice->total_bayar;

                if ($remainingAmount <= 0) {
                    Log::info("Invoice {$noInvoice} sudah lunas.");
                    continue;
                }

                $allocatedAmount = min($totalPayment, $remainingAmount);
                DB::table('tbl_payment_invoice_sup')->insert([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $allocatedAmount,
                ]);

                $invoice->total_bayar += $allocatedAmount;
                $invoice->status_bayar = $invoice->total_bayar >= $invoice->total_harga ? 'Lunas' : 'Belum Lunas';
                $invoice->save();

                $totalPayment -= $allocatedAmount;
                $invoiceList[] = $noInvoice;

                if ($totalPayment <= 0) {
                    break;
                }
            }

            if ($totalPayment > 0) {
                // throw new \Exception("Sisa dana pembayaran tidak teralokasi: {$totalPayment}");
                return response()->json([
                            'status' => 'error',
                            'message' => 'Total pembayaran melebihi total harga invoice.'
                        ], 400);
            }

            $noRef = implode(', ', $request->invoice);

            try {
                $request->merge(['code_type' => 'BKK']);
                $noJournal = $this->jurnalController->generateNoJurnal($request)->getData()->no_journal;

                $jurnal = new Jurnal();
                $jurnal->no_journal = $noJournal;
                $jurnal->tipe_kode = 'BKK';
                $jurnal->tanggal = $tanggalPayment;
                $jurnal->no_ref = $noRef;
                $jurnal->status = 'Approve';
                $jurnal->description = "Jurnal untuk Invoice {$noRef}";
                $jurnal->totaldebit = $request->paymentAmount;
                $jurnal->totalcredit = $request->paymentAmount;
                $jurnal->save();

                $jurnalItemDebit = new JurnalItem();
                $jurnalItemDebit->jurnal_id = $jurnal->id;
                $jurnalItemDebit->code_account = $vendorAccountId;
                $jurnalItemDebit->description = "Debit untuk Invoice {$noRef}";
                $jurnalItemDebit->debit = $request->paymentAmount;
                $jurnalItemDebit->credit = 0;
                $jurnalItemDebit->save();

                $jurnalItemCredit = new JurnalItem();
                $jurnalItemCredit->jurnal_id = $jurnal->id;
                $jurnalItemCredit->code_account = $request->paymentMethod;
                $jurnalItemCredit->description = "Kredit untuk Invoice {$noRef}";
                $jurnalItemCredit->debit = 0;
                $jurnalItemCredit->credit = $request->totalAmmount;
                $jurnalItemCredit->save();

                if ($request->has('items') && is_array($request->items)) {
                    foreach ($request->items as $item) {
                        DB::table('tbl_payment_sup_items')->insert([
                            'payment_id' => $payment->id,
                            'coa_id' => $item['account'],
                            'description' => $item['item_desc'],
                            'nominal' => $item['debit'],
                        ]);

                        $jurnalItem = new JurnalItem();
                        $jurnalItem->jurnal_id = $jurnal->id;
                        $jurnalItem->code_account = $item['account'];
                        $jurnalItem->description = $item['item_desc'];
                        $jurnalItem->debit = $item['debit'];
                        $jurnalItem->credit = 0;
                        $jurnalItem->save();

                        Log::info('Jurnal item untuk custom items berhasil ditambahkan.', [
                            'account' => $item['account'],
                            'description' => $item['item_desc'],
                            'nominal' => $item['debit'],
                        ]);
                    }
                }


            } catch (\Exception $e) {
                throw new \Exception('Gagal menambahkan jurnal: ' . $e->getMessage());
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment successfully created and invoice updated']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error during the transaction', 'error' => $e->getMessage()]);
        }
    }



    public function export(Request $request)
    {
        return Excel::download(new PaymentSupExport(
            $request->status,
            $request->startDate,
            $request->endDate
        ), 'Payment_Suppiler.xlsx');
    }

    public function getInvoiceSupDetail(Request $request)
    {
        $id = $request->id;

        $paymentSup = PaymentSup::select(
            'pc.id AS payment_sup_id',
            'pc.Keterangan',
            'pc.kode_pembayaran',
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(inv.invoice_no, '(', pi.amount, ')') SEPARATOR '; ') AS invoice_details"),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(coa.name, '(', pitems.nominal, ') - ', pitems.description) SEPARATOR '; ') AS item_details")
        )
            ->from('tbl_payment_sup as pc')
            ->where('pc.id', $id)
            ->leftJoin('tbl_payment_invoice_sup as pi', 'pc.id', '=', 'pi.payment_id')
            ->leftJoin('tbl_sup_invoice as inv', 'pi.invoice_id', '=', 'inv.id')
            ->leftJoin('tbl_payment_sup_items as pitems', 'pc.id', '=', 'pitems.payment_id')
            ->leftJoin('tbl_coa as coa', 'pitems.coa_id', '=', 'coa.id')
            ->groupBy(
                'pc.id',
                'pc.Keterangan',
                'pc.kode_pembayaran'
            )
            ->first();

        if (!$paymentSup) {
            return response()->json([
                'success' => false,
                'message' => 'Payment Vendor not found.',
            ], 404);
        }

        // Jika payment ditemukan, kirimkan response dengan datanya
        return response()->json([
            'success' => true,
            'data' => $paymentSup
        ]);
    }
    public function editpurchasepayment($id)
    {
        $payment = PaymentSup::with(['paymentInvoicesSup', 'paymentSupItem'])->findOrFail($id);
        $coas = COA::all();
        $listInvoice = SupInvoice::where('status_bayar', 'Belum Lunas')
            ->select('invoice_no')
            ->get();
        $selectedVendorId = $payment->selectVendor;

        $listVendor = Vendor::select('id', 'name')
            ->get();


        return view('vendor.purchasepayment.editpurchasepayment', [
            'payment' => $payment,
            'coas' => $coas,
            'listInvoice' => $listInvoice,
            'listVendor' => $listVendor,
            'selectedVendorId' => $selectedVendorId
        ]);
    }
    public function getInvoiceByNameEdits(Request $request)
    {
        dd($request->all());
        // $idVendor = $request->input('idVendor');

        // // dd($idVendor);

        // if (!$idVendor) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Vendor ID is required',
        //     ], 400);
        // }

        // $invoices = SupInvoice::where('vendor_id', $idVendor)->get(['invoice_no']);

        // if ($invoices->isEmpty()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No invoices found for this vendor',
        //     ]);
        // }

        // return response()->json([
        //     'success' => true,
        //     'invoices' => $invoices,
        // ]);
    }

    public function getInoviceByVendorEdit(Request $request)
    {
        dd($request->all());
    }

    public function update(Request $request, $id)
{

    dd($request->all());
    // Validasi input
    $validated = $request->validate([
        'invoice' => 'required|array',
        'tanggalPayment' => 'required|date',
        'paymentAmount' => 'required|numeric',
        'paymentMethod' => 'required|integer',
        'keteranganPaymentSup' => 'nullable|string',
        'items' => 'nullable|array',
        'items.*.account' => 'required|integer',
        'items.*.item_desc' => 'required|string',
        'items.*.debit' => 'required|numeric',
    ]);

    DB::beginTransaction();

    try {
        // Ambil data pembayaran lama
        $payment = PaymentSup::findOrFail($id);
        $oldInvoices = DB::table('tbl_payment_invoice_sup')->where('payment_id', $id)->get();

        // Kembalikan perubahan pada invoice lama
        foreach ($oldInvoices as $oldInvoice) {
            $invoice = SupInvoice::findOrFail($oldInvoice->invoice_id);
            $invoice->total_bayar -= $oldInvoice->amount;
            $invoice->status_bayar = $invoice->total_bayar >= $invoice->total_harga ? 'Lunas' : 'Belum Lunas';
            $invoice->save();
        }

        // Hapus alokasi lama
        DB::table('tbl_payment_invoice_sup')->where('payment_id', $id)->delete();

        // Perbarui data pembayaran
        $tanggalPayment = Carbon::createFromFormat('d F Y', $request->tanggalPayment)->format('Y-m-d');
        $payment->payment_date = $tanggalPayment;
        $payment->payment_method_id = $request->paymentMethod;
        $payment->Keterangan = $request->keteranganPaymentSup;
        $payment->save();

        // Alokasikan pembayaran ke invoice baru
        $totalPayment = $request->paymentAmount;
        foreach ($request->invoice as $noInvoice) {
            $invoice = SupInvoice::where('invoice_no', $noInvoice)->firstOrFail();
            $remainingAmount = $invoice->total_harga - $invoice->total_bayar;

            if ($remainingAmount <= 0) {
                Log::info("Invoice {$noInvoice} sudah lunas.");
                continue;
            }

            $allocatedAmount = min($totalPayment, $remainingAmount);
            DB::table('tbl_payment_invoice_sup')->insert([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $allocatedAmount,
            ]);

            $invoice->total_bayar += $allocatedAmount;
            $invoice->status_bayar = $invoice->total_bayar >= $invoice->total_harga ? 'Lunas' : 'Belum Lunas';
            $invoice->save();

            $totalPayment -= $allocatedAmount;

            if ($totalPayment <= 0) {
                break;
            }
        }

        if ($totalPayment > 0) {
            throw new \Exception("Sisa dana pembayaran tidak teralokasi: {$totalPayment}");
        }

        // Perbarui jurnal jika ada
        $jurnal = Jurnal::where('no_ref', implode(', ', $request->invoice))->firstOrFail();
        $jurnal->tanggal = $tanggalPayment;
        $jurnal->totaldebit = $request->paymentAmount;
        $jurnal->totalcredit = $request->paymentAmount;
        $jurnal->save();

        // Perbarui jurnal items
        JurnalItem::where('jurnal_id', $jurnal->id)->delete();

        // $jurnalItemDebit = new JurnalItem();
        // $jurnalItemDebit->jurnal_id = $jurnal->id;
        // $jurnalItemDebit->code_account = $vendor->account_id;
        // $jurnalItemDebit->description = "Debit untuk Invoice {$noRef}";
        // $jurnalItemDebit->debit = $request->paymentAmount;
        // $jurnalItemDebit->credit = 0;
        // $jurnalItemDebit->save();

        // $jurnalItemCredit = new JurnalItem();
        // $jurnalItemCredit->jurnal_id = $jurnal->id;
        // $jurnalItemCredit->code_account = $request->paymentMethod;
        // $jurnalItemCredit->description = "Kredit untuk Invoice {$noRef}";
        // $jurnalItemCredit->debit = 0;
        // $jurnalItemCredit->credit = $request->paymentAmount;
        // $jurnalItemCredit->save();

        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                DB::table('tbl_payment_sup_items')->insert([
                    'payment_id' => $payment->id,
                    'coa_id' => $item['account'],
                    'description' => $item['item_desc'],
                    'nominal' => $item['debit'],
                ]);

                $jurnalItem = new JurnalItem();
                $jurnalItem->jurnal_id = $jurnal->id;
                $jurnalItem->code_account = $item['account'];
                $jurnalItem->description = $item['item_desc'];
                $jurnalItem->debit = $item['debit'];
                $jurnalItem->credit = 0;
                $jurnalItem->save();
            }
        }

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Payment successfully updated']);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => 'Error during the transaction', 'error' => $e->getMessage()]);
    }
}


}
