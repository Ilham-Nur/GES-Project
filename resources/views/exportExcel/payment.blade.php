<table>
    <thead>
        <tr>
            <td style="text-align:center;font-size:14px; font-weight: bold; padding: 14px" colspan="6">Export Payment Customer</td>
        </tr>
        <tr>
            <td style="text-align:left;font-size:11px;padding: 14px;">Tanggal Mulai:</td>
            <td style="text-align:left;font-size:11px;padding: 14px;font-weight: bold;">
                {{ $startDate ? date('d M Y', strtotime($startDate)) : '-' }}
            </td>
        </tr>
        <tr>
            <td style="text-align:left;font-size:11px;padding: 14px;">Tanggal Akhir:</td>
            <td style="text-align:left;font-size:11px;padding: 14px;font-weight: bold;">
                {{ $endDate ? date('d M Y', strtotime($endDate)) : '-' }}
            </td>
        </tr>
        <tr>
            <td style="text-align:left;font-size:11px;padding: 14px;">Metode Pembayaran:</td>
            <td style="text-align:left;font-size:11px;padding: 14px;font-weight: bold;">
                {{ $status ? $status : 'Semua metode' }}
            </td>
        </tr>
        <tr></tr>
        <tr>
            <th style="text-align:center;font-size:11px;border:1px solid black; font-weight: bold; padding: 10px; white-space: normal;"
                bgcolor="#b9bab8">Kode Pembayaran</th>
            <th style="text-align:center;font-size:11px;border:1px solid black; font-weight: bold; padding: 10px; white-space: normal;"
                bgcolor="#b9bab8">No Invoice</th>
            <th style="text-align:center;font-size:11px;border:1px solid black; font-weight: bold; padding: 10px; white-space: normal;"
                bgcolor="#b9bab8">Tanggal Bayar</th>
            <th style="text-align:center;font-size:11px;border:1px solid black; font-weight: bold; padding: 10px; white-space: normal;"
                bgcolor="#b9bab8">Jumlah</th>
            <th style="text-align:center;font-size:11px;border:1px solid black; font-weight: bold; padding: 10px; white-space: normal;"
                bgcolor="#b9bab8">Metode Pembayaran</th>
            <th style="text-align:center;font-size:11px;border:1px solid black; font-weight: bold; padding: 10px; white-space: normal;"
                bgcolor="#b9bab8">Status Bayar</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($payments as $payment)
            <tr>
                <td  style="text-align:left;font-size:11px;border:1px solid black; padding: 10px">{{ $payment->kode_pembayaran }}</td>
                <td  style="text-align:left;font-size:11px;border:1px solid black; padding: 10px">{{ $payment->no_invoice }}</td>
                <td  style="text-align:left;font-size:11px;border:1px solid black; padding: 10px">{{ $payment->tanggal_bayar }}</td>
                <td  style="text-align:left;font-size:11px;border:1px solid black; padding: 10px">{{ $payment->amount }}</td>
                <td  style="text-align:left;font-size:11px;border:1px solid black; padding: 10px">{{ $payment->payment_method }}</td>
                <td  style="text-align:left;font-size:11px;border:1px solid black; padding: 10px">{{ $payment->status_bayar }}</td>
            </tr>
        @endforeach
    </tbody>
</table>