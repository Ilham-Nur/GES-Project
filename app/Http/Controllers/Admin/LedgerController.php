<?php

namespace App\Http\Controllers\Admin;
use App\Exports\LedgerExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Str;

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
            $journalQuery = DB::select("SELECT
                                                    ji.id AS items_id,
                                                    ji.jurnal_id AS jurnal_id,
                                                    ji.code_account AS account_id,
                                                    ji.debit AS debit,
                                                    ji.credit AS credit,
                                                    ji.description AS items_description,
                                                    ji.memo AS memo,
                                                    ju.tanggal AS tanggal,
                                                    ju.tanggal_payment AS tanggal_payment,
                                                    ju.no_journal AS no_journal,
                                                    pem_inv.marking AS pembeli_invoice,
                                                    GROUP_CONCAT(DISTINCT res.no_do SEPARATOR ', ') AS resi_no_do,
                                                    pem_pay.marking AS pembeli_payment
                                                FROM tbl_jurnal_items ji
                                                LEFT JOIN tbl_jurnal ju ON ju.id = ji.jurnal_id
                                                LEFT JOIN tbl_invoice inv ON ju.invoice_id = inv.id
                                                LEFT JOIN tbl_resi res ON inv.id = res.invoice_id
                                                LEFT JOIN tbl_payment_customer pc ON ju.payment_id = pc.id
                                                LEFT JOIN tbl_pembeli pem_inv ON inv.pembeli_id = pem_inv.id
                                                LEFT JOIN tbl_pembeli pem_pay ON pc.pembeli_id = pem_pay.id
                                                WHERE ji.code_account = ?
                                                AND ju.tanggal >= ?
                                                AND ju.tanggal <= ?
                                                GROUP BY
                                                    ji.id, ji.jurnal_id, ji.code_account, ji.debit, ji.credit,
                                                    ji.description, ji.memo, ju.tanggal, ju.tanggal_payment,
                                                    ju.no_journal, pem_inv.marking, pem_pay.marking
                                                ORDER BY ju.tanggal ASC
                                            ", [$coa->coa_id, $startDate, $endDate]);

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
                <th width="15%" style="text-indent: 50px;">Date</th>
                <th width="15%">Payment Date</th>
                <th width="15%">No. DO</th>
                <th width="20%">No Voucher</th>
                <th width="20%">Description</th>
                <th width="15%" class="text-right">Total Debit</th>
                <th width="15%" class="text-right">Total Credit</th>
            </thead>
            <tbody>';
        foreach ($ledgerAccounts as $data) {
            if (!empty($data['journal_entries']) || $data['beginning_balance'] != 0 || $data['ending_balance'] != 0) {
                $output .= '<tr>
                                <td colspan="3"><b>' . ($data['code'] ?? '-') . ' - ' . ($data['account_name'] ?? '-') . '</b></td>
                                <td><b>BEGINING BALANCE</b></td>
                                <td class="text-right"><b>  </b></td>
                                <td class="text-right"><b>  </b></td>';
                $output .= '<td class="text-right"><b>' . number_format($data['beginning_balance'], 2) . '</b> </td> </tr>';

                foreach ($data['journal_entries'] as $entry) {
                    $output .= '<tr>
                                    <td style="padding-left:50px;">' . ($entry->tanggal ?? '-') . '</td>
                                    <td>' . ($entry->tanggal_payment ?? '-') . ' </td>
                                    <td>' . ($entry->resi_no_do ?? '-') . ' </td>
                                    <td>' . ($entry->no_journal ?? '-') .
                                        (!empty($entry->pembeli_invoice) || !empty($entry->pembeli_payment)
                                            ? ' - ' . (!empty($entry->pembeli_invoice) ? $entry->pembeli_invoice : $entry->pembeli_payment)
                                            : '') . '
                                    </td>
                                    <td class="text-left">' . ($entry->items_description ?? '-') . '</td>
                                    <td class="text-right">' . ($entry->debit ?? '-') . '</td>
                                    <td class="text-right">' . ($entry->credit ?? '-') . '</td>
                                </tr>';
                }

                $output .= '<tr>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td><b>ENDING BALANCE</b></td>
                                <td class="text-right"><b>  </b></td>
                                <td class="text-right"> <b>  </b> </td>
                                <td class="text-right"><b>' . number_format($data['ending_balance'], 2) . '</b> </td> </tr>';
            }
        }
        $output .= '</tbody></table>';

        return $output;
    }


    public function exportLedgerPdf(Request $request)
    {
        $companyId = session('active_company_id');
        $startDate = $request->input('startDate') ? date('Y-m-d', strtotime($request->input('startDate'))) : date('Y-m-01');
        $endDate = $request->input('endDate') ? date('Y-m-d', strtotime($request->input('endDate'))) : date('Y-m-t');
        $filterCode = $request->code_account_id ?? '-';

        $filterCodeNames = DB::table('tbl_coa')
            ->whereIn('tbl_coa.id', (array) $filterCode)
            ->pluck('tbl_coa.name')
            ->toArray();

        $filterCodeStr = implode(', ', $filterCodeNames);

        try {
            $coaQuery = DB::table('tbl_coa')
                ->select('tbl_coa.name AS account_name', 'tbl_coa.id AS coa_id', 'tbl_coa.code_account_id AS code', 'tbl_coa.default_posisi AS position')
                ->when(!empty($filterCode) && $filterCode !== '-', function ($query) use ($filterCode) {
                    return $query->whereIn('tbl_coa.id', (array) $filterCode);
                })
                ->orderBy('tbl_coa.code_account_id', 'ASC')
                ->get();

            if ($coaQuery->isEmpty()) {
                $coaQuery = DB::table('tbl_coa')
                    ->select('tbl_coa.name AS account_name', 'tbl_coa.id AS coa_id', 'tbl_coa.code_account_id AS code', 'tbl_coa.default_posisi AS position')
                    ->orderBy('tbl_coa.code_account_id', 'ASC')
                    ->get();
            }

            $ledgerAccounts = [];
            foreach ($coaQuery as $coa) {
                $journalQuery = DB::select("SELECT
                                                        ji.id AS items_id,
                                                        ji.jurnal_id AS jurnal_id,
                                                        ji.code_account AS account_id,
                                                        ji.debit AS debit,
                                                        ji.credit AS credit,
                                                        ji.description AS items_description,
                                                        ji.memo AS memo,
                                                        ju.tanggal AS tanggal,
                                                        ju.tanggal_payment AS tanggal_payment,
                                                        ju.no_journal AS no_journal,
                                                        pem_inv.marking AS pembeli_invoice,
                                                        GROUP_CONCAT(DISTINCT res.no_do SEPARATOR ', ') AS resi_no_do,
                                                        pem_pay.marking AS pembeli_payment
                                                    FROM tbl_jurnal_items ji
                                                    LEFT JOIN tbl_jurnal ju ON ju.id = ji.jurnal_id
                                                    LEFT JOIN tbl_invoice inv ON ju.invoice_id = inv.id
                                                    LEFT JOIN tbl_resi res ON inv.id = res.invoice_id
                                                    LEFT JOIN tbl_payment_customer pc ON ju.payment_id = pc.id
                                                    LEFT JOIN tbl_pembeli pem_inv ON inv.pembeli_id = pem_inv.id
                                                    LEFT JOIN tbl_pembeli pem_pay ON pc.pembeli_id = pem_pay.id
                                                    WHERE ji.code_account = ?
                                                    AND ju.tanggal >= ?
                                                    AND ju.tanggal <= ?
                                                    GROUP BY
                                                        ji.id, ji.jurnal_id, ji.code_account, ji.debit, ji.credit,
                                                        ji.description, ji.memo, ju.tanggal, ju.tanggal_payment,
                                                        ju.no_journal, pem_inv.marking, pem_pay.marking
                                                    ORDER BY ju.tanggal ASC
                                                ", [$coa->coa_id, $startDate, $endDate]);


                $beginningBalanceQuery = DB::select("SELECT SUM(ji.debit) AS total_debit,
                                                                SUM(ji.credit) AS total_credit
                                                        FROM tbl_jurnal_items ji
                                                        LEFT JOIN tbl_jurnal ju ON ju.id = ji.jurnal_id
                                                        WHERE ji.code_account = ?
                                                        AND ju.tanggal < ?", [$coa->coa_id, $startDate]);

                $beginningBalance = ($coa->position == 'Debit')
                    ? ($beginningBalanceQuery[0]->total_debit - $beginningBalanceQuery[0]->total_credit)
                    : ($beginningBalanceQuery[0]->total_credit - $beginningBalanceQuery[0]->total_debit);

                $totalDebit = array_sum(array_column($journalQuery, 'debit'));
                $totalCredit = array_sum(array_column($journalQuery, 'credit'));

                $endingBalance = ($coa->position == 'Debit')
                    ? ($beginningBalance + $totalDebit - $totalCredit)
                    : ($beginningBalance + $totalCredit - $totalDebit);

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

            if (empty($ledgerAccounts)) {
                return response()->json(['error' => 'No Ledger report found'], 404);
            }

            $pdf = pdf::loadView('exportPDF.ledgerpdf', [
                'ledgerAccounts' => $ledgerAccounts,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'filterCode' => $filterCodeStr,
            ])
                ->setPaper('A4', 'portrait')
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->setWarnings(false);

            return $pdf->stream('Ledger_report.pdf');

        } catch (\Exception $e) {
            Log::error('Error generating ledger report PDF: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'An error occurred while generating the ledger report PDF'], 500);
        }
    }




    public function exportLedger(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $filterCode = $request->code_account_id ?? '-';

        return Excel::download(new LedgerExport($filterCode, $startDate, $endDate), 'Ledger.xlsx');
    }


}
