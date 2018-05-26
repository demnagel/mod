<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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
$patterns = array('/1/','/2/','/3/','/4/','/5/','/6/','/7/','/8/','/9/','/10/');
$replacements = array('first','second','third','fourth','fifth','sixth','seventh','eighth','ninth','tenth');
$arrlinks =[];
$i = 1;
// Сборка информационного массива
$arSort = array("SORT" => "ASC");
$arFilter = array("IBLOCK_ID" => 7, "CODE" => $arParams["PARENT_SECTION_CODE"], "ACTIVE" => "Y");
$itemsList = CIBlockSection::GetList($arSort, $arFilter, false);
while ($item = $itemsList->GetNext()) {
    $parent_section_id = $item["ID"];
    $rsParentSection = CIBlockSection::GetByID($parent_section_id);
    if ($arParentSection = $rsParentSection->GetNext()) {
        $arFilter = array('IBLOCK_ID' => 7, 'ID' => $arSect['ID'], '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']);
        $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
        while ($arSect = $rsSect->GetNext()) {
            foreach ($arResult["ITEMS"] as $arItem) {
                if ($arSect["ID"] !== $arItem['IBLOCK_SECTION_ID']) continue;
                $arrlinks[$arSect["NAME"]]['title'][] = $arItem["NAME"];
                $img = CFile::ResizeImageGet($arItem["DISPLAY_PROPERTIES"]["IMAGE"]["FILE_VALUE"]["ID"], array('width'=>800, 'height'=>600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                $arrlinks[$arSect["NAME"]]['img'][] = $img["src"];
                $arId[$arSect['NAME']] = $arItem['ID'];
            }
        }
    }
}

//Сборка навигации?>
<div class="rs-tab-links">
    <ul class="nav nav-tabs rs-tab-link-slider" id="myTab" role="tablist">
        <?foreach ($arrlinks as $key => $val):?>

            <li class="nav-item rs-tab-link" id ='<?=$this->GetEditAreaId($arId[$key]);?>'>
                <a class="nav-link <?=($i != 1)? '' : 'active'?>" data-toggle="tab" href="#<?=preg_replace($patterns, $replacements, $i)?>-tab" role="tab"><?=$key?></a>
            </li>

        <? $i++; endforeach; $i = 1; ?>
    </ul>
</div>
<?//Сборка контентной области слайдера?>
<div class="tab-content sh-wrap">
    <?foreach ($arrlinks as $key => $val):?>
        <div class="tab-pane <?=($i != 1)? '' : 'active'?>" id="<?=preg_replace($patterns, $replacements, $i)?>-tab" role="tabpanel">
            <div class="slider slider-hard ">
                <?for ($j = 0; $j < count($val['img']); $j++):?>

                    <div class="slide-h"><img src="<?=$val['img'][$j]?>" alt="">
                        <p><?=$val['title'][$j]?></p>
                    </div>

                <?endfor;?>
            </div>
            <div class="pagingInfo">1 из <?=count($val['img'])?></div>
        </div>
    <? $i++; endforeach; ?>
</div>












