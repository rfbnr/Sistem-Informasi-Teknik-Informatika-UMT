<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verifikasi</title>
</head>

<body style="width: 50%; margin: auto; border: 1px solid black; padding: 20px; box-sizing: border-box;">

    <style type="text/css">

    .content{
        text-align: justify;
        font-size: 18px;
        font-family: Arial, Helvetica, sans-serif
    }

    .header {
        margin-bottom: 20px;
        text-align: center;

    }

    .header img {
        width: 100px; /* Sesuaikan ukuran logo */
        margin-right: 20px;
        vertical-align: middle;
    }

    .header h2 {
        margin: 0;
        display: inline-block;
        vertical-align: middle;
        font-size: 18px;
    }

    .biodata {
        width: 100%;
        border-collapse: collapse;
        margin-top: 50px;
        font-size: 18px;
        font-family: Arial, Helvetica, sans-serif

    }

    .biodata th,
    .biodata td {
        /* border: 1px solid black; */
        padding: 8px;
        text-align: left;
    }

    .biodata th {
        width: 150px;
    }

    .biodata th {
        text-align: left;
        padding-right: 5px; /* Mengatur jarak antara th dan : */
    }

    .biodata th + th {
        width: 10px; /* Lebar kolom untuk titik dua (:) */
        text-align: center; /* Memusatkan titik dua */
    }

    .biodata td {
        padding-left: 5px; /* Mengatur jarak antara : dan isi data */
    }

    .bimbingan {
        width: 100%;
        border-collapse: collapse;
        margin-top: 50px;
    }



    .container {
        text-align: right;
        margin-top: 100px;
        font-size: 18px;
        font-family: Arial, Helvetica, sans-serif
    }

    .ttd {
        text-align: right;
    }

    .nama {
        margin-top: 50px;
        text-align: center;
    }

    </style>

    <!-- awal table logo -->
    <div class="header">
        <img src="{{ asset('assets/img/logoft.png') }}" alt="Logo">
        <h2>LEMBAR PENGESAHAN PRODI TEKNIK INFORMATIKA<br>FAKULTAS TEKNIK<br>UNIVERSITAS MUHAMMADIYAH TANGERANG</h2>
    </div>
    <tr>
        <th colspan="4"><hr></th>
    </tr>
    <!-- akhir table logo -->

    <!-- table biodata -->
    <table class="biodata">
             <tr>
            <th>Nomor</th>
            <th>:</th>
            <td>{{ $user->nomor }}/III.3.AU/KEP-FT/VIII/2024</td>
        </tr>
        <tr>
            <th>Nama</th>
            <th>:</th>
            <td>{{ $user->User->name }}</td>
        </tr>
        <tr>
            <th>Nim</th>
            <th>:</th>
            <td>{{ $user->User->NIM }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <th>:</th>
            <td>{{ $user->document_name }}</td>
        </tr>
    </table>
    <!-- akhir table biodata -->

    <!-- table bimbingan -->
    <div class="bimbingan">
        <p class="content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repudiandae et consequuntur eos, ad sed perspiciatis quam eaque ex dolore minima error placeat dolores qui officia delectus. Dolorum maxime eum officiis deserunt earum eaque sint tempore quisquam eius inventore, optio cupiditate corrupti esse aliquid voluptatibus nemo soluta est vero. Asperiores, hic, itaque alias aut velit possimus dignissimos id repudiandae odio sunt harum illum quasi quam deleniti quos! Sit corporis maiores id rem, quidem esse ut repudiandae voluptatem magni similique praesentium fuga dolore expedita dolorem distinctio velit quo porro dolores nesciunt. Quis consectetur alias hic soluta laudantium eius architecto numquam veniam id.</p>
        <p class="content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ut deserunt cum aut animi eligendi pariatur doloribus perspiciatis distinctio saepe, vero quisquam accusantium in iure optio quae sunt reiciendis porro libero, tempore molestias sapiente? Id voluptates eos provident minus ipsa tenetur nesciunt, aliquam consequuntur aliquid. Provident aliquid maxime libero deserunt cum.</p>
    </div>
    <!-- akhir bimbingan -->

    <!-- awal ttd -->
    <div class="container">
        <p>Tangerang, <?php echo date('d F Y'); ?></p>
        <p>Prodi Teknik Informatika</p>
    <img src="{{ asset('assets/img/ttd.png') }}" alt="ttd" width="200px">
        <p>Yani Sugiyani, MM., M.Kom</p>
        <p>NIDN : 0419038004</p>
    </div>

</body>

</html>
