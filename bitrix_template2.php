<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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
//$this->setFrameMode(true);

$bxajaxid = CAjax::GetComponentID($component->__name, $component->__template->__name);
$arR = []; // разделы иблока;
$arCount =[]; // количество элементов в разделах

?>

<div class="row">
    <div class="col-md-9 portfolio-link">
        <div class="row">
            <div class="col-md-4">
                <a class="ajax_link portfolio-title-1" href="/portfolio/?&amp;" onclick="BX.ajax.insertToNode('/portfolio/?page=creating&amp;bxajaxid=<?=$bxajaxid?>', 'comp_<?=$bxajaxid?>', true); return false;"><?=lang("Создание книг",'Creating books')?></a>
            </div>
            <div class="col-md-4">
                <a class="ajax_link portfolio-title-2" href="/portfolio/?&amp;" onclick="BX.ajax.insertToNode('/portfolio/?page=prepress&amp;bxajaxid=<?=$bxajaxid?>', 'comp_<?=$bxajaxid?>', true); return false;"><?=lang("Допечатная подготовка",'Prepress')?></a>
            </div>
            <div class="col-md-4">
                <a class="ajax_link portfolio-title-3" href="/portfolio/?&amp;" onclick="BX.ajax.insertToNode('/portfolio/?page=print&amp;bxajaxid=<?=$bxajaxid?>', 'comp_<?=$bxajaxid?>', true); return false;"><?=lang("Печать тиража",'The printing of')?></a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?

    $np = new Page;
    extract($np->final_page());
    $pc = $portfolio['start'];

    $SectList = CIBlockSection::GetList($arSort, array("IBLOCK_ID"=> $arParams['IBLOCK_ID'],"ACTIVE"=>"Y") ,false, array('ID'));
    while ($SectListGet = $SectList->GetNext())
    {
        $arR[]=$SectListGet;
        $arFilter = Array("IBLOCK_ID" => $arParams['IBLOCK_ID'], 'SECTION_ID' => $SectListGet['ID'], "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
        $allNews = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50),  Array("ID"));
        $arCount[$SectListGet['ID']] = $allNews->result->num_rows;
    }

    if($arCount[1] >= 4) $prep = $pc + floor($arCount[1] / 4);
    else  $prep = $pc + 1;
    if($arCount[2] >= 4) $print = $prep + floor($arCount[2] / 4);
    else  $print = $prep + 1;

    function getSection($arR, $i)
    {
        $elements = [];
        $arFilter = Array("IBLOCK_ID" => 3, 'SECTION_ID' => $arR[$i]['ID'], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), Array());
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arProp = $ob->GetProperties();
            $html = "<div class='pbox col-md-4' style='background: url(";
            $html .=  CFile::ResizeImageGet($arFields['ID'], array('width' => 408, 'height' => 238), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src'];
            $html .= ") center left no-repeat'>";
            $html .= "<div class='phover-effect'>";
            $html .= "<p>" . lang($arFields['NAME'], $arProp['NAME_EN']['VALUE']) ."<p>";
            $html .= "</div></div>";
            $elements[] = ['ELEMENT' => $html];
        }
        return $elements;
    }

    function getPagSection($arr,$pc)
    {
        $pattern = '/PAGEN_/';
        foreach ($_GET as $key => $val){
            if(preg_match($pattern, $key)){
                $pagKey = $key;
            }
        }
        $rs = new CDBResult;
        $rs->InitFromArray($arr);
        $rs->NavStart(4);
        while ($element = $rs->GetNext()){
            echo $element['~ELEMENT'];
        }

        if(isset($_GET[$pagKey])){
            echo "<h1 class='content-m-page'>".($pc + ($_GET[$pagKey] - 1))."</h1>";
        }
        else echo "<h1 class='content-m-page'>".$pc."</h1>";

        if($rs->IsNavPrint())
        {
            echo $rs->GetPageNavStringEx($comp, '', 'sekvoya_pag');
        }
    }

    if(!isset($_GET['page'])) {
        getPagSection(getSection($arR, 0),$pc);
    }
    if($_GET['page']=='creating'){
        getPagSection(getSection($arR, 0),$pc);
    }
    if($_GET['page']=='prepress') {
        getPagSection(getSection($arR, 1),$prep +1);
    }
    if($_GET['page']=='print'){
        getPagSection(getSection($arR, 2),$print + 1);
    }
    ?>
</div>


