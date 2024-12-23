<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo e($invoice->no_invoice); ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm 10mm 15mm 10mm;

            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .title {
            margin-bottom: 10px;
        }

        .title h2 {
            margin: 0;
            color: #444;
        }

        .title h5 {
            margin: 5px 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 20px;
        }

        .summary p {
            margin: 5px 0;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
            }

            .container {
                width: 100%;
                max-width: none;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            button {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media screen {
            .container {
                max-width: 900px;
            }
        }

        /* kop */
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo-container {
            flex: 0 0 23%;
            padding-right: 15px;
            /* padding-left: 30px; */
        }

        .logo {
            width: 100%;
            max-width: 120px;
            height: auto;
        }

        .company-info {
            flex: 0 0 75%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 90px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 10pt;
            line-height: 1.3;
        }

        .document-title {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 15px;
            text-align: center;
            clear: both;
        }
    </style>
</head>

<body>
    <?php
    $hargaIDR = ceil($hargaIDR / 1000) * 1000;
    ?>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <?php
                $path = public_path('img/logo4.png');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                if (file_exists($path)) {
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                } else {
                    $base64 = '';
                }
                ?>
                <img src="<?php echo $base64; ?>" alt="logo" class="logo" />
            </div>
            <div class="company-info">
                <div class="company-name">PT. GES LOGISTIC</div>
                <div class="company-address">
                    42Q2+6PH, Unnamed Road,
                    Batu Selicin, Kec. Lubuk Baja, Kota Batam, Kepulauan Riau<br>
                    Telp: 0856-BATU-KECE (0856-2288-5323) | Email: Pt@batukerenrambut.com
                </div>
            </div>
        </div>

        <div class="title">
            <h5>Tanggal: <?php echo e($invoice->tanggal_bayar); ?></h5>
            <h5>Pembeli: <?php echo e($invoice->pembeli); ?> (<?php echo e($invoice->marking); ?>) </h5>
            <p>Alamat: <?php echo e($invoice->alamat); ?></p>
            <h2>Invoice: <?php echo e($invoice->no_invoice); ?></h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>No. Resi</th>
                    <th>No. Do</th>
                    <th>Berat/Dimensi</th>
                    <th>Hitungan</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $no = 1;
                ?>
                <?php $__currentLoopData = $resiData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($no++); ?></td>
                        <td><?php echo e($resi->no_resi); ?></td>
                        <td><?php echo e($resi->no_do); ?></td>
                        <?php if($resi->berat): ?>
                            <td>Berat</td>
                        <?php else: ?>
                            <td>Dimensi</td>
                        <?php endif; ?>

                        <?php if($resi->berat): ?>
                            <td>
                                <?php echo e($resi->berat ?? '0'); ?>

                                <?php if($resi->priceperkg): ?>
                                    / <?php echo e(number_format($resi->priceperkg, 2)); ?> perkg
                                <?php endif; ?>
                            </td>
                        <?php else: ?>
                            <td>
                                <?php
                                    $panjang = $resi->panjang ?? 0;
                                    $lebar = $resi->lebar ?? 0;
                                    $tinggi = $resi->tinggi ?? 0;
                                    $volume = ($panjang / 100) * ($lebar / 100) * ($tinggi / 100); // hasil dalam m3
                                ?>
                                <?php echo e(number_format($volume, 3)); ?> m³
                            </td>
                        <?php endif; ?>
                        <td><?php echo e(number_format($resi->harga, 2) ?? '0'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="summary">
            <p class="text-right">Total Harga: <strong><?php echo e(number_format($hargaIDR, 2)); ?></strong></p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\ilono\OneDrive\Desktop\Project SAC\pt-ges-project\resources\views\exportPDF\invoice.blade.php ENDPATH**/ ?>