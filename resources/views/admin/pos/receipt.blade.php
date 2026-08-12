<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk-{{ $transaction->invoice_number }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 8px 10px;
            width: 58mm;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header-sub {
            font-size: 11px;
            color: #444;
            margin-top: 2px;
        }
        .divider {
            border-top: 1px dashed #999;
            margin: 8px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 3px;
            font-size: 12px;
        }
        .info-row .label { color: #333; flex-shrink: 0; }
        .info-row .value {
            text-align: right;
            font-weight: bold;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 12px;
        }
        .item-row .item-name { overflow-wrap: break-word; word-break: break-word; }
        .item-row .item-total { white-space: nowrap; }
        .item-detail {
            font-size: 11px;
            color: #555;
            margin: 0 0 5px 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 15px;
            font-weight: bold;
        }
        .money-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            margin-top: 3px;
        }
        .money-row .value { font-weight: bold; }
        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 15px;
            line-height: 1.5;
        }
        .btn-print {
            display: block;
            text-align: center;
            background: #047857;
            color: #fff;
            border: none;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 12px;
            width: 100%;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 5px 8px; width: 100%; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Struk</button>

    <div class="text-center">
        <div class="header-title">DESA WISATA GETAS</div>
        <div class="header-sub">Kec. Singorojo, Kab. Kendal</div>
        <div class="header-sub">Struk Pembayaran POS</div>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="label">No:</span>
        <span class="value">{{ $transaction->invoice_number }}</span>
    </div>
    <div class="info-row">
        <span class="label">Tgl:</span>
        <span class="value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="info-row">
        <span class="label">Kasir:</span>
        <span class="value">{{ $transaction->user->nama ?? ($transaction->user->name ?? 'Kasir') }}</span>
    </div>
    <div class="info-row">
        <span class="label">Metode:</span>
        <span class="value" style="text-transform: uppercase;">{{ $transaction->payment_method }}</span>
    </div>
    @if($transaction->customer_name)
        <div class="info-row">
            <span class="label">Pengunjung:</span>
            <span class="value">{{ $transaction->customer_name }}</span>
        </div>
    @endif

    <div class="divider"></div>

    @foreach($transaction->items as $item)
        <div class="item-row">
            <span class="item-name">{{ $item->product_name }}</span>
            <span class="item-total">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="item-detail">
            {{ $item->quantity }} x @ Rp {{ number_format($item->price, 0, ',', '.') }}
        </div>
        @if($item->item_type === 'paket_wisata' && $item->booking)
            <div class="item-detail">
                Kode Booking: <span class="bold">{{ $item->booking->booking_code }}</span>
                ({{ $item->booking->tanggal_kunjungan->format('d/m/Y') }} - {{ $item->booking->sesi }})
            </div>
        @endif
    @endforeach

    <div class="divider"></div>

    <div class="total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
    </div>

    @if($transaction->payment_method === 'cash')
        <div class="money-row">
            <span>Bayar (Tunai)</span>
            <span class="value">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="money-row">
            <span>Kembali</span>
            <span class="value">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>
    @endif

    <div class="divider"></div>

    <div class="footer">
        <div>Terima Kasih atas Kunjungan Anda!</div>
        <div>~ Desa Wisata Getas ~</div>
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
