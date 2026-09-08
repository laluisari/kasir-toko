<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleDocument;
use App\Services\ThermalPrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrintController extends Controller
{
    public function printReceipt(SaleDocument $saleDocument, ThermalPrintService $thermalPrintService): JsonResponse
    {
        Log::info('Print Endpoint: Request received', [
            'user_id' => Auth::id(),
            'sale_id' => $saleDocument->id,
            'invoice' => $saleDocument->invoice_number,
        ]);

        $result = $thermalPrintService->printSaleDocument($saleDocument);

        Log::info('Print Endpoint: Response sent', [
            'user_id' => Auth::id(),
            'sale_id' => $saleDocument->id,
            'success' => $result['success'],
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function printBarcode(Request $request, Product $product, ThermalPrintService $thermalPrintService): JsonResponse
    {
        Log::info('Print Barcode Endpoint: request received', [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'barcode' => $product->barcode,
        ]);

        $copies = (int) $request->integer('copies', 1);

        $result = $thermalPrintService->printBarcode($product, $copies);

        Log::info('Print Barcode Endpoint: response sent', [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'success' => $result['success'],
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function barcodeLabel(Request $request, Product $product): \Illuminate\Contracts\View\View
    {
        $copies = (int) max(1, min(100, $request->integer('copies', 1)));

        return view('admin.products.barcode-label', compact('product', 'copies'));
    }
}
