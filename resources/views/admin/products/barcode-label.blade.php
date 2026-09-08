@extends('layouts.main')

@section('title', 'Label Barcode')
@section('subTitle', $product->name)

@section('content')
<div class="container py-3">
    <div class="text-center mb-4 d-print-none">
        <h5 class="fw-semibold">Print Preview Label Barcode</h5>
        <p class="text-secondary-light text-sm">Menampilkan {{ $copies }} label. Klik print atau tunggu dialog otomatis.</p>
        <button class="btn btn-dark btn-sm px-4 py-2 fw-semibold" onclick="window.print()">🖨️ Print Sekarang</button>
    </div>

    <div id="labelArea" class="mx-auto" style="width: 58mm;">
        @for ($i = 0; $i < $copies; $i++)
        <div class="barcode-label">
            <div class="label-store">{{ config('thermal.store_name', 'TOKO SERBAGUNA') }}</div>
            <div class="label-name">{{ $product->name }}</div>
            <div class="label-barcode">
                <svg id="code-{{ $i }}" class="code-img" data-code="{{ $barcode = trim((string) $product->barcode) }}"></svg>
            </div>
            <div class="label-price">Rp {{ number_format((int) $product->selling_price, 0, ',', '.') }}</div>
        </div>
        @endfor
    </div>
</div>
@endsection

@section('styles')
<style>
    body {
        background: #f1f5f9;
    }

    #labelArea {
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        padding: 4mm;
        border-radius: 4px;
    }

    .barcode-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        font-family: 'Courier New', Courier, monospace;
        color: #000;
        padding: 2mm 0;
    }

    .barcode-label + .barcode-label {
        border-top: 1px dashed #bbb;
        margin-top: 3mm;
        padding-top: 5mm;
    }

    .label-store {
        font-size: 3.5mm;
        font-weight: 700;
        letter-spacing: 0.5mm;
    }

    .label-name {
        font-size: 2.8mm;
        font-weight: 700;
        margin: 1.5mm 0;
        text-transform: uppercase;
        max-width: 52mm;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .label-barcode {
        margin: 1mm 0;
    }

    .code-img {
        height: 18mm;
        width: 50mm;
    }

    .label-price {
        font-size: 3.8mm;
        font-weight: 700;
        margin-top: 1mm;
    }

    @media print {
        @page {
            size: 58mm auto;
            margin: 0;
        }

        body * {
            visibility: hidden;
        }

        #labelArea,
        #labelArea * {
            visibility: visible;
        }

        #labelArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 58mm;
            padding: 0;
            margin: 0;
            box-shadow: none;
            border-radius: 0;
        }

        .d-print-none {
            display: none !important;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.querySelectorAll('.code-img').forEach(svg => {
        const code = svg.dataset.code || '';
        let format = 'CODE128';
        if (/^\d{13}$/.test(code)) format = 'EAN13';
        else if (/^\d{8}$/.test(code)) format = 'EAN8';

        JsBarcode(svg, code, {
            format: format,
            lineColor: '#000',
            width: 1.2,
            height: 52,
            displayValue: true,
            fontSize: 15,
            font: 'Courier',
            margin: 0
        });
    });

    window.onload = function () {
        setTimeout(function () {
            window.print();
        }, 600);
    };
</script>
@endsection