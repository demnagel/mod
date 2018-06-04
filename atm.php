<?php
include ('atmCell_set.php');
include ('atmCell_get.php');

// Банкомат
class ATM
{
    use atmCell_set;
    use atmCell_get;
    // количество купюр в ячейке
    private $r50 = 50;
    private $r100 = 20;
    private $r500 = 10;
    private $r1000 = 20;
    private $r2000 = 10;
    private $r5000 = 0;
    // процент купюр при автозагрузке
    private $r50p;
    private $r100p = 5;
    private $r500p = 20;
    private $r1000p = 30;
    private $r2000p = 15;
    private $r5000p = 30;

    private $balance;


    public function get_balance()
    {
        return $this->balance;
    }
    // Загрузка с автораспределением по ячейкам
    public function autoLoad($summ)
    {
       if(!self::chekSumm($summ)){
           return false;
       }
       elseif (!$this->r100p || !$this->r500p || !$this->r1000p || !$this->r2000p || !$this->r5000p ){
           throw new Exception('Не установлен процент номинала купюр');
           return false;
       }
       else {
           $arr = [];
           $startSumm = $summ;
           $arrCell = ['5000' => $this->r5000p,
               '2000' => $this->r2000p,
               '1000' => $this->r1000p,
               '500' => $this->r500p,
               '100' => $this->r100p,
               '50' => $this->r50p
           ];

           foreach ($arrCell as $cell => $percent) {

               $sub_sum = $percent / 100 * $startSumm;
               if ($cell == '50') {
                   if(!method_exists(ATM, 'set50')){
                       throw new Exception('Метод set50 не существует');
                       return false;
                   }
                   $count = $summ / $cell;
                   $arr['set50'] = $count;
               } elseif ($sub_sum >= $cell) {
                   $call = 'set' . $cell;
                   if(!method_exists(ATM, $call)){
                       throw new Exception("Метод $call не существует");
                       return false;
                   }
                   $count = floor($sub_sum / $cell);
                   $arr[$call] = $count;
                   $cs = $count * $cell;
                   $summ -= $cs;
               } else continue;
           }
           foreach ($arr as $method => $cellSumm){
               $this->$method($cellSumm);
           }
           return $arr;
       }
    }
    // Выдача купюр минимальным колличеством
    public function getSumm($summ)
    {
        $summStart = $summ;
        if(!self::chekSumm($summ)){
            return false;
        }
        else {
            $arrRes = [];
            $arrCell = ['r5000' => 5000,
                        'r2000' => 2000,
                        'r1000' => 1000,
                        'r500'  => 500,
                        'r100'  => 100,
                        'r50'   => 50,
                        ];
            foreach ($arrCell as $cell => $num) {
                if ($summ >= $num && $this->$cell != 0) {
                    $s = floor($summ / $num);
                    if($this->$cell > $s) {
                        $arrRes[$cell] = $s; // добавить в массив
                        $s *= $num;
                        $summ -= $s;
                    }
                    else{
                        $arrRes[$cell] = $this->$cell;
                        $summ -= $this->$cell * $num;
                    }
                } else continue;
            }
            if($summ != 0){
                throw new Exception('в АТМ не хватает средств. Доступная сумма - '.($summStart - $summ));
                return false;
            }
            return $arrRes;
        }
    }

    public static function chekSumm($summ)
    {
        $num = substr($summ, -2, -1);
        if($summ < 50){
            throw new Exception('Минимальная сумма 50');
            return false;
        }
        elseif($num != 0 && $num != 5){
            throw new Exception('Округлите сумму до 50');
            return false;
        }
        else return true;
    }
}


