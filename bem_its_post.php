<?php

function scrap($url) {
    if($url==='') {
        echo 'URL tidak lengkap!';
    }
    else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        curl_setopt($ch, CURLOPT_URL, 'http://bem.its.ac.id/id/'.$url.'/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // html
        $data = curl_exec($ch);
        curl_close($ch);

        // array untuk output
        $output = array();

        // gagal cURL
        if (!$data) {
            $output['status'] = "error";
            $output['pesan'] = "website sedang offline";
        }
        // sukses cURL
        else {
            // include plugin
            require 'plugin/simple_html_dom.php';

            // merubah html menjadi string
            $html = str_get_html($data);

            // menentukan bahan yang mau diolah yaitu div id=siteTable array ke 0
            $bahan = $html->find('div[class=sections_group]', 0);

            // Deskripsi konten
            $desc = $bahan->find('div[class=section]', 0);

            // Ambil judul
            $head = $desc->find('div[class=title_wrapper]', 0);
            $title = $head->find('h1[class=entry-title]', 0)->innertext;

            // Ambil gambar
            $img = '';
            // Ada post yang tidak memiliki gambar
            $image = $desc->find('div[class=image_wrapper]', 0);
            if (!empty($image)) {
                if (!empty($image->find('a', 0))) {
                    $img = $image->find('a', 0)->href;
                }
            }

            // Ambil author dan date
            $auth = $desc->find('div[class=author-date]', 0);
            $author = $auth->find('a', 0)->innertext;
            $date = $auth->find('span[class=date]', 0)->find('time', 0)->innertext;

            foreach($bahan->find('div[class=post-wrapper-content]', 0)->find('div[class=the_content_wrapper]', 0)->find('p') as $e)
                $content[] = $e->innertext;

            // hapus kolom komentar
            unset($content[count($content)-1]);

            $output['status'] = 'success';
            $output['data'][] = array(
                'title' => $title,
                'img' => $img,
                'date' => $date,
                'author' => $author,
                'content' => $content
            );
            return $output;
        }
    }
}

// menentukan parameter
$url = (empty($_GET['url']) ? '' : $_GET['url']);

// menjalankan proses curl
$result = scrap($url);

// convert jadi json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode($result, JSON_PRETTY_PRINT);
?>