<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$product = []; // элементы
$section = []; // разделы
$sub_section = []; // подразделы
$list = [];

// Выбираем из и.блока необходимый раздел
$items = GetIBlockSectionList($arParams['IBLOCK_ID'], $arParams['PARENT_SECTION'], Array("sort" => "asc"));
while ($arItem = $items->GetNext()) {
    // Сборка разделов и элементов раздела
    $arSelect = Array();
    $arFilter = Array("IBLOCK_ID" => $arParams['IBLOCK_ID'], 'SECTION_ID' => Array($arParams['PARENT_SECTION'], $arItem['ID']), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 50), $arSelect);
    $res->NavStart(8);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProp = $ob->GetProperties();
        $elem = ['NAME' => $arFields['NAME'],
            'ID' => $arFields['ID'],
            'SECT_NAME' => $arItem['NAME'],
            'SECT_CODE' => $arItem['CODE'],
            'WEIGHT' => $arProp['PRODUCT_WEIGHT']['VALUE'][0],
            'IMG' => CFile::ResizeImageGet($arProp["PRODUCT_IMG"]["VALUE"], array('width'=>500, 'height'=>500), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src'],
            'PRICE' => $arProp['PRODUCT_PRICE']['VALUE'][0],
            'INFO' => $arProp['PRODUCT_INFO']['VALUE']['TEXT'],
        ];
        $product[] = $elem;
        $section[$arItem['CODE']] = $arItem['NAME'];
        $list[$arItem['CODE']] = $arItem['NAME'];
    }
    // Если раздел имеет подразделы
    $rsParentSection = CIBlockSection::GetByID($arItem['ID']);
    if ($arParentSection = $rsParentSection->GetNext()) {
        $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'], '>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'], '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'], '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']); // выберет потомков без учета активности
        $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
        while ($arSect = $rsSect->GetNext()) {
            // Собираем подразделы и элементы
            $arSelect = Array();
            $arFilter = Array("IBLOCK_ID" => $arParams['IBLOCK_ID'], 'SECTION_ID' => Array($arParams['PARENT_SECTION'], $arSect['ID']), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 50), $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arProp = $ob->GetProperties();
                $elem = ['NAME' => $arFields['NAME'],
                    'ID' => $arFields['ID'],
                    'SECT_NAME' => $arSect['NAME'],
                    'SECT_CODE' => $arSect['CODE'],
                    'WEIGHT' => $arProp['PRODUCT_WEIGHT']['VALUE'][0],
                    'PRICE' => $arProp['PRODUCT_PRICE']['VALUE'][0],
                    'IMG' => CFile::ResizeImageGet($arProp["PRODUCT_IMG"]["VALUE"], array('width'=>500, 'height'=>500), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src'],
                    'INFO' => $arProp['PRODUCT_INFO']['VALUE']['TEXT'],
                ];
                $product[] = $elem;
                $section[$arItem['CODE']] = $arItem['NAME'];
                $list[$arSect['CODE']] = $arSect['NAME'];
                $sub_section[$arItem['CODE']][$arSect['CODE']] = $arSect['NAME'];
            }
        }
    }
}

$active = ['active' => 'active', 'true' => 'true'];
?>
<div class="col-md-3">
    <ul class="nav nav-tabs pmp-tabs" id="myTab" role="tablist">
        <? foreach ($section as $key => $val): ?>
            <?if (array_key_exists($key, $sub_section)): ?>
                <li class="nav-item ">
                    <p class="pmp-title"><?=$val?></p>
                    <div class="pmp-sub-drop">
                        <? foreach ($sub_section[$key] as $k => $v): ?>
                            <a class="nav-link pmp-sub-title n-l" data-toggle="tab" href="#<?= $k ?>" role="tab"
                               aria-selected="false"><?= $v ?></a>
                        <? endforeach; ?>
                    </div>
                </li>
            <?else:?>
                <li class="nav-item">
                    <a class="nav-link n-l <?=$active['active']?> pmp-title " data-toggle="tab" href="#<?= $key ?>" role="tab"
                       aria-selected="<?=$active['true']?>"><?= $val ?></a>
                </li>
                <? $active = ['active' => '', 'true' => 'false'];?>
            <? endif; ?>
        <? endforeach; ?>
    </ul>
</div>

<div class="col-md-9">
    <div class="row sm-fist-row">
        <div class="col-md-4 pmp-fr-title">
            <p class="ms-main-title ">
                НАЗВАНИЕ / ОПИСАНИЕ
            </p>
        </div>
        <div class="col-md-2 pmp-fr-ves">
            <div class="ms-main-title">
                ВЕС
            </div>
        </div>
        <div class="col-md-2 pmp-fr-price">
            <div class="ms-main-title">
                ЦЕНА
            </div>
        </div>
        <div class="col-md-2 pmp-fr-photo">
            <div class="ms-main-title text-center">
                ФОТО
            </div>
        </div>
        <div class="col-md-2 pmp-fr-card">
            <div class="ms-main-title text-center">
                В КОРЗИНУ
            </div>
        </div>
    </div>
    <div class="tab-content">
        <?$active = 'active'?>
        <? foreach ($list as $key => $val): ?>
            <div class="tab-pane tp <?=$active?>" id="<?= $key ?>" role="tabpanel">
                <?
                $active = '';
                for ($i = 0; $i < count($product); $i++){
                    if ($key == $product[$i]['SECT_CODE']) {
                        $sectElem[] = $product[$i];
                    }else continue;
                }
                for($i=0; $i<count($sectElem); $i++):
                    if ($i < 8)$page = 1;
                    else if($i >= 8 && $i < 16) $page = 2;
                    else if($i >= 16 && $i < 24) $page = 3;
                    else if($i >= 24 && $i < 32) $page = 4;
                    else if($i >= 32 && $i < 40) $page = 5;
                    ?>
                    <div class="row sec-menu-item s-it" id="<?=$this->GetEditAreaId($sectElem[$i]['ID'])?>" data-index-menu="1" data-page-menu="0" data-page-list="<?=$page?>">
                        <div class="col-md-4">
                            <div class="smcont-desc pmp-tc-title">
                                <h1 class=""><?= $sectElem[$i]['NAME'] ?></h1>
                                <p><?= $sectElem[$i]['INFO'] ?></p>
                            </div>
                        </div>
                        <div class="col-md-2 pmp-tc-ves">
                            <div class="smcont-desc">
                                <h1><?= $sectElem[$i]['WEIGHT'] ?></h1>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="smcont-desc pmp-tc-price">
                                <h1><?= $sectElem[$i]['PRICE'] ?> <span class="rouble">р</span></h1>
                            </div>
                        </div>
                        <div class="col-md-2 pmp-tc-photo">
                            <?if(!empty($sectElem[$i]['IMG'])):?>
                                <div class="smcont-desc text-center pmp-h-img">
                                    <div class="click-md-img">
                                        <img src="<?=$sectElem[$i]['IMG']?>" alt="">
                                    </div>
                                </div>
                            <?endif;?>
                        </div>
                        <div class="col-md-2 pmp-tc-card" data-product-id="<?=$sectElem[$i]['ID']?>">
                            <div class="smcont-desc text-center add-to-card">
                                <div class="delivery-bl-card">
                                    <div class="del-back"></div>
                                    <img src="<?= SITE_TEMPLATE_PATH ?>/img/del-img-6.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                <?endfor;?>
            </div>
            <?unset($sectElem);?>
        <? endforeach; ?>
    </div>
</div>