<?php

use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\UpdateTypes;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

require 'vendor/autoload.php';
require './ExcelReader.php';

set_time_limit(0);

class MyConversation extends Conversation {
    private bool $flag = false; 

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

        $bot->sendMessage('Введите <b>имеющееся</b> значение силы', ['parse_mode'=>"html"]);
        
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
        
        $bot->sendMessage('Введите <b>имеющееся</b> значение стамины', ['parse_mode'=>"html"]);
        
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
        
        $bot->sendMessage('Введите <b>имеющееся</b> значение скорости', ['parse_mode'=>"html"]);
        
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
        
        $bot->sendMessage('Введите <b>желаемое</b> значение силы', ['parse_mode'=>"html"]);
        
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
        
        $bot->sendMessage('Введите <b>желаемое</b> значение стамины', ['parse_mode'=>"html"]);
        
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
        
        $bot->sendMessage('Введите <b>желаемое</b> значение скорости', ['parse_mode'=>"html"]);
        
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
        
        $result =(new ExcelReader())->canBuy(ExcelReader::$mainArray, $this->params, $this->wanted);

        if (!empty($result))
        {
            $bot->sendMessage('<b>'. $this->name . '</b>' . " может надеть на себя:", ['parse_mode'=>"html"]);

            $message = '';
    
            for ($j = 0; $j < count($result); $j++) 
            {   
                for ($i = 0; $i < count($result[$j]) - 1; $i++)
                {
                    $message = $message . $result[$j][$i][0] .'-' . $result[$j][$i][1] . '-' . $result[$j][$i][2]. ' ' . $result[$j][$i]['type'] . ' ';
                }
    
                $message = $message . '~ Price <b>' . $result[$j]['price'] . '</b>';
    
                $bot->sendMessage($message, ['parse_mode'=>"html"]);
    
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

$bot = new Nutgram("6034191716:AAEMdbm9eJGn4-0C9r0UzLcZOw7JWqEC4Vw");

$filePath = "./sales.xlsx";

ExcelReader::getPrices($filePath);
ExcelReader::getData($filePath);

$bot->onCommand('start', function(Nutgram $bot){
    $bot->sendMessage('Welcome!', [     
        'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make("h"),
            KeyboardButton::make('Give me animal!'),
        )
    ]);
});

$bot->onText('h', MyConversation::class);

$bot->fallbackOn(UpdateTypes::MESSAGE ,function (Nutgram $bot) {
    $bot->sendMessage('Я вас не понимаю! /start для начала!');
});

$bot->run();
?>