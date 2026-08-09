<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk-{{ $transaction->invoice_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
            width: 280px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-detail {
            font-size: 10px;
            color: #333;
            margin-left: 10px;
            margin-bottom: 5px;
        }
        .btn-print {
            background: #047857;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 15px;
            width: 100%;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 5px; width: 100%; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Struk</button>

    <div class="text-center">
        <div class="bold" style="font-size: 14px;">DESA WISATA GETAS</div>
        <div>Kec. Singorojo, Kab. Kendal</div>
        <div>Struk Pembayaran POS</div>
    </div>

    <div class="divider"></div>

    <div>
        <div>No: <span class="bold">{{ $transaction->invoice_number }}</span></div>
        <div>Tgl: {{ $transaction->created_at->format('d/m/Y H:i') }}</div>
        <div>Kasir: {{ $transaction->user->nama ?? ($transaction->user->name ?? 'Kasir') }}</div>
        <div>Metode: <span class="bold" style="text-transform: uppercase;">{{ $transaction->payment_method }}</span></div>
    </div>

    <div class="divider"></div>

    @foreach($transaction->items as $item)
        <div class="item-row bold">
            <span>{{ $item->product_name }}</span>
            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="item-detail">
            {{ $item->quantity }} x @ Rp {{ number_format($item->price, 0, ',', '.') }}
        </div>
    @endforeach

    <div class="divider"></div>

    <div class="item-row bold" style="font-size: 13px;">
        <span>TOTAL</span>
        <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
    </div>

    @if($transaction->payment_method === 'cash')
        <div class="item-row" style="margin-top: 4px;">
            <span>Bayar (Tunai)</span>
            <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="item-row">
            <span>Kembali</span>
            <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>
    @endif

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 15px; font-size: 11px;">
        <div>Terima Kasih atas Kunjungan Anda!</div>
        <div style="margin-top: 4px;">~ Desa Wisata Getas ~</div>
    </div>

    <script>
        // Auto trigger print when opened
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>

</body>
</html>
