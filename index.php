<?php

use SergiX44\Nutgram\Conversations\Conversation;
use \SergiX44\Nutgram\Nutgram;

require 'vendor/autoload.php';
require './ExcelReader.php';

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
        
        $result = ExcelReader::canBuy(ExcelReader::$mainArray, $this->params, $this->wanted);
        if (!empty($result))
        {
            $bot->sendMessage($this->name . " может надеть на себя:");

            $message = '';
    
            for ($j = 0; $j < count($result); $j++) 
            {   
                for ($i = 0; $i < count($result[$j]) - 1; $i++)
                {
                    $message = $message . $result[$j][$i][0] .'-' . $result[$j][$i][1] . '-' . $result[$j][$i][2]. ' ' . $result[$j][$i]['type'] . ' ';
                }
    
                $message = $message . '~ Price ' . $result[$j]['price'];
    
                $bot->sendMessage($message);
    
                $message = '';
    
                usleep(10000);
            }
            
            $bot->sendMessage("Примечание: Цена указана приблизительная, и приведена для сравнения в качестве ориентира на общую стоимость, она не всегда будет совпадать с действительной ценой на маркете! Рекомендуем зайти на маркет и промониторить цены в ручную.");    
        }
        else
        {
            $bot->sendMessage("К сожалению, добиться такого результата невозможно, попробуйте ещё раз!");
        }

        $this->end();        
    }
}

$bot = new Nutgram("TOKEN");

$filePath = "./sales.xlsx";

ExcelReader::getPrices($filePath);
ExcelReader::getData($filePath);

$bot->onCommand('start', MyConversation::class);

$bot->fallback(function (Nutgram $bot) {
    $bot->sendMessage('Я вас не понимаю! /start для начала!');
});

$bot->run();
?>