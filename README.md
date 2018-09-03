# Sakura
A simple and easy to use script to create Telegram bots.
## Installation
1. First thing first, you need to create a Telegram Bot. In order to do it, start the telegram bot [@BotFather](t.me/BotFather) and type `/newbot`.
![BotFather Message](https://i.imgur.com/mW6vCn2.png)
You just have to save the HTTP API Token, you'll need it later.
2. Next, move on your VPS and clone this repository using
 `git clone https://github.com/justlel/Sakura.git && cd Sakura/`
3. Once you are done, you can install all the required dependecies using
 `composer install composer.json`
## Create the bot.
Once you are done with the installation, you have to create the bot file.
You can create it as you rather, but here there is a simple example
```
<?php

require_once __DIR__.'/vendor/autoload.php'; // require autoload composer

error_reporting(0);


$bot = new \Sakura\TGBot("664855233:AAGUo8FGkI8c6fsjqxTD_lgBYwoXuxUjDgU", [143336289], ['save_instance' => true]);
// The first parameter is the token given by botfather, the second is the array list and the third the settings to be modified.

$handler = new \Sakura\Addons\Updater(function($update) use($lel) { // create an update handler function
    isset($update['message']['text']) ? $message = $update['message']['text'] : $message = '';
    isset($update['message']['from']['id']) ? $user_id = $update['message']['from']['id'] : $user_id = '';
    
    if($message=='/start') {
        $lel->sendMessage(['chat_id' => $user_id, 'text' => 'Hello!']); // send a message if /start is given.
    }
});
