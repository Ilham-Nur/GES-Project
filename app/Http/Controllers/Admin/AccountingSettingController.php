<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\AccountSettings;
use DB;
use Illuminate\Http\Request;

class AccountingSettingController extends Controller
{
    public function index()
    {
        $coas = COA::all();
        $accountSettings = DB::table('tbl_account_settings')->first();

        $savedPaymentAccounts = DB::table('tbl_payment_account')
            ->pluck('coa_id')
            ->toArray();

        $savedOperatingRevenue = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Operating Revenue')
            ->pluck('coa_id')
            ->toArray();

        $savedOperatingExpense = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Operating Expense')
            ->pluck('coa_id')
            ->toArray();
        
        $savedHpp = DB::table('tbl_report_accounts')
            ->where('type', '=', 'HPP')
            ->pluck('coa_id')
            ->toArray();    

        $savedNonOperatingRevenue = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Non Operating Revenue')
            ->pluck('coa_id')
            ->toArray();

        $savedNonOperatingExpense = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Non Operating Expense')
            ->pluck('coa_id')
            ->toArray();

        $savedCapitalAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Capital')
            ->pluck('coa_id')
            ->toArray();

        $savedAdditionalCapitalAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Additional Capital')
            ->pluck('coa_id')
            ->toArray();

        $savedReturnedProfitAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Returned Profit')
            ->pluck('coa_id')
            ->toArray();

        $savedCurrentProfitAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Current Profit')
            ->pluck('coa_id')
            ->toArray();

        $savedDevidenAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Deviden')
            ->pluck('coa_id')
            ->toArray();

        $savedInvestingAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Investing')
            ->pluck('coa_id')
            ->toArray();

        $savedFinancingAccount = DB::table('tbl_report_accounts')
            ->where('type', '=', 'Financing')
            ->pluck('coa_id')
            ->toArray();

        $createdAtStatus = $accountSettings ? $accountSettings->id : null;

        return view('accounting.accountingSetting.indexaccountsetting',
                    compact('coas',
                            'accountSettings',
                            'createdAtStatus',
                            'savedPaymentAccounts',
                            'savedOperatingRevenue',
                            'savedOperatingExpense',
                            'savedHpp',
                            'savedNonOperatingRevenue',
                            'savedNonOperatingExpense',
                            'savedCapitalAccount',
                            'savedAdditionalCapitalAccount',
                            'savedReturnedProfitAccount',
                            'savedCurrentProfitAccount',
                            'savedDevidenAccount',
                            'savedInvestingAccount',
                            'savedFinancingAccount'
                            )
                    );
    }


    public function store(Request $request)
    {
        // Validasi semua data yang dibutuhkan
        $validatedData = $request->validate([
            'sales_account_id' => 'nullable',
            'receivable_sales_account_id' => 'nullable',
            'customer_sales_return_account_id' => 'nullable',
            'discount_sales_account_id' => 'nullable',
            'sales_profit_rate_account_id' => 'nullable',
            'sales_loss_rate_account_id' => 'nullable',
            'purchase_account_id' => 'nullable',
            'debt_account_id' => 'nullable',
            'supplier_purchase_return_account_id' => 'nullable',
            'discount_purchase_account_id' => 'nullable',
            'purchase_profit_rate_account_id' => 'nullable',
            'purchase_loss_rate_account_id' => 'nullable',
            'coa_id' => 'nullable|array',
            'coa_id.*' => 'exists:tbl_coa,id',
            'operating_revenue' => 'nullable|array',
            'operating_revenue.*' => 'exists:tbl_coa,id',
        ]);


        if ($request->idData) {
            $accountSetting = AccountSettings::find($request->idData);
            $accountSetting->update($validatedData);
        } else {
            AccountSettings::create($validatedData);
        }


        if (empty($request->coa_id)) {
            DB::table('tbl_payment_account')->delete();
        } else {
            foreach ($request->coa_id as $coaId) {
                DB::table('tbl_payment_account')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['updated_at' => now()]
                );
            }
        }

        if (empty($request->operating_revenue)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Operating Revenue')
                ->delete();
        } else {
            // Insert or update records
            foreach ($request->operating_revenue as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Operating Revenue', 'updated_at' => now()]
                );
            }
        
            // Delete records that are not in the provided array
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Operating Revenue')
                ->whereNotIn('coa_id', $request->operating_revenue)
                ->delete();
        }

        if (empty($request->operating_expense)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Operating Expense')
                ->delete();
        } else {
            foreach ($request->operating_expense as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Operating Expense'],
                    ['updated_at' => now()]
                );
            }
            
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Operating Expense')
            ->whereNotIn('coa_id', $request->operating_expense)
            ->delete();
        }
        
        if (empty($request->hpp)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'HPP')
                ->delete();
        } else {
            foreach ($request->hpp as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'HPP'],
                    ['updated_at' => now()]
                );
            }
        
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'HPP')
            ->whereNotIn('coa_id', $request->hpp)
            ->delete();
        }

        if (empty($request->non_operating_revenue)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Non Operating Revenue')
                ->delete();
        } else {
            foreach ($request->non_operating_revenue as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Non Operating Revenue'],
                    ['updated_at' => now()]
                );
            }
        
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Non Operating Revenue')
            ->whereNotIn('coa_id', $request->non_operating_revenue)
            ->delete();
        }

        if (empty($request->non_operating_expense)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Non Operating Expense')
                ->delete();
        } else {
            foreach ($request->non_operating_expense as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Non Operating Expense'],
                    ['updated_at' => now()]
                );
            }

        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Non Operating Expense')
            ->whereNotIn('coa_id', $request->non_operating_expense)
            ->delete();

        }

        if (empty($request->capital)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Capital')
                ->delete();
        } else {
            foreach ($request->capital as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Capital'],
                    ['updated_at' => now()]
                );
            }

        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Capital')
            ->whereNotIn('coa_id', $request->capital)
            ->delete();
        }

        if (empty($request->additional_capital)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Addtional Capital')
                ->delete();
        } else {
            foreach ($request->additional_capital as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Additional Capital'],
                    ['updated_at' => now()]
                );
            }
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Additional Capital')
            ->whereNotIn('coa_id', $request->additional_capital)
            ->delete();
        }

        if (empty($request->returned_profit)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Returned Profit}')
                ->delete();
        } else {
            foreach ($request->returned_profit as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Returned Profit'],
                    ['updated_at' => now()]
                );
            }
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Returned Profit')
            ->whereNotIn('coa_id', $request->returned_profit)
            ->delete();
        }

        if (empty($request->current_profit)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Current Profit')
                ->delete();
        } else {
            foreach ($request->current_profit as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Current Profit'],
                    ['updated_at' => now()]
                );
            }
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Current Profit')
            ->whereNotIn('coa_id', $request->current_profit)
            ->delete();
        }

        if (empty($request->deviden)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Deviden')
                ->delete();
        } else {
            foreach ($request->deviden as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Deviden'],
                    ['updated_at' => now()]
                );
            }

        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Deviden')
            ->whereNotIn('coa_id', $request->deviden)
            ->delete();
        }

        if (empty($request->investing)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Investing')
                ->delete();
        } else {
            foreach ($request->investing as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Investing'],
                    ['updated_at' => now()]
                );
            }
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Investing')
            ->whereNotIn('coa_id', $request->investing)
            ->delete();
        }

        if (empty($request->financing)) {
            DB::table('tbl_report_accounts')
                ->where('type', '=', 'Financing')
                ->delete();
        } else {
            foreach ($request->financing as $coaId) {
                DB::table('tbl_report_accounts')->updateOrInsert(
                    ['coa_id' => $coaId],
                    ['type' => 'Financing'],
                    ['updated_at' => now()]
                );
            }
        DB::table('tbl_report_accounts')
            ->where('type', '=', 'Financing')
            ->whereNotIn('coa_id', $request->financing)
            ->delete();
        }


        return response()->json(['success' => 'Data berhasil disimpan']);
    }
}


