<?php

use SergiX44\Nutgram\Conversations\Conversation;
use \SergiX44\Nutgram\Nutgram;

require 'vendor/autoload.php';

require('ExcelReader.php');

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