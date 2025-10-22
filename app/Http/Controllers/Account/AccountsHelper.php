<?php
//devmonir date 07-09-2025 16:00 pm 
namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Models\AccountTransaction;
use App\Http\Controllers\Account\Models\AccountTransactionDetail;
use App\Http\Controllers\Account\Models\SubsidiaryLedger;
use App\Http\Controllers\Account\Models\SubsidiaryCalculation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountsHelper
{
    /**
     * Convert number to words
     *
     * @param int $number
     * @return string
     */
    public static function numberToWords($number)
    {
        $ones = array(
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        );
        
        $tens = array(
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        
        if ($number < 20) {
            return $ones[$number];
        } elseif ($number < 100) {
            $tens_digit = floor($number / 10) * 10;
            $ones_digit = $number % 10;
            return $tens[$tens_digit] . ($ones_digit > 0 ? ' ' . $ones[$ones_digit] : '');
        } elseif ($number < 1000) {
            $hundreds = floor($number / 100);
            $remainder = $number % 100;
            $result = $ones[$hundreds] . ' Hundred';
            if ($remainder > 0) {
                $result .= ' ' . self::numberToWords($remainder);
            }
            return $result;
        } elseif ($number < 100000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            $result = self::numberToWords($thousands) . ' Thousand';
            if ($remainder > 0) {
                $result .= ' ' . self::numberToWords($remainder);
            }
            return $result;
        } elseif ($number < 10000000) {
            $lakhs = floor($number / 100000);
            $remainder = $number % 100000;
            $result = self::numberToWords($lakhs) . ' Lakh';
            if ($remainder > 0) {
                $result .= ' ' . self::numberToWords($remainder);
            }
            return $result;
        } else {
            $crores = floor($number / 10000000);
            $remainder = $number % 10000000;
            $result = self::numberToWords($crores) . ' Crore';
            if ($remainder > 0) {
                $result .= ' ' . self::numberToWords($remainder);
            }
            return $result;
        }
    }

    /**
     * Generate voucher number based on transaction type
     *
     * @param int $transType Transaction type (1=Payment, 2=Receive, 3=Journal, 4=Contra)
     * @param string $prefix Optional prefix for voucher number
     * @return string
     */
    public static function generateVoucherNumber($transType, $prefix = '')
    {
        $typePrefixes = [
            1 => 'PV', // Payment Voucher
            2 => 'RV', // Receive Voucher
            3 => 'JV', // Journal Voucher
            4 => 'CV'  // Contra Voucher
        ];

        $prefix = ($prefix ?: ($typePrefixes[$transType] ?? 'V')) . '-';
        
        // Get the last voucher number for this type
        $lastVoucher = AccountTransaction::where('trans_type', $transType)
            ->where('voucher_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastVoucher) {
            // Extract number from last voucher
            $lastNumber = (int) substr($lastVoucher->voucher_no, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Format currency amount
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($amount, $currency = 'BDT')
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Validate date range
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public static function validateDateRange($dateFrom, $dateTo)
    {
        $errors = [];

        if (empty($dateFrom)) {
            $errors[] = 'From date is required';
        }

        if (empty($dateTo)) {
            $errors[] = 'To date is required';
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $fromDate = Carbon::parse($dateFrom);
            $toDate = Carbon::parse($dateTo);

            if ($fromDate->gt($toDate)) {
                $errors[] = 'From date cannot be greater than To date';
            }

            // Check if date range is not more than 1 year
            if ($fromDate->diffInDays($toDate) > 365) {
                $errors[] = 'Date range cannot exceed 365 days';
            }
        }

        return $errors;
    }

    /**
     * Calculate debit and credit totals
     *
     * @param array $transactions
     * @return array
     */
    public static function calculateTotals($transactions)
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($transactions as $transaction) {
            $totalDebit += $transaction['debit'] ?? 0;
            $totalCredit += $transaction['credit'] ?? 0;
        }

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => $totalDebit == $totalCredit
        ];
    }

    /**
     * Validate transaction balance
     *
     * @param array $transactions
     * @return array
     */
    public static function validateTransactionBalance($transactions)
    {
        $totals = self::calculateTotals($transactions);
        
        $errors = [];
        
        if (!$totals['is_balanced']) {
            $errors[] = 'Transaction is not balanced. Debit: ' . 
                       self::formatCurrency($totals['total_debit']) . 
                       ', Credit: ' . self::formatCurrency($totals['total_credit']);
        }

        if ($totals['total_debit'] == 0 && $totals['total_credit'] == 0) {
            $errors[] = 'At least one transaction entry is required';
        }

        return $errors;
    }

    /**
     * Get transaction type name
     *
     * @param int $transType
     * @return string
     */
    public static function getTransactionTypeName($transType)
    {
        $types = [
            1 => 'Payment Voucher',
            2 => 'Receive Voucher',
            3 => 'Journal Voucher',
            4 => 'Contra Voucher'
        ];

        return $types[$transType] ?? 'Unknown';
    }

    /**
     * Format date for display
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'd-m-Y')
    {
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Get financial year
     *
     * @param string $date
     * @return string
     */
    public static function getFinancialYear($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        
        if ($date->month >= 7) {
            return $date->year . '-' . ($date->year + 1);
        } else {
            return ($date->year - 1) . '-' . $date->year;
        }
    }

    /**
     * Sanitize input data
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeInput($data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(strip_tags($value));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Generate unique reference number
     *
     * @param string $prefix
     * @return string
     */
    public static function generateReferenceNumber($prefix = 'REF')
    {
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Check if account is active
     *
     * @param int $accountId
     * @return bool
     */
    public static function isAccountActive($accountId)
    {
        // This would need to be implemented based on your account model structure
        // For now, returning true as placeholder
        return true;
    }

    /**
     * Get account balance
     *
     * @param int $accountId
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public static function getAccountBalance($accountId, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('account_transaction_details')
            ->where('subsidiary_ledger_id', $accountId);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        $result = $query->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        return [
            'debit' => $result->total_debit ?? 0,
            'credit' => $result->total_credit ?? 0,
            'balance' => ($result->total_debit ?? 0) - ($result->total_credit ?? 0)
        ];
    }

    /**
     * Log transaction activity
     *
     * @param string $action
     * @param array $data
     * @param int $userId
     * @return void
     */
    public static function logActivity($action, $data = [], $userId = null)
    {
        \Log::info('Account Transaction Activity', [
            'action' => $action,
            'data' => $data,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now()
        ]);
    }

    /**
     * Store Receive Voucher
     *
     * @param array $data
     * @return array
     */
    public static function receiveVoucherStore($data)
    {
        try {
            DB::beginTransaction();

            // Generate voucher number
            $voucherNo = self::generateVoucherNumber(2); // 2 = Receive Voucher

            // Calculate total amount
            $totalAmount = array_sum(array_column($data['line_items'], 'amount'));

            // Create main transaction
            $transaction = AccountTransaction::create([
                'voucher_no' => $voucherNo,
                'trans_date' => $data['trans_date'],
                'trans_type' => 2, // Receive Voucher
                'amount' => $totalAmount,
                'comments' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
                'status' => 1,
            ]);

            // Process line items
            foreach ($data['line_items'] as $item) {
                $debitLedger = SubsidiaryLedger::where('ledger_code',$item['dr_ledger_id'])->first();
                $creditLedger = SubsidiaryLedger::where('ledger_code',$item['cr_ledger_id'])->first();

                // Create transaction detail
                $detail = AccountTransactionDetail::create([
                    'acc_transaction_id' => $transaction->id,
                    'dr_adjust_trans_id' => 0,
                    'dr_adjust_voucher_no' => null,
                    'dr_adjust_voucher_date' => null,
                    'cr_adjust_trans_id' => 0,
                    'cr_adjust_voucher_no' => null,
                    'cr_adjust_voucher_date' => null,
                    'dr_gl_ledger' => $debitLedger->group_id,
                    'dr_sub_ledger' => $debitLedger->id,
                    'cr_gl_ledger' => $creditLedger->group_id,
                    'cr_sub_ledger' => $creditLedger->id,
                    'ref_sub_ledger' => 0,
                    'amount' => $item['amount'],
                    'created_by' => auth()->id(),
                ]);

                $tran_details_id = $detail->id;

                // Double Entry in SubsidiaryCalculation
                SubsidiaryCalculation::create([
                    'particular' => $creditLedger->id,
                    'particular_control_group' => $creditLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $debitLedger->id,
                    'gl_ledger' => $debitLedger->group_id,
                    'nature_id' => $debitLedger->account_type ?? 2,
                    'debit_amount' => $item['amount'],
                    'credit_amount' => 0,
                    'transaction_type' => 2,
                    'transaction_id' => $transaction->id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);

                SubsidiaryCalculation::create([
                    'particular' => $debitLedger->id,
                    'particular_control_group' => $debitLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $creditLedger->id,
                    'gl_ledger' => $creditLedger->group_id,
                    'nature_id' => $creditLedger->account_type ?? 2,
                    'debit_amount' => 0,
                    'credit_amount' => $item['amount'],
                    'transaction_type' => 2,
                    'transaction_id' => $transaction->id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            self::logActivity('Receive Voucher Created', [
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount
            ]);

            return [
                'success' => true,
                'message' => 'Receive voucher created successfully',
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount
            ];

        } catch (\Exception $e) {
            DB::rollback();
            self::logActivity('Receive Voucher Error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Error creating receive voucher: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store Payment Voucher
     *
     * @param array $data
     * @return array
     */
    public static function paymentVoucherStore($data)
    {
        try {
            DB::beginTransaction();

            // Generate voucher number
            $voucherNo = self::generateVoucherNumber(1); // 1 = Payment Voucher

            // Create main transaction
            $transaction = AccountTransaction::create([
                'voucher_no' => $voucherNo,
                'trans_date' => $data['trans_date'],
                'trans_type' => 1, // Payment Voucher
                'amount' => $data['total_amount'],
                'comments' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
                'status' => 1,
            ]);

            $transaction_id = $transaction->id;

            // Process line items
            foreach ($data['line_items'] as $item) {
              
                $debitLedger = SubsidiaryLedger::where('ledger_code',$item['dr_ledger_id'])->first();
                $creditLedger = SubsidiaryLedger::where('ledger_code',$item['cr_ledger_id'])->first();
                // dd($item,$debitLedger,$creditLedger);
                // Create transaction detail
                $detail = AccountTransactionDetail::create([
                    'acc_transaction_id' => $transaction_id,
                    'dr_adjust_trans_id' => 0,
                    'dr_adjust_voucher_no' => null,
                    'dr_adjust_voucher_date' => null,
                    'cr_adjust_trans_id' => 0,
                    'cr_adjust_voucher_no' => null,
                    'cr_adjust_voucher_date' => null,
                    'dr_gl_ledger' => $debitLedger->group_id,
                    'dr_sub_ledger' => $debitLedger->id,
                    'cr_gl_ledger' => $creditLedger->group_id,
                    'cr_sub_ledger' => $creditLedger->id,
                    'ref_sub_ledger' => 0,
                    'amount' => $item['amount'],
                    'created_by' => auth()->id(),
                ]);

                $tran_details_id = $detail->id;

                // Double Entry in SubsidiaryCalculation
                SubsidiaryCalculation::create([
                    'particular' => $creditLedger->id,
                    'particular_control_group' => $creditLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $debitLedger->id,
                    'gl_ledger' => $debitLedger->group_id,
                    'nature_id' => $debitLedger->account_type ?? 2,
                    'debit_amount' => $item['amount'],
                    'credit_amount' => 0,
                    'transaction_type' => 2,
                    'transaction_id' => $transaction_id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);

                SubsidiaryCalculation::create([
                    'particular' => $debitLedger->id,
                    'particular_control_group' => $debitLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $creditLedger->id,
                    'gl_ledger' => $creditLedger->group_id,
                    'nature_id' => $creditLedger->account_type ?? 2,
                    'debit_amount' => 0,
                    'credit_amount' => $item['amount'],
                    'transaction_type' => 2,
                    'transaction_id' => $transaction_id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            self::logActivity('Payment Voucher Created', [
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction_id,
                'amount' => $data['total_amount']
            ]);

            return [
                'success' => true,
                'message' => 'Payment voucher created successfully',
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction_id,
                'amount' => $data['total_amount']
            ];

        } catch (\Exception $e) {
            DB::rollback();
            self::logActivity('Payment Voucher Error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Error creating payment voucher: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store Journal Voucher
     *
     * @param array $data
     * @return array
     */
    public static function journalVoucherStore($data)
    {
        
        try {
            DB::beginTransaction();

            // Generate voucher number
            $voucherNo = self::generateVoucherNumber(3); // 3 = Journal Voucher

            // Calculate total amount
            $totalAmount = array_sum(array_column($data['line_items'], 'amount'));

            // Create main transaction
            $transaction = AccountTransaction::create([
                'voucher_no' => $voucherNo,
                'trans_date' => $data['trans_date'],
                'trans_type' => 3, // Journal Voucher
                'amount' => $totalAmount,
                'comments' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
                'status' => 1,
            ]);

            // Process line items
            foreach ($data['line_items'] as $item) {
               
                $debitLedger = SubsidiaryLedger::where('ledger_code',$item['dr_ledger_id'])->first();
                $creditLedger = SubsidiaryLedger::where('ledger_code',$item['cr_ledger_id'])->first();
                // Create transaction detail
                $detail = AccountTransactionDetail::create([
                    'acc_transaction_id' => $transaction->id,
                    'dr_adjust_trans_id' => 0,
                    'dr_adjust_voucher_no' => null,
                    'dr_adjust_voucher_date' => null,
                    'cr_adjust_trans_id' => 0,
                    'cr_adjust_voucher_no' => null,
                    'cr_adjust_voucher_date' => null,
                    'dr_gl_ledger' => $debitLedger->group_id,
                    'dr_sub_ledger' => $debitLedger->id,
                    'cr_gl_ledger' => $creditLedger->group_id,
                    'cr_sub_ledger' => $creditLedger->id,
                    'ref_sub_ledger' => 0,
                    'amount' => $item['amount'],
                    'created_by' => auth()->id(),
                ]);

                $tran_details_id = $detail->id;

                // Double Entry in SubsidiaryCalculation
                SubsidiaryCalculation::create([
                    'particular' => $creditLedger->id,
                    'particular_control_group' => $creditLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $debitLedger->id,
                    'gl_ledger' => $debitLedger->group_id,
                    'nature_id' => $debitLedger->account_type ?? 2,
                    'debit_amount' => $item['amount'],
                    'credit_amount' => 0,
                    'transaction_type' => 2,
                    'transaction_id' => $transaction->id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);

                SubsidiaryCalculation::create([
                    'particular' => $debitLedger->id,
                    'particular_control_group' => $debitLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $creditLedger->id,
                    'gl_ledger' => $creditLedger->group_id,
                    'nature_id' => $creditLedger->account_type ?? 2,
                    'debit_amount' => 0,
                    'credit_amount' => $item['amount'],
                    'transaction_type' => 2,
                    'transaction_id' => $transaction->id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            self::logActivity('Journal Voucher Created', [
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount
            ]);

            return [
                'success' => true,
                'message' => 'Journal voucher created successfully',
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount
            ];

        } catch (\Exception $e) {
            DB::rollback();
            self::logActivity('Journal Voucher Error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Error creating journal voucher: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store Contra Voucher
     *
     * @param array $data
     * @return array
     */
    public static function contraVoucherStore($data)
    {
        try {
            DB::beginTransaction();

            // Generate voucher number
            $voucherNo = self::generateVoucherNumber(4); // 4 = Contra Voucher

            // Create main transaction
            $transaction = AccountTransaction::create([
                'voucher_no' => $voucherNo,
                'trans_date' => $data['trans_date'],
                'trans_type' => 4, // Contra Voucher
                'amount' => $data['total_amount'],
                'comments' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
                'status' => 1,
            ]);

            $transaction_id = $transaction->id;

            // Process line items
            foreach ($data['line_items'] as $item) {
                $fromLedger = SubsidiaryLedger::where('ledger_code',$item['dr_ledger_id'])->first();
                $toLedger = SubsidiaryLedger::where('ledger_code',$item['cr_ledger_id'])->first();

                // Create transaction detail
                $detail = AccountTransactionDetail::create([
                    'acc_transaction_id' => $transaction_id,
                    'dr_adjust_trans_id' => 0,
                    'dr_adjust_voucher_no' => null,
                    'dr_adjust_voucher_date' => null,
                    'cr_adjust_trans_id' => 0,
                    'cr_adjust_voucher_no' => null,
                    'cr_adjust_voucher_date' => null,
                    'dr_gl_ledger' => $fromLedger->group_id,
                    'dr_sub_ledger' => $fromLedger->id,
                    'cr_gl_ledger' => $toLedger->group_id,
                    'cr_sub_ledger' => $toLedger->id,
                    'ref_sub_ledger' => 0,
                    'amount' => $item['amount'],
                    'created_by' => auth()->id(),
                ]);

                $tran_details_id = $detail->id;

                // Double Entry in SubsidiaryCalculation
                SubsidiaryCalculation::create([
                    'particular' => $toLedger->id,
                    'particular_control_group' => $toLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $fromLedger->id,
                    'gl_ledger' => $fromLedger->group_id,
                    'nature_id' => $fromLedger->account_type ?? 2,
                    'debit_amount' => $item['amount'],
                    'credit_amount' => 0,
                    'transaction_type' => 2,
                    'transaction_id' => $transaction_id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);

                SubsidiaryCalculation::create([
                    'particular' => $fromLedger->id,
                    'particular_control_group' => $fromLedger->group_id,
                    'trans_date' => $data['trans_date'],
                    'sub_ledger' => $toLedger->id,
                    'gl_ledger' => $toLedger->group_id,
                    'nature_id' => $toLedger->account_type ?? 2,
                    'debit_amount' => 0,
                    'credit_amount' => $item['amount'],
                    'transaction_type' => 2,
                    'transaction_id' => $transaction_id,
                    'tran_details_id' => $tran_details_id,
                    'adjust_trans_id' => null,
                    'adjust_voucher_date' => null,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            self::logActivity('Contra Voucher Created', [
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction_id,
                'amount' => $data['total_amount']
            ]);

            return [
                'success' => true,
                'message' => 'Contra voucher created successfully',
                'voucher_no' => $voucherNo,
                'transaction_id' => $transaction_id,
                'amount' => $data['total_amount']
            ];

        } catch (\Exception $e) {
            DB::rollback();
            self::logActivity('Contra Voucher Error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Error creating contra voucher: ' . $e->getMessage()
            ];
        }
    }
}
