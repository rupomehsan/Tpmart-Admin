<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventory\Models\ProductSupplier;
use App\Http\Controllers\Inventory\Models\ProductWarehouse;
use App\Http\Controllers\Inventory\Models\ProductWarehouseRoom;
use App\Http\Controllers\Inventory\Models\ProductWarehouseRoomCartoon;
use App\Http\Controllers\Inventory\Models\ProductPurchaseOrder;
use App\Http\Controllers\Inventory\Models\ProductPurchaseOrderProduct;
use App\Http\Controllers\Inventory\Models\ProductPurchaseQuotation;
use App\Http\Controllers\Inventory\Models\ProductPurchaseQuotationProduct;
use App\Http\Controllers\Inventory\Models\ProductPurchaseOtherCharge;
use App\Http\Controllers\Inventory\Models\ProductStock;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Account\AccountsHelper;
use App\Http\Controllers\Account\Models\AccountTransaction;
use App\Http\Controllers\Account\Models\SubsidiaryLedger;
use App\Http\Controllers\Account\Models\AccountTransactionDetail;
use App\Http\Controllers\Account\Models\SubsidiaryCalculation;
use App\Http\Controllers\Account\Models\AccountsConfiguration;


class ProductPurchaseOrderController extends Controller
{
    public function addNewPurchaseProductOrder()
    {
        $products = Product::where('status', 'active')->get();
        $suppliers = ProductSupplier::where('status', 'active')->get();
        $productWarehouses = ProductWarehouse::where('status', 'active')->get();
        $productWarehouseRooms = ProductWarehouseRoom::where('status', 'active')->get();
        $productWarehouseRoomCartoons = ProductWarehouseRoomCartoon::where('status', 'active')->get();
        $other_charges_types = ProductPurchaseOtherCharge::where('status', 'active')->get();
        return view('backend.purchase_product_order.create', compact('products', 'suppliers', 'productWarehouses', 'productWarehouseRooms', 'productWarehouseRoomCartoons', 'other_charges_types'));
    }

    public function calc_other_charges($other_charges, $subtotal)
    {

        $percent_total = 0;
        $fixed_total = 0;

        foreach ($other_charges as $charge) {
            if ($charge['type'] === 'percent') {
                $percent_total += ($subtotal * $charge['amount']) / 100;
            } else {
                $fixed_total += $charge['amount'];
            }
        }

        $total = $percent_total + $fixed_total;
        return $total;
    }

    public function saveNewPurchaseProductOrder(Request $request)
    {
        // dd(request()->all());
        // $request->validate([
        //     'title' => ['required', 'string', 'max:255'],
        //     'product_warehouse_id' => ['required'],
        //     'product_warehouse_room_id' => ['required'],
        // ], [
        //     'title.required' => 'title is required.',
        // ]);

        $other_charge_total = $this->calc_other_charges(request()->other_charges, request()->subtotal);

        $random_no = random_int(100, 999) . random_int(1000, 9999);
        $slug = Str::orderedUuid() . uniqid() . $random_no;


        $user = auth()->user();
        $order = new ProductPurchaseOrder();
        $order->product_warehouse_id = request()->purchase_product_warehouse_id;
        $order->product_warehouse_room_id = request()->purchase_product_warehouse_room_id;
        $order->product_warehouse_room_cartoon_id = request()->purchase_product_warehouse_room_cartoon_id;
        $order->product_supplier_id = request()->supplier_id;
        $order->date = request()->purchase_date;

        // $order->other_charge_type = request()->other_charges_type;
        // $order->other_charge_percentage = request()->other_charges_input_amount;
        // $order->other_charge_amount = request()->other_charges_amt;

        $order->other_charge_type = json_encode(request()->other_charges);
        // $order->other_charge_percentage = request()->other_charges_input_amount;
        $order->other_charge_amount = $other_charge_total;




        $order->discount_type = request()->discount_to_all_type;
        $order->discount_amount = request()->discount_on_all;
        $order->calculated_discount_amount = request()->discount_to_all_amt;
        $order->round_off = request()->total_round_off_amt;
        $order->subtotal = request()->subtotal_amt;
        $order->total = request()->grand_total_amt;
        $order->note = request()->purchase_note;
        $order->payment_status = request()->payment_status ?? 'due';
        // $order->code = $new_code;
        // $order->reference = $new_reference;
        $order->order_status = 'pending';
        $order->creator = $user->id;

        $order->status = 'active';
        $order->created_at = Carbon::now();
        $order->save();



        foreach ($request->product as $productItem) {

            $unit_price = $productItem['prices'];
            $discount_percent = $productItem['discounts'];
            $tax_percent = $productItem['taxes'];
            $discounted_price = $unit_price * (1 - ($discount_percent / 100));
            $final_price_per_unit = $discounted_price * (1 + ($tax_percent / 100));

            $product_slug = Str::orderedUuid() . $random_no . $order->id . uniqid();

            // $product = Product::where('id', $productItem['id'])->first();
            ProductPurchaseOrderProduct::create([
                'product_warehouse_id' => request()->purchase_product_warehouse_id,
                'product_warehouse_room_id' => request()->purchase_product_warehouse_room_id,
                'product_warehouse_room_cartoon_id' => request()->purchase_product_warehouse_room_cartoon_id,
                'product_supplier_id' => request()->supplier_id,
                'product_purchase_order_id' => $order->id,
                'product_id' => $productItem['id'],
                'product_name' => $productItem['name'],
                'qty' => $productItem['quantities'],
                'product_price' => $productItem['prices'],
                'discount_type' => 'in_percentage',
                'discount_amount' => $productItem['discounts'],
                'tax' => $productItem['taxes'],
                'purchase_price' => $final_price_per_unit,
                'slug' => $product_slug,
            ]);
        }

        $last = ProductPurchaseOrder::orderBy('id', 'desc')->first();
        if ($last) {
            $new_code = "OP" . $order->id . ($last->code + 1) . $random_no;
        } else {
            $new_code = "OP" . $order->id . "00001" . $random_no;
        }

        $reference = ProductPurchaseOrder::orderBy('id', 'desc')->first();
        if ($reference) {
            $new_reference = $order->id . ($reference->reference + 1) . $random_no;
        } else {
            $new_reference = "001" . $order->id . $random_no;
        }

        $order->code = $new_code;
        $order->reference = $new_reference;
        $order->slug = $order->id . $slug;
        $order->save();


        Toastr::success('Quotation has been added successfully!', 'Success');
        return back();
    }

    public function viewAllPurchaseProductOrder(Request $request)
    {
        if ($request->ajax()) {

            $data = ProductPurchaseOrder::with('creator', 'order_products')
                // ->where('status', 'active')
                ->orderBy('id', 'desc') // Order by the ID
                ->get();

            // dd($data);
            return Datatables::of($data)
                // ->editColumn('creator', function ($data) {
                //     return $data->creator ? $data->creator->name : 'N/A'; // Access creator name
                // })
                ->editColumn('status', function ($data) {
                    return $data->status == "active" ? 'Active' : 'Inactive';
                })
                ->editColumn('created_at', function ($data) {
                    return date("Y-m-d", strtotime($data->created_at));
                })
                ->addIndexColumn()
                // ->addColumn('action', function ($data) {
                //     $btn = '<a href="' . url('edit/purchase-product/quotation') . '/' . $data->slug . '" class="btn-sm btn-warning rounded editBtn"><i class="fas fa-edit"></i></a>';
                //     $btn .= ' <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $data->slug . '" data-original-title="Delete" class="btn-sm btn-danger rounded deleteBtn"><i class="fas fa-trash-alt"></i></a>';
                //     return $btn;
                // })
                ->addColumn('action', function ($data) {
                    $btn = '<div class="dropdown">';
                    $btn .= '<button class="btn-sm btn-primary dropdown-toggle rounded" type="button" id="actionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $btn .= 'Action';
                    $btn .= '</button>';
                    $btn .= '<div class="dropdown-menu" aria-labelledby="actionDropdown">';
                    $btn .= '<a class="dropdown-item" href="' . url('edit/purchase-product/order') . '/' . $data->slug . '"><i class="fas fa-edit"></i> Edit</a>';
                    $btn .= '<a class="dropdown-item" href="' . url('edit/purchase-product/order/confirm') . '/' . $data->slug . '"><i class="fas fa-edit"></i> Confirm Order</a>';
                    $btn .= '<a class="dropdown-item" href="javascript:void(0)" data-toggle="tooltip" data-id="' . $data->slug . '" data-original-title="Delete" class="deleteBtn"><i class="fas fa-trash-alt"></i> Delete</a>';
                    $btn .= '</div>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('backend.purchase_product_order.view');
    }



    public function editPurchaseProductOrder($slug)
    {
        // dd($slug);
        $data = ProductPurchaseOrder::where('slug', $slug)->first();
        $productWarehouses = ProductWarehouse::where('status', 'active')->get();
        $productWarehouseRooms = ProductWarehouseRoom::where('product_warehouse_id', $data->product_warehouse_id)->where('status', 'active')->get();
        $productWarehouseRoomCartoon = ProductWarehouseRoomCartoon::where('product_warehouse_id', $data->product_warehouse_id)->where('product_warehouse_room_id', $data->product_warehouse_room_id)->where('status', 'active')->get();
        $suppliers = ProductSupplier::where('status', 'active')->get();

        $other_charges_types = ProductPurchaseOtherCharge::where('status', 'active')->get();

        return view('backend.purchase_product_order.edit', compact('data', 'productWarehouses', 'productWarehouseRooms', 'productWarehouseRoomCartoon', 'suppliers', 'other_charges_types'));
    }

    public function apiEditPurchaseProduct($slug)
    {
        // dd($slug);
        $data = ProductPurchaseOrder::with('order_products')->where('slug', $slug)->first();
        $productWarehouses = ProductWarehouse::where('status', 'active')->get();
        $productWarehouseRooms = ProductWarehouseRoom::where('product_warehouse_id', $data->product_warehouse_id)->where('status', 'active')->get();
        $productWarehouseRoomCartoon = ProductWarehouseRoomCartoon::where('product_warehouse_id', $data->product_warehouse_id)->where('product_warehouse_room_id', $data->product_warehouse_room_id)->where('status', 'active')->get();
        $suppliers = ProductSupplier::where('status', 'active')->get();

        return response()->json([
            'data' => $data,
        ]);

        // return view('backend.purchase_product_quotation.edit', compact('data', 'productWarehouses', 'productWarehouseRooms', 'productWarehouseRoomCartoon', 'suppliers'));
    }

    public function updatePurchaseProductOrder(Request $request)
    {
        // dd(request()->all());
        $other_charge_total = $this->calc_other_charges(request()->other_charges, request()->subtotal);

        $order = ProductPurchaseOrder::where('id', request()->purchase_product_order_id)->first();

        $user = auth()->user();
        $order->product_warehouse_id = request()->purchase_product_warehouse_id;
        $order->product_warehouse_room_id = request()->purchase_product_warehouse_room_id;
        $order->product_warehouse_room_cartoon_id = request()->purchase_product_warehouse_room_cartoon_id;
        $order->product_supplier_id = request()->supplier_id;
        $order->product_purchase_quotation_id = $order->product_purchase_quotation_id ?? '';
        $order->date = request()->purchase_date;

        // $order->other_charge_type = request()->other_charges_type;
        // $order->other_charge_percentage = request()->other_charges_input_amount;
        // $order->other_charge_amount = request()->other_charges_amt;

        $order->other_charge_type = json_encode(request()->other_charges);
        // $order->other_charge_percentage = request()->other_charges_input_amount;
        $order->other_charge_amount = $other_charge_total;



        $order->discount_type = request()->discount_to_all_type;
        $order->discount_amount = request()->discount_on_all;
        $order->calculated_discount_amount = request()->discount_to_all_amt;
        $order->round_off = request()->total_round_off_amt;
        $order->subtotal = request()->subtotal;
        $order->total = request()->grand_total_amt;
        $order->note = request()->purchase_note;
        $order->payment_status = request()->payment_status ?? 'due';
        // $order->is_ordered = 'pending';
        $order->creator = $user->id;
        $order->status = 'active';
        $order->created_at = Carbon::now();
        $order->save();

        // Get all existing product IDs for the given order
        $existingProductIds = ProductPurchaseOrderProduct::where('product_purchase_order_id', $order->id)
            ->pluck('product_id')
            ->toArray();

        $requestProductIds = []; // To track products in the request

        foreach ($request->product as $productItem) {
            if (!isset($productItem['id'])) {
                continue;
            }

            $unit_price = $productItem['prices'];
            $discount_percent = $productItem['discounts'];
            $tax_percent = $productItem['taxes'];
            $discounted_price = $unit_price * (1 - ($discount_percent / 100));
            $final_price_per_unit = $discounted_price * (1 + ($tax_percent / 100));

            $product_slug = Str::orderedUuid() . $order->id . uniqid();

            // Determine the product_id
            $product_id = !empty($productItem['product_id']) ? $productItem['product_id'] : $productItem['id'];

            // Track product IDs in the request
            $requestProductIds[] = $product_id;

            // Check if product already exists in the database
            $existingProduct = ProductPurchaseOrderProduct::where('product_purchase_order_id', $order->id)
                ->where('product_id', $product_id)
                ->first();
            
            if ($existingProduct) {
                // Update the existing record
                $existingProduct->update([
                    'product_warehouse_id' => $request->purchase_product_warehouse_id,
                    'product_warehouse_room_id' => $request->purchase_product_warehouse_room_id,
                    'product_warehouse_room_cartoon_id' => $request->purchase_product_warehouse_room_cartoon_id,
                    'product_supplier_id' => $request->supplier_id,
                    'product_name' => $productItem['name'],
                    'qty' => $productItem['quantities'],
                    'product_price' => $productItem['prices'],
                    'discount_type' => 'in_percentage',
                    'discount_amount' => $productItem['discounts'],
                    'tax' => $productItem['taxes'],
                    'purchase_price' => $final_price_per_unit,
                    'slug' => $product_slug,
                    'updated_at' => now(),
                ]);

            } else {
                // Insert new record
                ProductPurchaseOrderProduct::create([
                    'product_purchase_order_id' => $order->id,
                    'product_id' => $product_id,
                    'product_warehouse_id' => $request->purchase_product_warehouse_id,
                    'product_warehouse_room_id' => $request->purchase_product_warehouse_room_id,
                    'product_warehouse_room_cartoon_id' => $request->purchase_product_warehouse_room_cartoon_id,
                    'product_supplier_id' => $request->supplier_id,
                    'product_name' => $productItem['name'],
                    'qty' => $productItem['quantities'],
                    'product_price' => $productItem['prices'],
                    'discount_type' => 'in_percentage',
                    'discount_amount' => $productItem['discounts'],
                    'tax' => $productItem['taxes'],
                    'purchase_price' => $final_price_per_unit,
                    'slug' => $product_slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Delete records that are not present in the request
        $recordsToDelete = array_diff($existingProductIds, $requestProductIds);

        if (!empty($recordsToDelete)) {
            ProductPurchaseOrderProduct::where('product_purchase_order_id', $order->id)
                ->whereIn('product_id', $recordsToDelete)
                ->delete();
        }

        Toastr::success('Updation Has been Successful', 'Success!');
        return redirect()->route('ViewAllPurchaseProductOrder');
    }

    

    public function editPurchaseProductOrderConfirm($slug)
    {
        $data = ProductPurchaseOrder::with('order_products')->where('slug', $slug)->first();

        if ($data->order_status == 'received') {
            Toastr::error('Order has been received already!', 'Error');
            return back();
        }

        $data->order_status = 'received';
        $data->save();

        $random_no = random_int(100, 999) . random_int(1000, 9999);
        $slug = Str::orderedUuid() . uniqid() . $random_no;

        // Insert records into ProductStock for each product in the order
        foreach ($data->order_products as $product) {
            $product_stock = new ProductStock();
            $product_stock->product_warehouse_id = $product->product_warehouse_id;
            $product_stock->product_warehouse_room_id = $product->product_warehouse_room_id;
            $product_stock->product_warehouse_room_cartoon_id = $product->product_warehouse_room_cartoon_id;
            $product_stock->product_supplier_id = $product->product_supplier_id;
            $product_stock->product_purchase_order_id = $product->product_purchase_order_id;
            $product_stock->product_id = $product->product_id;
            $product_stock->date = $data->date;
            $product_stock->qty = $product->qty;
            $product_stock->purchase_price = $product->purchase_price;
            $product_stock->status = 'active';
            $product_stock->slug = $slug;
            $product_stock->save();



            $productModel = Product::where('id', $product->product_id)->first();
            // logger()->info('Product Model:', ['productModel' => $productModel]);
            if ($productModel) {
                // logger()->info('Product found:', ['product_id' => $productModel->id, 'current_stock' => $productModel->stock]);
                // Calculate average costing price
                $currentStock = $productModel->stock;
                $currentCostingPrice = $productModel->average_costing_price ?? $productModel->last_purchase_price ?? 0; // Only use existing costing_price, not MRP
                $newQty = $product_stock->qty;
                $newPurchasePrice = $product_stock->purchase_price;
                
                // Calculate weighted average costing price
                if ($currentStock > 0 && $currentCostingPrice > 0) {
                    // If we have existing stock with costing price, calculate weighted average
                    $totalCurrentValue = $currentStock * $currentCostingPrice;
                    $totalNewValue = $newQty * $newPurchasePrice;
                    $totalQty = $currentStock + $newQty;
                    $averageCostingPrice = ($totalCurrentValue + $totalNewValue) / $totalQty;
                } else {
                    // If no existing stock or costing price, use purchase price as costing price
                    $averageCostingPrice = $newPurchasePrice;
                }
                
                // Update product with new stock and average costing price
                $productModel->stock += $product_stock->qty;
                $productModel->last_purchase_price = $product_stock->purchase_price;
                $productModel->average_costing_price = $averageCostingPrice;
                
                // logger()->info('Updated stock:', ['product_id' => $productModel->id, 'new_stock' => $productModel->stock, 'average_costing_price' => $averageCostingPrice]);
                $productModel->update();
                // logger()->info('Product stock updated successfully.', ['product_id' => $productModel->id]);
            } else {
                logger()->warning('Product not found for stock update.', ['product_id' => $product->product_id]);
            }
        }

        // Generate voucher based on payment status
        try {
        // dd($data->payment_status);
            if ($data->payment_status == 'due') {
                // Generate Journal Voucher (JV) for due payment
                // This will show: Inventory increases, Supplier liability increases
                logger()->info('=== DUE PAYMENT TRANSACTION FLOW ===', [
                    'order_id' => $data->id,
                    'order_code' => $data->code,
                    'total_amount' => $data->total,
                    'message' => 'Inventory will increase, Supplier liability will increase (no cash movement)'
                ]);
                $this->generateJournalVoucher($data);
            } else {
                // Generate Payment Voucher for paid order
                // This will show: Cash decreases, Inventory increases, Supplier payment made
                logger()->info('=== PAID ORDER TRANSACTION FLOW ===', [
                    'order_id' => $data->id,
                    'order_code' => $data->code,
                    'total_amount' => $data->total,
                    'message' => 'Cash will decrease, Inventory will increase, Supplier payment will be made'
                ]);
                $this->generatePaymentVoucher($data);
            }
        } catch (\Exception $e) {
            logger()->error('Voucher generation failed: ' . $e->getMessage());
            Toastr::warning('Order confirmed but voucher generation failed: ' . $e->getMessage(), 'Warning');
        }

        Toastr::success('Order Confirmation Has been Successfull', 'Success!');
        
        // Show transaction summary to user
        if ($data->payment_status == 'paid') {
            Toastr::info('Cash decreased, Inventory increased, Supplier payment made', 'Transaction Summary');
        } else {
            Toastr::info('Inventory increased, Supplier liability created (No cash movement)', 'Transaction Summary');
        }
        
        return redirect()->route('ViewAllPurchaseProductOrder');
    }


    public function deletePurchaseProductOrder($slug)
    {
        $data = ProductPurchaseOrder::where('slug', $slug)->first();

        $data->delete();
        // $data->status = 'inactive';
        // $data->save();
        return response()->json([
            'success' => 'Deleted successfully!',
            'data' => 1
        ]);
    }


    public function searchProduct(Request $request)
    {
        // Check if the search query is an exact match
        $query = request()->query('query');
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'price', 'slug')
            ->limit(10)  // Limit to 200 products
            ->get();  // Use `get()` to return all matched products in a single request

        return response()->json($products);
    }



    private function generateJournalVoucher($purchaseOrder)
    {
        try {
            // === Ledgers ===
            $supplierLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_type', 'Supplier Payable')
                    ->orWhere('account_name', 'like', '%Supplier%')
                    ->orWhere('account_name', 'like', '%Vendor%');
                })
                ->where('is_active', 1)
                ->firstOrFail();

            $inventoryLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_name', 'like', '%inventory%')
                    ->orWhere('account_name', 'like', '%Stock%')
                    ->orWhere('account_name', 'like', '%purchase%');
                })
                ->where('is_active', 1)
                ->firstOrFail();

            $taxLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_name', 'like', '%Tax%')
                    ->orWhere('account_name', 'like', '%VAT%');
                })
                ->where('is_active', 1)
                ->first();

            $shippingLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_name', 'like', '%Shipping%')
                    ->orWhere('account_name', 'like', '%Freight%')
                    ->orWhere('account_name', 'like', '%Transport%');
                })
                ->where('is_active', 1)
                ->first();

            // === Amounts ===
            $totalProductAmount = 0;
            $totalTaxAmount = 0;
            $shippingAmount = $purchaseOrder->other_charge_amount ?? 0;

            foreach ($purchaseOrder->order_products as $product) {
                $unitPrice = $product->product_price;
                $discountPercent = $product->discount_amount ?? 0;
                $taxPercent = $product->tax ?? 0;

                $discountedPrice = $unitPrice * (1 - ($discountPercent / 100));
                $productAmount = $discountedPrice * $product->qty;
                $taxAmount = $productAmount * ($taxPercent / 100);

                $totalProductAmount += $productAmount;
                $totalTaxAmount += $taxAmount;
            }

            // === Journal Entries ===
            $lineItems = [];

            // 1. Inventory Stock (Debit)
            if ($totalProductAmount > 0) {
                $lineItems[] = [
                    'dr_ledger_id'  => $inventoryLedger->account_code,
                    'cr_ledger_id' => $supplierLedger->account_code,
                    'amount'           => $totalProductAmount+$totalTaxAmount+$shippingAmount,
                    'description'      => "Inventory Stock - Product Purchase"
                ];
            }

            // // 2. Tax (Debit)
            // if ($totalTaxAmount > 0 && $taxLedger) {
            //     $lineItems[] = [
            //         'dr_ledger_id'  => $taxLedger->account_code,
            //         'cr_ledger_id' => $supplierLedger->account_code,
            //         'amount'           => $totalTaxAmount,
            //         'description'      => "Tax - Purchase Tax"
            //     ];
            // }

            // // 3. Shipping (Debit)
            // if ($shippingAmount > 0 && $shippingLedger) {
            //     $lineItems[] = [
            //         'dr_ledger_id'  => $shippingLedger->account_code,
            //         'cr_ledger_id' => $supplierLedger->account_code,
            //         'amount'           => $shippingAmount,
            //         'description'      => "Shipping/Freight - Purchase Expense"
            //     ];
            // }

            $voucherData = [
                'trans_date' => $purchaseOrder->date,
                'remarks'    => 'Purchase (Due) - Order: ' . $purchaseOrder->code,
                'line_items' => $lineItems
            ];

            // dd($voucherData);
            $result = AccountsHelper::journalVoucherStore($voucherData);
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            logger()->info('Journal Voucher Created for Due Purchase', [
                'voucher_no' => $result['voucher_no'],
                'order_id'   => $purchaseOrder->id,
                'total'      => $totalProductAmount + $totalTaxAmount + $shippingAmount,
            ]);

        } catch (\Exception $e) {
            logger()->error('Journal Voucher generation failed', [
                'order_id' => $purchaseOrder->id,
                'error'    => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function generatePaymentVoucher($purchaseOrder)
    {
        try {
            // === Ledgers ===
            $supplierLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_type', 'Supplier Payable')
                    ->orWhere('account_name', 'like', '%Supplier%')
                    ->orWhere('account_name', 'like', '%Vendor%');
                })
                ->where('is_active', 1)
                // ->firstOrFail();
                ->first();

            $cashLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_type', 'Cash')
                    ->orWhere('account_type', 'Bank')
                    ->orWhere('account_name', 'like', '%Cash%')
                    ->orWhere('account_name', 'like', '%Bank%');
                })
                ->where('is_active', 1)
                // ->firstOrFail();
                ->first();

            $inventoryLedger = AccountsConfiguration::where('account_name', 'like', '%Inventory%')
                ->where('is_active', 1)
                // ->firstOrFail();
                ->first();

            $taxLedger = AccountsConfiguration::where('account_name', 'like', '%Tax%')
                ->where('is_active', 1)
                // ->firstOrFail();
                ->first();

            $shippingLedger = AccountsConfiguration::where(function ($q) {
                    $q->where('account_name', 'like', '%Shipping%')
                    ->orWhere('account_name', 'like', '%Freight%')
                    ->orWhere('account_name', 'like', '%Transport%');
                })
                ->where('is_active', 1)
                // ->firstOrFail();
                ->first();

            // === Amounts ===
            $totalProductAmount = 0;
            $totalTaxAmount = 0;
            $shippingAmount = $purchaseOrder->other_charge_amount ?? 0;

            foreach ($purchaseOrder->order_products as $product) {
                $unitPrice = $product->product_price;
                $discountPercent = $product->discount_amount ?? 0;
                $taxPercent = $product->tax ?? 0;

                $discountedPrice = $unitPrice * (1 - ($discountPercent / 100));
                $productAmount = $discountedPrice * $product->qty;
                $taxAmount = $productAmount * ($taxPercent / 100);

                $totalProductAmount += $productAmount;
                $totalTaxAmount += $taxAmount;
            }

            // === Payment Entries ===
            $lineItems = [];

            // 1. Inventory Stock (Debit)
            // if ($totalProductAmount > 0) {
            //     $lineItems[] = [
            //         'dr_ledger_id' => $inventoryLedger->account_code,
            //         'cr_ledger_id'        => $supplierLedger->account_code,
            //         'amount'            => $totalProductAmount,
            //         'description'       => "Inventory Stock - Product Purchase"
            //     ];
            // }

            // 1. Inventory Stock (Debit)
            if ($totalProductAmount > 0) {
                $lineItems[] = [
                    // 'dr_ledger_id' => $supplierLedger->account_code,
                    'dr_ledger_id' => $inventoryLedger->account_code,
                    'cr_ledger_id'        => $cashLedger->account_code,
                    'amount'            => $totalProductAmount,
                    'description'       => "Inventory Stock - Product Purchase"
                ];
            }

            // 2. Tax (Debit)
            if ($totalTaxAmount > 0 && $taxLedger) {
                $lineItems[] = [
                    'dr_ledger_id' => $taxLedger->account_code,
                    'cr_ledger_id'        => $cashLedger->account_code,
                    'amount'            => $totalTaxAmount,
                    'description'       => "Tax - Purchase Tax"
                ];
            }

            // 3. Shipping (Debit)
            if ($shippingAmount > 0 && $shippingLedger) {
                $lineItems[] = [
                    'dr_ledger_id' => $shippingLedger->account_code,
                    'cr_ledger_id'        => $cashLedger->account_code,
                    'amount'            => $shippingAmount,
                    'description'       => "Shipping Cost - Freight Charges"
                ];
            }

            // 4. Supplier Payable (Credit / payment)
            // $lineItems[] = [
            //     'dr_ledger_id' => $supplierLedger->account_code,
            //     'cr_ledger_id'        => $cashLedger->account_code,
            //     'amount'            => $purchaseOrder->total,
            //     'description'       => "Vendor Payment - Purchase Cleared"
            // ];

            $voucherData = [
                'trans_date'   => $purchaseOrder->date,
                'total_amount' => $purchaseOrder->total,
                'remarks'      => 'Purchase Payment - Order: ' . $purchaseOrder->code,
                'line_items'   => $lineItems
            ];

            $result = AccountsHelper::paymentVoucherStore($voucherData);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            logger()->info('Payment Voucher Created for Paid Purchase', [
                'voucher_no' => $result['voucher_no'],
                'order_id'   => $purchaseOrder->id,
                'total'      => $purchaseOrder->total,
            ]);

        } catch (\Exception $e) {
            logger()->error('Payment Voucher generation failed', [
                'order_id' => $purchaseOrder->id,
                'error'    => $e->getMessage()
            ]);
            throw $e;
        }
    }


}
