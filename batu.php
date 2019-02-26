<?php 
require __DIR__ . "/vendor/autoload.php";

$batuTarget = isset($argv[1]) ? $argv[1] : '';
if (empty($batuTarget)) {
    echo "Empty target!", PHP_EOL;
    exit;
}

$saveDir = __DIR__ . "/target";
if (!is_dir($saveDir)) {
    mkdir($saveDir);
}
foreach (["images", "css", "js"] as $childType) {
    if (!is_dir($saveDir. "/{$childType}")) {
        mkdir($saveDir. "/{$childType}");
    }
}    
$saveFile = isset($argv[2]) ? $argv[2] : '';
if (empty($saveFile)) {
    echo "Empty save file!", PHP_EOL;
    exit;
}
$saveFile = $saveDir . "/" . $saveFile;

$targetInfo = parse_url($batuTarget);
$targetRoot = $targetInfo["scheme"] . "://" . $targetInfo["host"];
$target = QL\QueryList::get($batuTarget);
$targetHtml = $target->getHtml();

//find images
$target->find("img")->map(function ($img) use ($targetRoot, $saveDir, &$targetHtml) {
    $imgUrl = preg_match("/^http/", $img->src) === 0 ? $targetRoot . $img->src : $img->src;
    //save images
    $imgUrlInfo = parse_url($imgUrl);
    $imgSaveFile = $saveDir . "/images" . $imgUrlInfo['path'];
    if (!is_dir(dirname($imgSaveFile))) {
        mkdir(dirname($imgSaveFile), 0744, true);
    }
    if (!is_file($imgSaveFile)) {
        file_put_contents($imgSaveFile, file_get_contents($imgUrl));
    }
    $targetHtml = str_replace($img->src, "images".$imgUrlInfo['path'], $targetHtml);
});
//save html
file_put_contents($saveFile, $targetHtml);