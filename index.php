<?php

use SergiX44\Nutgram\Conversations\Conversation;
use \SergiX44\Nutgram\Nutgram;

require 'vendor/autoload.php';

set_time_limit(0);

class MyConversation extends Conversation {
    private bool $flag; 

    public string $name;
    public $params = [];
    public $wanted = [];

    public function start(Nutgram $bot)
    {
        $bot->sendMessage('Введите имя вашего CAThlete');
        $this->next('powerStep');
    }

    public function powerStep(Nutgram $bot)
    {
        $this->name = $bot->message()->text;
        $this->flag = false;
        
        $bot->sendMessage('Введите имеющееся значение силы');
        
        $this->next('staminaStep');
    }

    public function staminaStep(Nutgram $bot)
    {
        if(!ctype_digit($bot->message()->text) && !$this->flag)
        {
            $this->flag = true;
            
            $this->powerStep($bot);
           
            return;
        }
        
        if (!array_key_exists(0, $this->params)) 
        array_push($this->params, (int)$bot->message()->text);
        
        $this->flag = false;
        
        $bot->sendMessage('Введите имеющееся значение стамины');
        
        $this->next('speedStep');
    }

    public function speedStep(Nutgram $bot)
    {
        if(!ctype_digit($bot->message()->text) && !$this->flag)
        {
            $this->flag = true;
            
            $this->staminaStep($bot);
           
            return;
        }
        
        if (!array_key_exists(1, $this->params)) 
            array_push($this->params, (int)$bot->message()->text);
        
        $this->flag = false;
        
        $bot->sendMessage('Введите имеющееся значение скорости');
        
        $this->next('wPowerStep');
    }

    public function wPowerStep(Nutgram $bot)
    {
        if(!ctype_digit($bot->message()->text) && !$this->flag)
        {
            $this->flag = true;
            
            $this->speedStep($bot);
            
            return;
        }
        
        if (!array_key_exists(2, $this->params)) 
            array_push($this->params, (int)$bot->message()->text);
        
        $this->flag = false;
        
        $bot->sendMessage('Введите желаемое значение силы');
        
        $this->next('wStaminaStep');
    }

    public function wStaminaStep(Nutgram $bot)
    {
        if(!ctype_digit($bot->message()->text) && !$this->flag)
        {
            $this->flag = true;
            
            $this->wPowerStep($bot);
            
            return;
        }
        
        if (!array_key_exists(0, $this->wanted)) 
            array_push($this->wanted, (int)$bot->message()->text);
        
        $this->flag = false;
        
        $bot->sendMessage('Введите желаемое значение стамины');
        
        $this->next('wSpeedStep');
    }

    public function wSpeedStep(Nutgram $bot)
    {
        if(!ctype_digit($bot->message()->text) && !$this->flag)
        {
            $this->flag = true;
        
            $this->wStaminaStep($bot);
        
            return;
        }
        
        if (!array_key_exists(1, $this->wanted)) 
            array_push($this->wanted, (int)$bot->message()->text);
        
        $this->flag = false;
        
        $bot->sendMessage('Введите желаемое значение скорости');
        
        $this->next('lastStep');
    }

    public function lastStep(Nutgram $bot)
    {
        if(!ctype_digit($bot->message()->text) && !$this->flag)
        {
            $this->flag = true;
        
            $this->wSpeedStep($bot);
        
            return;
        }
        
        if (!array_key_exists(2, $this->wanted)) 
            array_push($this->wanted, (int)$bot->message()->text);
        
        $this->flag = false;
        
        $bot->sendMessage('Закрытие');
        
        $this->end();
    }
}

class ExcelReader {
    private static function getSheet(string $filePath)
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly("Sheet1");
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);

        return $worksheet = $spreadsheet->getActiveSheet();
    }

    public static $prices = [];
    public static $mainArray = [];

    public static function getPrices(string $filePath)
    {
        $worksheet = ExcelReader::getSheet($filePath);

        $row = 2;
        
        while ($value = $worksheet->getCellByColumnAndRow(2, $row)->getValue()) {
            array_push(ExcelReader::$prices, $value);
            
            $row++;
        }
    }

    public static function getData(string $filePath)
    {
        $worksheet = ExcelReader::getSheet($filePath);

        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($col = 4; $col <= $highestColumnIndex; ++$col) {
            if ($type = $worksheet->getCellByColumnAndRow($col, 1)->getValue()) {
                ExcelReader::$mainArray[$type] = array();
                
                for ($subCol = $col; $subCol <= $col + 2; ++$subCol) {
                    $row = 3;
                    
                    array_push(ExcelReader::$mainArray[$type], array());
                    
                    while ($worksheet->getCellByColumnAndRow($subCol, $row)->getValue() !== NULL) {
                        $value = $worksheet->getCellByColumnAndRow($subCol, $row)->getValue();
                        
                        array_push(ExcelReader::$mainArray[$type][$subCol - $col], $value);
                        
                        $row++;
                    }
                }
            }
        }
    }
    
    public static function canBuy($candidates, $params, $wanted) 
    {
        $sum = [];

        for ($i = 0; $i < count($params); $i++)
        {
            $sum[$i] = $wanted[$i] - $params[$i];
        }

        $result = [];
        $subList = [];
        
        ExcelReader::doNext(0, $result, 0, $candidates, $sum[0], $sum[1], $sum[2], $subList);
        ExcelReader::sortByPrice($result);

        return $result;
    }

    private static function doNext(
                        int $i,
                        &$result,
                        int $count,
                        $candidates,
                        $target1,
                        $target2,
                        $target3,
                        $subArr
                    ) 
    {
        if ($target1 == 0 && $target2 == 0 && $target3 == 0)
        {
        $subList = [];
        
        for ($k = 0; $k < $count; $k++) 
            array_push($subList, $subArr[$k]);
        
        array_push($result, $subList);
        } 
        else if ($target1 > 0 && $target2 > 0 && $target3 > 0 && $count < 6) 
        {
            foreach ($candidates as $key => $value)
            {
                for ($j = $i, $l = count($value[0]); $j < $l; $j++) 
                {
                    $subArr[] = [$key=>array($value[0][$j], $value[1][$j], $value[2][$j])];
                    
                    ExcelReader::doNext($j, $result, $count + 1, $candidates, 
                    $target1 - $value[0][$j], $target2 - $value[1][$j], $target3 - $value[2][$j], $subArr);
                    
                    unset($subArr[count($subArr) - 1]);
                    $subArr = array_values($subArr);
                }
            }
        }
    }

    private static function sort_func($a, $b)
    {
        if ($a == 'Legendary' && $b == 'Epic') return 1;
        if ($a == 'Legendary' && $b == 'Rare') return 1;
        if ($a == 'Legendary' && $b == 'Uncommon') return 1;
        if ($a == 'Legendary' && $b == 'Common') return 1;
        if ($a == 'Legendary' && $b == 'Legendary') return 0;
        if ($a == 'Epic' && $b == 'Rare') return 1;
        if ($a == 'Epic' && $b == 'Uncommon') return 1;
        if ($a == 'Epic' && $b == 'Common') return 1;
        if ($a == 'Epic' && $b == 'Epic') return 0;
        if ($a == 'Rare' && $b == 'Uncommon') return 1;
        if ($a == 'Rare' && $b == 'Common') return 1;
        if ($a == 'Rare' && $b == 'Rare') return 0;
        if ($a == 'Uncommon' && $b == 'Common') return 1;
        if ($a == 'Uncommon' && $b == 'Uncommon') return 0;
        if ($a == 'Common' && $b == 'Common') return 0;
        return -1;
    }

    private static function sortByPrice($unsorted)
    {
        $sorted = [];
        for ($i = 0; $i < count($unsorted); $i++)
        {
            for ($j = 0; $j < count($unsorted[$i]); $j++)
            {
                for ($k = 0; $k < count($unsorted[$i][$k]); $k++)
                {
                    print_r($unsorted[$i]);
                    uksort($unsorted[$i], array('ExcelReader', 'sort_func'));
                }
            }
        }
    }
}

$bot = new Nutgram("6034191716:AAEMdbm9eJGn4-0C9r0UzLcZOw7JWqEC4Vw");

$filePath = "./sales.xlsx";

ExcelReader::getPrices($filePath);
ExcelReader::getData($filePath);

$target = array(20,30,40);
ExcelReader::canBuy(ExcelReader::$mainArray, $target, array(23, 50, 69));

$bot->onCommand('start', function(Nutgram $bot) {
    $bot->sendMessage('Ciao!');
});

$bot->onCommand('test', MyConversation::class);

$bot->run();
?>