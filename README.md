![Sakura](https://i.imgur.com/yq83tg3.png)
# Sakura
A simple and easy to use script to create Telegram bots.
## Index
* [**Features**](#features)
* **Installation**
    * [**Installation**](#installation)
    * [**Bot Creation**](#create-the-bot)
* [**Working**](#how-it-really-works)
    * [**Updates**](#update-handling)
    * [**Actions**](#execute-actions)
* **Addons**
    * [**How to create an addon**](#create-custom-addons)
    * [**Official Plugins**](#current-list-of-official-plugins)
## Features
This is what Sakura offers to Telegram Bot Developers:
* Full compatibility to PHP 7.*
* An efficient and fast Update-Handler, thanks to [pcntl_fork()](https://secure.php.net/manual/en/function.pcntl-fork.php#115714).
* Completely customizable with official and unofficial addons.
* Storage of the current Settings at each start.
* Possibility to modify the script behavior simply by editing the configs.
* A nice logger to notify you of everything that happens.
* Custom exception handler (obviously modifiable).
* Compact. Just create the bot file and it's done!

## Installation
1. First thing first, you need to create a Telegram Bot. In order to do it, start the telegram bot [@BotFather](t.me/BotFather) and type `/newbot`.

![BotFather Message](https://i.imgur.com/mW6vCn2.png)

You just have to save the HTTP API Token, you'll need it later.
2. Next, move on your VPS and clone this repository using

 `git clone https://github.com/justlel/Sakura.git && cd Sakura/`


3. Once you are done, you can install all the required dependecies using


 `composer update`

## Create the bot
Once you are done with the installation, you have to create the bot file.
You can create it as you rather, but here there is a simple example.
```
<?php

require_once __DIR__.'/vendor/autoload.php'; // require autoload composer

error_reporting(0);


$bot = new \Sakura\TGBot("664855233:AAGUo8FGkI8c6fsjqxTD_lgBYwoXuxUjDgU", [143336289], ['as_array' => true]);
// The first parameter is the token given by botfather, the second is the array list and the third the settings to be modified.

$plugins = new \Sakura\PluginsManager(['Updater.php', 'EasierKeyboards.php']); // Load custom plugins :>

$handler = new \Sakura\Addons\Updater(function(array $update) use($lel) { // create an update handler function
    isset($update['message']['text']) ? $message = $update['message']['text'] : $message = '';
    isset($update['message']['from']['id']) ? $user_id = $update['message']['from']['id'] : $user_id = '';
    
    if($message=='/start') {
        $bot->sendMessage(['chat_id' => $user_id, 'text' => 'Hello!']); // send a message if /start is given.
    }
});
$handler->loop();
```
## How it really works
### Update Handling
By creating a new instance of the class `TGbot`, all the other main classes (_Logger.php,Settings.php and HttpRequest.php_)
are automatically initialized. The `TGbot` constructor asks for 3 parameters, 1 required and 2 optional:
* _The Telegram bot token._ **(REQUIRED)**
* _An array with the bot admins._ **(OPTIONAL)**
* _An array of custom settings._ **(OPTIONAL)**

After that, we can optionally initialize the class `PluginsManager.php` to add some addons to the project. The class contructor accepts one parameter:
* _An array of plugin to enable_ **(REQUIRED)**

Which can either be passed as:
* _An URL to a php file, containing ONLY the code of the addon, named as the class inside it. [RAW GitHub files](https://raw.githubusercontent.com/justlel/Sakura/master/src/Addons/EasierKeyboards.php) are a perfect example._
* _A string, ending with .php, to download an [official plugin](#current-list-of-official-plugins) from the repository._

Then you can start to write the real bot. 

If you have already downloaded the plugin `Updater.php`, then you can use **multithreading** to handle updates.

Create an instance of the class `Updater.php` and pass **1** required parameter, a callable which has to **accept one parameter of type _array_.**

Now the bot is ready to process the updates. The script will start getting the last updates in an endless loop and very time a new one is found, the process will fork and the update will be processed.

You will probably want to have the bot execute actions depending on which update is received, so, in order to let the bot execute properly,
you can check every field of the function parameter `array`, which is simply an array representing a [Telegram Update.](https://core.telegram.org/bots/api#update)

For example, if you want to check if an user has sent a message to the bot, you could check the `$update['message']['text']` field, and so on.
### Execute actions
The execution of Telegram Actions is made possible by the php "[__call()](http://secure.php.net/manual/en/language.oop5.overloading.php#object.call)" method.
This method is located in the `TGBot` class and its working is preatty easy.

Every time that you invoke an inaccessible method of the class `TGBot`, the __call method is called.
The inaccessible function name and the inaccessible funcion parameters are passed as parameters of the method.

The method will now create a new object of the `HttpRequest` class passing its parameter to it.
The return field of the method is the response of the request.

_(I would like to thanks [peppelg](https://github.com/peppelg/EzTG) for giving me the inspiration to add this functionality to my own script <3)_

## Create custom addons
We <3 contributions of every kind from our users. Given the extendibility of the bot, you can create your own plugin and ask to make it official! Here is how you can do.
1. Your plugin MUST contain only one class, which MUST have the same name as the file name.
2. Your plugin MUST be part of the namespace `Sakura\Addons`.
3. Your plugin MUST have a constant called `INFO`, which has to be an associative array containing these 3 fields: 
    * `author`: the name of the plugin's author.
    * `description`: a short description of the plugin.
    * `version`: the version of your plugin.

As long as your plugin respects these requirements, you will be able to load it by putting his code in a PHP file and loading it by passing a link to it.
(Note that the link must not be to your plugin, but to a page which clearly print its code. [RAW GitHub files](https://raw.githubusercontent.com/justlel/Sakura/master/src/Addons/EasierKeyboards.php) are perfect examples).

If you would like to make your plugin official, feel free to [Contact me on Telegram](https://t.me/JustLel) or to push it to this repository.
## Current list of official plugins
* EasierKeyboard.php:
    * `Author:`[JustLel](https://t.me/JustLel)
    * `Description: `A class to create Telegram inline keyboards more easly.
    * `Version: `1.0
* Updater.php
    * `Author: `[JustLel](https://t.me/JustLel)
    * `Description: `Addons to handle Telegram updates.
    * `Version: `1.0


*Thanks for downloading <3*
    
    - JustLel a.k.a. Fluction
