<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .invoice-details {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        .invoice-details th,
        .invoice-details td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .invoice-details th {
            background-color: #f2f2f2;
        }

        .total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
        }

        .invoice-header {
            margin-top: 0vh;
            width: 100%;
        }
    </style>

</head>

<body>
    <div style="position: absolute; top: 0; right: 0; margin: 0px;">
        <img src="./images/Asset1.png" alt="Charles Hall" style="width: 150px; height: 150px; margin: 0;" />
    </div>
    {{-- <div style="margin-bottom: 100vh; visibility: hidden;">
        <h1>Yayasan Unggul Amanah</h1>
    </div> --}}


    <div style="margin-bottom:60vh">
        <img src="./images/asset2.png" alt="Charles Hall" style="width: 1000px; height: 300px; margin: 0;" />
    </div>

    <table class="invoice-header">
        <tr>
            <th style="text-align: left; font-weight:normal; width: 100px; ">
                Dengan ikhlas dan mengharap ridho
                Allah SWT
                bermaksud menyerahkan sebagian harta
                yang diamanahkan kepada
                saya berupa :
            </th>
            <td style="width: 250px; text-align:right; font-weight:normal; font-size:1.3rem;">
                Sudah terima dari : <br /> <span style="font-weight:bold;"> {{ $transaksi->nama_donatur }}</span>
            </td>

        </tr>
    </table>
    <p style="text-align: right; font-weight:bold; font-size:1.2rem"> Tanggal :
        <span style="font-weight:normal; margin-left:20px; font-size:1.2rem">
            {{ $transaksi->tanggal_donasi ? date('d M Y', strtotime($transaksi->tanggal_donasi)) : 'Tanggal tidak tersedia' }}</span>
    </p>

    <table class="invoice-details">
        <tr>
            <th style="background-color: #3E8257; color:#fff;">No</th>
            <th style="background-color: #3E8257; color:#fff;">Program Donasi</th>
            <th style="background-color: #3E8257; color:#fff;">Nominal</th>
        </tr>
        @php
            $totalDonasi = 0; // Inisialisasi total donasi
        @endphp
        @foreach ($transaksi_detail as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->nama_program }}</td>
                <td>Rp. {{ number_format($item->nominal_donasi, 0, ',', '.') }}</td>
                @php
                    $totalDonasi += $item->nominal_donasi; // Menambahkan nominal donasi ke totalDonasi
                @endphp
            </tr>
        @endforeach

        <tr>
            <td colspan="2" style="border:none"></td>
            <td>Rp. {{ number_format($totalDonasi, 0, ',', '.') }}</td>
            {{-- <td style="background-color: #3E8257; color:#fff;">Rp. {{ number_format($totalDonasi, 0, ',', '.') }}</td> --}}
        </tr>

    </table>



    {{-- <div class="total">Total Donasi: Rp. {{ number_format($transaksi->total_donasi, 0, ',', '.') }}</div> --}}
    <div style="margin-top:0px">
        <img src="./images/asset3.png" alt="Charles Hall" style="width: 1000px; height: 300px; margin: 0;" />
    </div>
    <div style="margin-top:20px">
        <img src="./images/asset5.png" alt="Charles Hall" style="width: 1000px; height: 300px; margin: 0;" />
    </div>

    <div style="position: absolute; bottom: 0; left: 0; margin: 0px;">
        <img src="./images/asset4.png" alt="Charles Hall" style="width: 150px; height: 150px; margin: 0;" />
    </div>
</body>

</html>
