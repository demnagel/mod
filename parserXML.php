<?php
class ParseXML
{
    protected $xmlDOM;
    protected $xpath;
    protected $list;
    protected $table;
    protected $object = [];
    protected $tree = [];

    public function __construct($filename)
    {
        $reg = '/.+\.xml/i';
        if(!preg_match($reg, $filename)){
            throw new Exception("фаил ($filename) не xml");
        }
        if(!file_exists($filename)){
            throw new Exception("Фаил $filename не найден.");
        }
        $this->xmlDOM = new DOMDocument();
        $this->xmlDOM->preserveWhiteSpace = false;
        $this->xmlDOM->load($filename);
        $this->xpath = new DOMXPath($this->xmlDOM);
    }


    public function read($table, array $listColumn)
    {
        $queryTable = "//table[@name='$table']";
        $res = $this->xpath->query($queryTable);
        if($res['length'] == 0){
            throw new Exception("Таблица $table не найдена");
        }
        for($i = 0; $i < count($listColumn); $i++) {
            $query = "//table[@name='$table']/column[@name='$listColumn[$i]']";
            $res = $this->xpath->query($query);
            if($res['length'] == 0){
                throw new Exception("$listColumn[$i] в таблице $table не существеут");
            }
            $a = [];
            $j = 0;
            foreach ($res as $key => $value){
                $a[$j] = $value->nodeValue;
                $j++;
            }
            $this->object[$listColumn[$i]] = $a;
        }
        $this->table = $table;
        $this->list = $listColumn;
        return $this->object;
    }

    public function getArray()
    {
        $arr = [];
        $col = array_values($this->object);
        if($col[0] == 0){
            throw new Exception("в таблице $this->table нет значений");
        }
        for($i = 0; $i < count($col[0]); $i++) {
            $subArr = [];
            for ($j = 0; $j < count($col); $j++) {
                $subArr[$this->list[$j]] = $col[$j][$i];
            }
            $arr[] = $subArr;
        }
        return $arr;
    }


    public function getTree(array $field)
    {

        $glArr = $this->getArray(); // глобальный массив значений
        $tree = [];                 // дерево

        $category = $this->suggest([$field[0]]); //поиск уникальных категорий
        $numCat = $this->object[$field[0]];      //список категорий
        $subCategory = $this->object[$field[1]]; //список подкатегорий

        //поиск ключей вхождений категории
        for($i = 0; $i < count($category); $i++){
            $arrValues = [];
            for($j = 0; $j < count($numCat); $j++){
                if($category[$i] == $numCat[$j]){
                    $arrValues[] = $j;
                }
            }
            //поиск уникальных значений дл категории по списку вхождений
            $numSubcat =[];
            foreach($arrValues as $key => $val){
                $numSubcat[] = $subCategory[$val];
            }
            $uniqSC = [];
            for($j = 0; $j < count($numSubcat); $j++){
                if(!in_array($numSubcat[$j], $uniqSC)){
                    $uniqSC[] = $numSubcat[$j];
                }
            }
            //значения подкатегорий
            for($j =0; $j< count($uniqSC); $j++){
                $values = [];
                foreach ($glArr as $key => $val){
                    if(in_array($uniqSC[$j], $val)){
                        unset($val[$field[0]]); // удаление категории из результата
                        unset($val[$field[1]]); // удаление подкатегории из результата
                        $values[] = $val;       //оставшиеся значения
                    }
                }
                $tree[$category[$i]][$uniqSC[$j]] = $values;    //сборка дерева
            }
        }
        return $tree;
    }


    public function suggest( array $nameField )
    {
        $suggest =[];
        for($i = 0; $i < count($nameField); $i++){
            $arr = $this->object[$nameField[$i]];
            for($j = 0; $j < count($arr); $j++){
                if(!in_array($arr[$j], $suggest)){
                    $suggest[] = $arr[$j];
                }
            }
        }
        return $suggest;
    }

    public function getMinPrice($nameField)
    {
        $arr = $this->object[$nameField];
        sort($arr);
        return $arr[0];
    }
    public function getMaxPrice($nameField)
    {
        $arr = $this->object[$nameField];
        sort($arr);
        return $arr[count($arr) - 1];
    }
    public function getFullPrice($nameField)
    {
        $summ = 0;
        for($i = 0; $i < count($this->object[$nameField]); $i++){
            $summ += $this->object[$nameField][$i];
        }
        return $summ;
    }
    public function getGoods($findGoods)
    {
        $arrValues = [];
        $obj = array_values($this->object);
        for($i = 0; $i < count($obj); $i++){
            //быстрый поиск по массивам
            if(in_array($findGoods, $obj[$i])){
                //точный поиск в массиве
                for($j = 0; $j < count($obj[$i]); $j++){
                    if($findGoods == $obj[$i][$j]){
                        $arrValues[] = $j;
                    }
                }
            }
        }
        if(!empty($arrValues)) {
            //сбор массива по результатам поиска
            $arr = [];
            for ($j = 0; $j < count($arrValues); $j++) {
                $subArr = [];
                for ($i = 0; $i < count($obj); $i++) {
                    $subArr[] = $obj[$i][$arrValues[$j]];
                }
                $arr[] = $subArr;
            }
            return $arr;
        }
    }
}

?>


