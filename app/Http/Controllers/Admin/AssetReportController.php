<?php

namespace App\Http\Controllers\Admin;
use App\Exports\AssetReportExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Asset;
use App\Traits\WhatsappTrait;
use Carbon\Carbon;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Str;

class AssetReportController extends Controller
{
    use WhatsappTrait;

    public function index()
    {

        return view('Report.AssetReport.indexassetreport');
    }

    public function getAssetReport(Request $request)
    {
        $companyId = session('active_company_id');
        $txSearch = '%' . strtoupper(trim($request->txSearch)) . '%';
        $status = $request->status;
        $customer = $request->customer;

        $startDate = $request->startDate ? date('Y-m-d', strtotime($request->startDate)) : Carbon::now()->startOfMonth();
        $endDate = $request->endDate ? date('Y-m-d', strtotime($request->endDate)) : Carbon::now()->endOfMonth();

        $asset = Asset::select(
                'tbl_assets.id as asset_id',
                'tbl_assets.acquisition_price',
                'tbl_assets.estimated_age',
                'tbl_assets.asset_name',
                'tbl_assets.acquisition_date',
                'tbl_jurnal.totalcredit as credit',
                'tbl_jurnal.tanggal as tanggal',
                'tbl_jurnal.begining_value as begining_value',
                'tbl_jurnal.ending_value as ending_value'
            )
            ->join('tbl_jurnal', 'tbl_assets.id', '=', 'tbl_jurnal.asset_id')
            ->where('tbl_assets.company_id', $companyId)
            ->whereDate('tbl_jurnal.tanggal', '>=', $startDate)
            ->whereDate('tbl_jurnal.tanggal', '<=', $endDate)
            ->get()
            ->map(function ($item) {
                // Adjusting the balance calculation
                $item->beginning_balance = $item->acquisition_price - $item->total_credit_before;
                $item->ending_balance = $item->beginning_balance - $item->credit;
                return $item;
            });
        
        // Calculate the sum of begining_value and ending_value
        $totalBeginningValue = $asset->sum('begining_value');
        $totalEndingValue = $asset->sum('ending_value');
        $totalDepreciation = $totalBeginningValue - $totalEndingValue;

        $output = '
                    <h5 style="text-align:center; width:100%">'
            . \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - '
            . \Carbon\Carbon::parse($endDate)->format('d M Y') .
            '</h5>

                    <div class="card-body">
                    <table class="table" width="100%">
                    <thead>
                        <th width="15%" style="text-left">Date</th>
                        <th width="25%">Asset Name</th>
                        <th width="15%">Estimated Age</th>
                        <th width="15%" class="text-right">Begining Value</th>
                        <th width="15%" class="text-right">Depreciation</th>
                        <th width="15%" class="text-right">Ending Value</th>
                    </thead>
                    <tbody>';

            foreach ($asset as $data) {
                if ($data->ending_balance != 0) { // Check if ending_balance is not 0
                    $output .= '<tr>
                                    <td>' . \Carbon\Carbon::parse($data->tanggal)->format('d M Y') . '</td>
                                    <td>' . ($data->asset_name) . '</td>
                                    <td class="text-center">' . ($data->estimated_age) . ' Month </td>
                                    <td class="text-right">' . number_format($data->begining_value, 2) . '</td>
                                    <td class="text-right">' . number_format($data->credit, 2) . '</td>
                                    <td class="text-right">' . number_format($data->ending_value, 2) . '</td>        
                                </tr>';
                }
            }

        $output .= '
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="text-center"> Grand Total</td>
                        <td class="text-right">' . number_format($totalBeginningValue, 2) . '</td>
                        <td class="text-right">' . number_format($totalDepreciation, 2) . '</td>
                        <td class="text-right">' . number_format($totalEndingValue, 2) . '</td>
                    </tr>
                </tfoot></table> </div>';

        return $output;
    }


    public function exportAssetReport(Request $request)
    {

        // Pastikan format yang diterima adalah 'd M Y' (misalnya '01 Jan 2025')
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Periksa jika input tanggal ada
        if ($startDate && $endDate) {
            // Ubah tanggal menjadi format yang bisa digunakan dalam query
            $startDate = Carbon::createFromFormat('d M Y', $startDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d M Y', $endDate)->format('Y-m-d');
        } else {
            // Tentukan default tanggal jika tidak ada input tanggal
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }


        return Excel::download(new AssetReportExport($startDate, $endDate), 'asset_report.xlsx');
    }
    public function generatePdf(Request $request)
    {

        $companyId = session('active_company_id');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        try {
            $query = Asset::select(
                'tbl_assets.id as asset_id',
                'tbl_assets.acquisition_price',
                'tbl_assets.estimated_age',
                'tbl_assets.asset_name',
                'tbl_assets.acquisition_date',
                DB::raw('SUM(tbl_jurnal.totalcredit) as credit'),
                DB::raw('SUM(tbl_jurnal.begining_value) as begining_value'),
                DB::raw('SUM(tbl_jurnal.ending_value) as ending_value')
            )
            ->join('tbl_jurnal', 'tbl_assets.id', '=', 'tbl_jurnal.asset_id')
            ->where('tbl_assets.company_id', $companyId)
            ->groupBy(
                'tbl_assets.id',
                'tbl_assets.acquisition_price',
                'tbl_assets.estimated_age',
                'tbl_assets.asset_name',
                'tbl_assets.acquisition_date',
            );


            // Filter berdasarkan tanggal jika tersedia
            if ($request->startDate && $request->endDate) {
                $query->whereBetween('tbl_jurnal.tanggal', [
                    Carbon::parse($request->startDate)->format('Y-m-d'),
                    Carbon::parse($request->endDate)->format('Y-m-d')
                ]);
            }

            // Ambil hasil query dan proses data
            $assets = $query->get()->map(function ($asset) {
                $asset->beginning_balance = $asset->acquisition_price - $asset->total_credit_before;
                $asset->ending_balance = $asset->beginning_balance - $asset->credit;
                return $asset;
            });
            
            $totalBeginningValue = $assets->sum('begining_value');
            $totalEndingValue = $assets->sum('ending_value');
            $totalDepreciation = $totalBeginningValue - $totalEndingValue;
            
            // Pastikan data tidak kosong
            if ($assets->isEmpty()) {
                return response()->json(['error' => 'No data available for the selected date range'], 404);
            }

            // Generate PDF
            $pdf = pdf::loadView('exportPDF.assetreportpdf', [
                'assets' => $assets,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalBeginningValue' => $totalBeginningValue,
                'totalEndingValue' => $totalEndingValue,
                'totalDepreciation' => $totalDepreciation,
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->setWarnings(false);

            // Buat folder untuk menyimpan PDF jika belum ada
            $folderPath = storage_path('app/public/assetreports');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            // Tentukan nama file untuk PDF
            $fileName = 'assets_report_' . (string) Str::uuid() . '.pdf';
            $filePath = $folderPath . '/' . $fileName;

            // Simpan PDF
            $pdf->save($filePath);

            // Kembalikan URL PDF yang dihasilkan
            $url = asset('storage/assetreports/' . $fileName);
            return response()->json(['url' => $url]);

        } catch (\Exception $e) {
            Log::error('Error generating Asset Report PDF: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'An error occurred while generating the PDF'], 500);
        }
    }
}
