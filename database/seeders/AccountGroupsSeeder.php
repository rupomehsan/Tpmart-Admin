<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, insert account types
        $accountTypes = [
            [
                'name' => 'PROPERTY AND ASSETS',
                'code' => '1000000000',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'LIABILITY AND CAPITAL',
                'code' => '2000000000',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'INCOME/REVENUE/SALES',
                'code' => '3000000000',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EXPENSES/COST OF GOODS SOLD',
                'code' => '4000000000',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ];
        // Insert account types
        foreach ($accountTypes as $type) {
            DB::table('account_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        // Get account type IDs
        $assetTypeId = DB::table('account_types')->where('code', 'ASSET')->first()->id;
        $liabilityTypeId = DB::table('account_types')->where('code', 'LIABILITY')->first()->id;
        $equityTypeId = DB::table('account_types')->where('code', 'EQUITY')->first()->id;
        $revenueTypeId = DB::table('account_types')->where('code', 'REVENUE')->first()->id;
        $expenseTypeId = DB::table('account_types')->where('code', 'EXPENSE')->first()->id;

        // Account Groups Data
        $accountGroups = [
            // ASSETS
            [
                'name' => 'Furniture & Fixtures',
                'code' => '1101001000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Office Equipment',
                'code' => '1101002000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Computer & Computer Equipment',
                'code' => '1101003000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Software Development',
                'code' => '1101004000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Motor Vehicles',
                'code' => '1101005000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Land & Buildings',
                'code' => '1101006000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cash in Hand',
                'code' => '1101007000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cash at Bank',
                'code' => '1101008000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Account Receivable',
                'code' => '1101009000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Advance',
                'code' => '1101010000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Fixed Assets',
                'code' => '1101011000',
                'account_type_id' => $assetTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // LIABILITIES
            [
                'name' => 'Capital Fund/Equity',
                'code' => '2001001000',
                'account_type_id' => $liabilityTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Long-term Liabilities',
                'code' => '2001002000',
                'account_type_id' => $liabilityTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Current Liabilities',
                'code' => '2001003000',
                'account_type_id' => $liabilityTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accounts Payable',
                'code' => '2001004000',
                'account_type_id' => $liabilityTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Loans & Advances',
                'code' => '2001005000',
                'account_type_id' => $liabilityTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Income
            [
                'name' => 'Operating Income',
                'code' => '3001000000',
                'account_type_id' => $revenueTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Non-Operating Income',
                'code' => '3001001000',
                'account_type_id' => $revenueTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            
            // EXPENSES
            [
                'name' => 'Operating Expenses',
                'code' => '4001000000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrative Expenses',
                'code' => '4001001000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marketing Expenses',
                'code' => '4001002000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cost of Goods Sold',
                'code' => '4001003000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Financial Expenses',
                'code' => '4001004000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Program Cost',
                'code' => '4001005000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VAT',
                'code' => '4001006000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Income Tax',
                'code' => '4001007000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transpotation &Communication',
                'code' => '4001008000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Expenses',
                'code' => '4001009000',
                'account_type_id' => $expenseTypeId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert account groups
        foreach ($accountGroups as $group) {
            DB::table('account_groups')->updateOrInsert(
                ['code' => $group['code']],
                $group
            );
        }

        // Account Subsidiaries Data
        $accountSubsidiaries = [
            // ASSETS
            [
                'name' => 'Desktop Computer',
                'ledger_code' => '1101003001',
                'group_id' => DB::table('account_groups')->where('code', '1101003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],   
            [
                'name' => 'Laptop',
                'ledger_code' => '1101003002',
                'group_id' => DB::table('account_groups')->where('code', '1101001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Printer',
                'ledger_code' => '1101003003',
                'group_id' => DB::table('account_groups')->where('code', '1101003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Scanner',
                'ledger_code' => '1101003004',
                'group_id' => DB::table('account_groups')->where('code', '1101003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fax Machine',
                'ledger_code' => '1101003005',
                'group_id' => DB::table('account_groups')->where('code', '1101003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Computer Equipment',
                'ledger_code' => '1101003006',
                'group_id' => DB::table('account_groups')->where('code', '1101003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Software Development',
                'ledger_code' => '1101004001',
                'group_id' => DB::table('account_groups')->where('code', '1101004000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Software Development',
                'ledger_code' => '1101004002',
                'group_id' => DB::table('account_groups')->where('code', '1101004000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inventory',
                'ledger_code' => '1101005001',
                'group_id' => DB::table('account_groups')->where('code', '1101005000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cash',
                'ledger_code' => '1101007001',
                'group_id' => DB::table('account_groups')->where('code', '1101007000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bkash Mobile Banking Account',
                'ledger_code' => '1101008001',
                'group_id' => DB::table('account_groups')->where('code', '1101008000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nogod Mobile Banking Account',
                'ledger_code' => '1101008002',
                'group_id' => DB::table('account_groups')->where('code', '1101008000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rocket Mobile Banking Account',
                'ledger_code' => '1101008003',
                'group_id' => DB::table('account_groups')->where('code', '1101008000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DBBL',
                'ledger_code' => '1101008004',
                'group_id' => DB::table('account_groups')->where('code', '1101008000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Customer Receivable',
                'ledger_code' => '1101009001',
                'group_id' => DB::table('account_groups')->where('code', '1101009000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Advance Payment to Supplier',
                'ledger_code' => '1101010001',
                'group_id' => DB::table('account_groups')->where('code', '1101010000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Advance Payment to Customer',
                'ledger_code' => '1101011001',
                'group_id' => DB::table('account_groups')->where('code', '1101011000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Advance Payment',
                'ledger_code' => '1101011002',
                'group_id' => DB::table('account_groups')->where('code', '1101011000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '1000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // LIABILITIES
            [
                'name' => 'Retained Surplus',
                'ledger_code' => '2001001001',
                'group_id' => DB::table('account_groups')->where('code', '2001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ], 
            [
                'name' => 'Capital Fund - Share Capital',
                'ledger_code' => '2001001002',
                'group_id' => DB::table('account_groups')->where('code', '2001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Capital Fund - Share Premium',
                'ledger_code' => '2001001003',
                'group_id' => DB::table('account_groups')->where('code', '2001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shareholder\'s Drawings',
                'ledger_code' => '2001001004',
                'group_id' => DB::table('account_groups')->where('code', '2001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bank Loan',
                'ledger_code' => '2001002001',
                'group_id' => DB::table('account_groups')->where('code', '2001002000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Long-term Liabilities',
                'ledger_code' => '2001002002',
                'group_id' => DB::table('account_groups')->where('code', '2001002000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Supplier Payable',
                'ledger_code' => '2001003001',
                'group_id' => DB::table('account_groups')->where('code', '2001003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Loans & Advances',
                'ledger_code' => '2001005001',
                'group_id' => DB::table('account_groups')->where('code', '2001005000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tax Payable',
                'ledger_code' => '2001006001',
                'group_id' => DB::table('account_groups')->where('code', '2001006000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VAT Payable',
                'ledger_code' => '2001007001',
                'group_id' => DB::table('account_groups')->where('code', '2001006000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sales Tax Payable',
                'ledger_code' => '2001008001',
                'group_id' => DB::table('account_groups')->where('code', '2001006000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Current Liabilities',
                'ledger_code' => '2001009001',
                'group_id' => DB::table('account_groups')->where('code', '2001009000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '2000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // INCOME
            [
                'name' => 'Sales',
                'ledger_code' => '3001001001',
                'group_id' => DB::table('account_groups')->where('code', '3001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sales of Services',
                'ledger_code' => '3001001002',
                'group_id' => DB::table('account_groups')->where('code', '3001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wages Sales',
                'ledger_code' => '3001002001',
                'group_id' => DB::table('account_groups')->where('code', '3001002000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Delivery Charges',
                'ledger_code' => '3001003001',
                'group_id' => DB::table('account_groups')->where('code', '3001003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brokerage Income',
                'ledger_code' => '3001003002',
                'group_id' => DB::table('account_groups')->where('code', '3001003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cost Sharing / Overhead',
                'ledger_code' => '3001003003',
                'group_id' => DB::table('account_groups')->where('code', '3001003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Miscellaneous Income',
                'ledger_code' => '3001004001',
                'group_id' => DB::table('account_groups')->where('code', '3001004000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Other Income',
                'ledger_code' => '3001005001',
                'group_id' => DB::table('account_groups')->where('code', '3001005000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '3000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

           
            // EXPENSES
            [
                'name' => 'Manufacturing Expenses',
                'ledger_code' => '4001000001',
                'group_id' => DB::table('account_groups')->where('code', '4001000000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Raw Materials',
                'ledger_code' => '4001001001',
                'group_id' => DB::table('account_groups')->where('code', '4001001000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Work-in-Progress',
                'ledger_code' => '4001002001',
                'group_id' => DB::table('account_groups')->where('code', '4001002000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wages',
                'ledger_code' => '4001003001',
                'group_id' => DB::table('account_groups')->where('code', '4001003000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Packaging',
                'ledger_code' => '4001004001',
                'group_id' => DB::table('account_groups')->where('code', '4001004000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Depreciation',
                'ledger_code' => '4001005001',
                'group_id' => DB::table('account_groups')->where('code', '4001005000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VAT',
                'ledger_code' => '4001006001',
                'group_id' => DB::table('account_groups')->where('code', '4001006000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bank Charges',
                'ledger_code' => '4001007001',
                'group_id' => DB::table('account_groups')->where('code', '4001007000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Income Tax',
                'ledger_code' => '4001008001',
                'group_id' => DB::table('account_groups')->where('code', '4001008000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rent & Lease',
                'ledger_code' => '4001009001',
                'group_id' => DB::table('account_groups')->where('code', '4001009000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Returns',
                'ledger_code' => '4001010001',
                'group_id' => DB::table('account_groups')->where('code', '4001010000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Conversion Charges',
                'ledger_code' => '4001011001',
                'group_id' => DB::table('account_groups')->where('code', '4001011000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other Expenses',
                'ledger_code' => '4001012001',
                'group_id' => DB::table('account_groups')->where('code', '4001012000')->first()->id,
                'account_type_id' => DB::table('account_types')->where('code', '4000000000')->first()->id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert account subsidiaries
        foreach ($accountSubsidiaries as $subsidiary) {
            DB::table('account_subsidiary_ledgers')->updateOrInsert(
                ['code' => $subsidiary['code']],
                $subsidiary
            );
        }

        $this->command->info('Account Types and Groups seeded successfully!');
    }
}
