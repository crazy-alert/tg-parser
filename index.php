<?php
include_once('vendor/autoload.php');

$ch = curl_init('https://core.telegram.org/bots/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$html = curl_exec($ch);
curl_close($ch);


$document = new DiDom\Document($html);
////*[@id="dev_page_content_wrap"]/div[1]/div/ul/li[6]/ul
///
$dev_side_nav = $document->find('ul.nav');


$r= 1;
//$saw = \nokogiri::fromHtml($html);

///html/body/div/div[2]/div/div/div[1]/div/ul/li[6]/ul/li[1]
//$side_nav = $document->find('dev_side_nav > ul');