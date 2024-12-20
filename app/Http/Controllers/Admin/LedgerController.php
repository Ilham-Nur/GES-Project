<?php

namespace App\Http\Controllers\Admin;
use App\Exports\LedgerExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LedgerController extends Controller
{
    public function index()
    {
        $listCode = DB::table('tbl_coa')
            ->select('tbl_coa.id', 'tbl_coa.code_account_id', 'tbl_coa.name')
            ->distinct()
            ->join('tbl_jurnal_items', 'tbl_coa.id', '=', 'tbl_jurnal_items.code_account')
            ->join('tbl_jurnal', 'tbl_jurnal.id', '=', 'tbl_jurnal_items.jurnal_id')
            ->orderBy('tbl_coa.code_account_id', 'ASC')
            ->get();

        return view('Report.Ledger.indexledger', compact('listCode'));
    }

    public function getLedgerData(Request $request)
    {
        $filterCode = $request->filterCode;
        $startDate = $request->startDate ? date('Y-m-d', strtotime($request->startDate)) : date('Y-m-01');
        $endDate = $request->endDate ? date('Y-m-d', strtotime($request->endDate)) : date('Y-m-t');

        $coaQuery = DB::table('tbl_coa')
            ->select('tbl_coa.name AS account_name', 'tbl_coa.id AS coa_id', 'tbl_coa.code_account_id AS code', 'tbl_coa.default_posisi AS position')
            ->when($filterCode, function ($query, $filterCode) {
                return $query->whereIn('tbl_coa.id', $filterCode); 
            })
            ->orderBy('tbl_coa.code_account_id', 'ASC')
            ->get();

        $ledgerAccounts = [];
        foreach ($coaQuery as $coa) {
            $journalQuery = DB::select("SELECT ji.id AS items_id,
                                            ji.jurnal_id AS jurnal_id,
                                            ji.code_account AS account_id,
                                            ji.debit AS debit,
                                            ji.credit AS credit,
                                            ji.description AS items_description,
                                            ju.tanggal AS tanggal
                                        FROM tbl_jurnal_items ji
                                        LEFT JOIN tbl_jurnal ju ON ju.id = ji.jurnal_id
                                        WHERE ji.code_account = $coa->coa_id
                                        AND ju.tanggal >= '$startDate'
                                        AND ju.tanggal <= '$endDate'");

            $beginningBalanceQuery = DB::select("SELECT SUM(ji.debit) AS total_debit,
                                                            SUM(ji.credit) AS total_credit
                                                    FROM tbl_jurnal_items ji
                                                    LEFT JOIN tbl_jurnal ju ON ju.id = ji.jurnal_id
                                                    WHERE ji.code_account = $coa->coa_id
                                                    AND ju.tanggal < '$startDate'");

            $beginningBalance = ($coa->position == 'Debit')
                ? $beginningBalanceQuery[0]->total_debit - $beginningBalanceQuery[0]->total_credit
                : $beginningBalanceQuery[0]->total_credit - $beginningBalanceQuery[0]->total_debit;

            $totalDebit = array_sum(array_column($journalQuery, 'debit'));
            $totalCredit = array_sum(array_column($journalQuery, 'credit'));

            $endingBalance = ($coa->position == 'Debit')
                ? $beginningBalance + $totalDebit - $totalCredit
                : $beginningBalance + $totalCredit - $totalDebit;

            if (!empty($journalQuery) || $beginningBalance != 0) {
                $ledgerAccounts[] = [
                    'coa_id' => $coa->coa_id,
                    'account_name' => $coa->account_name,
                    'code' => $coa->code,
                    'beginning_balance' => $beginningBalance,
                    'ending_balance' => $endingBalance,
                    'journal_entries' => $journalQuery,
                ];
            }

        }

        return $ledgerAccounts;
    }

    public function getLedgerHtml(Request $request)
    {
        $ledgerAccounts = $this->getLedgerData($request);

        $output = '<table width="100%" class="table table-vcenter card-table">
            <thead>
                <th width="30%" style="text-indent: 50px;">Date</th>
                <th width="30%">Description</th>
                <th width="20%" class="text-right">Total Debit</th>
                <th width="20%" class="text-right">Total Credit</th>
            </thead>
            <tbody>';
        foreach ($ledgerAccounts as $data) {
            if (!empty($data['journal_entries']) || $data['beginning_balance'] != 0 || $data['ending_balance'] != 0) {
                $output .= '<tr>
                                <td><b>' . ($data['code'] ?? '-') . ' - ' . ($data['account_name'] ?? '-') . '</b></td>
                                <td><b>BEGINING BALANCE</b></td>
                                <td class="text-right"><b>  </b></td>';
                $output .= '<td class="text-right"><b>' . number_format($data['beginning_balance'], 2) . '</b> </td> </tr>';

                foreach ($data['journal_entries'] as $entry) {
                    $output .= '<tr>
                                    <td style="padding-left:50px;">' . ($entry->tanggal ?? '-') . '</td>
                                    <td>' . ($entry->items_description ?? '-') . '</td>
                                    <td class="text-right">' . ($entry->debit ?? '-') . '</td>
                                    <td class="text-right">' . ($entry->credit ?? '-') . '</td>
                                </tr>';
                }

                $output .= '<tr>
                                <td> </td>
                                <td><b>ENDING BALANCE</b></td>
                                <td class="text-right"> <b>  </b> </td>
                                <td class="text-right"><b>' . number_format($data['ending_balance'], 2) . '</b> </td> </tr>';
            }
        }
        $output .= '</tbody></table>';

        return $output;
    }


    public function generatePdf(Request $request)
    {
        $htmlOutput = $this->getLedgerHtml($request);

        $pdf = PDF::loadHTML($htmlOutput);
        return $pdf->download('Ledger_Report.pdf');
    }


    public function exportExcel(Request $request)
    {
        $ledgerAccounts = $this->getLedgerData($request);
        return Excel::download(new LedgerExport($ledgerAccounts), 'Ledger_Report.xlsx');
    }


}
