<?php

/*
   Just4Fun by iBacor.com ^^
*/

//error_reporting(0);

function cek_ktp($nik) {

    ################################################ MULAI cURL ###############################################
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


    // URL KPU yg akan di cURL
    curl_setopt($ch, CURLOPT_URL,'http://data.kpu.go.id/dps2015.php');
    // mengirm rquest kepada URL KPU menggunakan method POST dengan parameter seperti dibawah
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'wilayah_id=0&page=&nik_global='.$nik.'&g-recaptcha-response=capcay&cmd=Cari.');
    ################################################# END cURL ################################################

    // data array untuk output nanti
    $array = array();

    // Gagal ngecURL
    if(!$html = curl_exec($ch)){
        $array['status'] = "error";
        $array['pesan'] = "website sedang offline";
    }

    // Sukses ngecURL
    else{

        // manipulasi data html
        $dom = new DOMDocument;
        $dom->loadHTML($html);

        // mengambil data html yang ada didalam tag <span>
        $span = $dom->getElementsByTagName('span');

        // jika tag <span> tidak ada berarti artinya nik tidak tersedia maka status error
        if(empty($span->item(1))){
            $array['status'] = "error";
            $array['pesan'] = "data tidak ada";
        }else{
            // jika data nik ada maka status success
            $array['status'] = "success";
            $array['pesan'] = "data ada";

            // mengekstrak semua data html yang ada didalam tag <span> dan di ambil yang diperlukan saja berdasarkan key array.y
            foreach ($span as $key => $value) {
                if($key == 1){
                    $nik = $value->nodeValue;
                }else if($key == 3){
                    $nama = $value->nodeValue;
                }else if($key == 5){
                    $kelamin = $value->nodeValue;
                }else if($key == 7){
                    $kelurahan = $value->nodeValue;
                }else if($key == 9){
                    $kecamatan = $value->nodeValue;
                }else if($key == 11){
                    $kabupaten_kota = $value->nodeValue;
                }else if($key == 13){
                    $provinsi = $value->nodeValue;
                }
            }

            // memasukan data yang diperlukan ke array data untuk output
            $data = array(
                'nik' => strtolower($nik),
                'nama' => strtolower($nama),
                'kelamin' => strtolower($kelamin),
                'kelurahan' => strtolower($kelurahan),
                'kecamatan' => strtolower($kecamatan),
                'kabupaten_kota' => strtolower($kabupaten_kota),
                'provinsi' => strtolower($provinsi)
            );
            $array['data'] = $data;
        }

        return $array;

    }

}

// menentukan NIK dengan parameter nik => http://anu.kom/ani.php?nik=123456789
$nik = (empty($_GET['nik']) ? '' : $_GET['nik']);

// menjalankan proses curl dan mengolah data html.y nanti output.y dalam bentuk array
$data = cek_ktp($nik);

// merubah data array menjadi json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode($data, JSON_PRETTY_PRINT);

?>