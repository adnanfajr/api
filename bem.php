<?php

function scrap($page) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_REFERER, 'http://bem.its.ac.id/id/blog-id/');

    if($page==='' ||$page==='1') {
        curl_setopt($ch, CURLOPT_URL, 'http://bem.its.ac.id/id/blog-id/');
        $halaman = 1;
    }
    elseif($page > 1) {
        curl_setopt($ch, CURLOPT_URL, 'http://bem.its.ac.id/id/blog-id/page/'.$page.'/');
        $halaman = $page;
    }
    else {
        curl_setopt($ch, CURLOPT_URL, 'http://bem.its.ac.id/id/blog-id/');
    }

    // html
    $data = curl_exec($ch);

    // array untuk output
    $output = array();

    // Gagal ngecURL
    if (!$data) {
        $output['status'] = "error";
        $output['pesan'] = "website sedang offline";

        curl_close($ch);
    }
    // Sukses ngecURL
    else {
        curl_close($ch);
        // include plugin
        require 'plugin/simple_html_dom.php';

        // merubah html menjadi string
        $html = str_get_html($data);

        // menentukan bahan yang mau diolah yaitu div id=siteTable array ke 0
        $bahan = $html->find('div[class=blog_wrapper]', 0);

        // mengambil kotak yang ada di dalam siteTable yaitu div class=thing output array
        $kotak = $bahan->find('div[class=post-item]');

        // ekstrak kotak
        foreach ($kotak as $key => $val) {
            // Deskripsi konten
            $desc = $val->find('div[class=post-desc-wrapper]', 0);

            // Ambil judul dan link
            $head = $desc->find('h2[class=entry-title]', 0);
            $title = $head->find('a', 0)->innertext;
            $link = $head->find('a', 0)->href;

            // Ambil gambar
            $img = '';
            // Ada post yang tidak memiliki gambar
            if (!empty($val->find('div[class=image_wrapper]', 0))) {
                if (!empty($val->find('a', 0)->find('img', 0))) {
                    $img = $val->find('a', 0)->find('img', 0)->src;
                }
            }

            // Ambil author dan date
            $auth = $val->find('div[class=author-date]', 0);
            $author = $auth->find('a', 0)->innertext;
            $date = $auth->find('span[class=post-date]', 0)->innertext;

            $output['status'] = 'success';
            $output['page'] = $halaman;
            $output['data'][] = array(
                'title' => $title,
                'link' => $link,
                'img' => $img,
                'date' => $date,
                'author' => $author
            );
        }
        return $output;
    }
}

// menentukan parameter
$page = (empty($_GET['page']) ? '' : $_GET['page']);

// menjalankan proses curl dan mengolah data html.y nanti output.y dalam bentuk array
$result = scrap($page);

// convert jadi json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode($result, JSON_PRETTY_PRINT);
?>