<?php
require_once 'phpQuery.php';

function curl($url, $referer = 'http://google.com')
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $referer);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64)
     AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($curl);
    curl_close($curl);
    return $content;
}

if(isset($_POST['query'])){
    $i=0;
    $content = '';
    $url = 'http://nominatim.emotian.at/search.php?';
    $query = 'q='.str_replace(' ','+',$_POST['query']).'&polygon=1&viewbox=';
    $html = curl($url.$query);
    $patLon = '/\"lon\": \"([0-9]{2}\.[0-9]{3,})\"/';
    $patLat = '/\"lat\": \"([0-9]{2}\.[0-9]{3,})\"/';
    preg_match_all( $patLon, $html, $arrLon, PREG_SET_ORDER);
    preg_match_all( $patLat, $html, $arrLat, PREG_SET_ORDER);

    $pq = phpQuery::newDocument($html);
    $elem = $pq->find('.result');
    foreach ($elem as $e){
        $namepq = pq($e)->find('.name');
        $name = $namepq->text();
        $apq = pq($e)->find('a');
        $href = $apq->attr('href');
        $content .= "<div class='element'><span class='desc' lat ='{$arrLat[$i][1]}' lan='{$arrLon[$i][1]}' data-link='$href'>$name</span></div>";
        $i++;
    }
    $content .= "<div class='detail'>Подробнее</div>";
    echo json_encode($content);
}
if(isset($_POST['lan']) && isset($_POST['lat'])){
    $lan = $_POST['lan'];
    $lat = $_POST['lat'];
    ob_start();
    include ('v_map.php');
    $map = ob_get_clean();
    echo json_encode($map);
}
if(isset($_POST['detail']) && $_POST['detail_text']){
    if(!strstr($_POST['detail_text'],'Москва')){
        ob_start();
        include ('v_noreport.php');
        $res = ob_get_clean();
        echo json_encode($res);
    }
    else {
        $html = curl('http://nominatim.emotian.at/' . $_POST['detail']);
        $pq = phpQuery::newDocument($html);
        $elem = $pq->find('#locationdetails a');
        $href = $elem->text();
        $osm = explode(' ', $href)[1];
        $final_href = "http://new.emotian.at/api/v3/reports/place/?id=" . $osm . "&start=2018-01-10T00:00:00&stop=2018-01-14T23:59:59&token=1&report_format=html&report_type=analytic";
        ob_start();
        include ('v_report.php');
        $res = ob_get_clean();
        echo json_encode($res);
    }
}
