<?php declare(strict_types=1);

namespace Sakura\Addons;


use Sakura\Exception\TGException;
use Sakura\HttpRequest;
use Sakura\TGBot;

class Updater
{
    const INFO = [
        'version' => '1.0',
        'description' => 'Addons to handle Telegram updates.',
        'author' => 't.me/JustLel',
    ];
    private $max_threads;
    private static $telegram;
    private $user_function_handler;

    public function __construct(callable $handler, int $threads=10, ?TGBot $tg = NULL)
    {
        if(is_null($tg)) {
            try {
                new HttpRequest('getMe');
            } catch(TGException $e) {
                throw new TGException('No valid TGBot instance was provided.');
            }
        } else {
            self::$telegram = $tg;
        }
        $this->max_threads = $threads;
        $this->validateHandler($handler);
        $this->user_function_handler = $handler;
    }


    private function validateHandler(callable $validate): bool {
        $function_info = new \ReflectionFunction($validate);
        $parameters_count = count($function_info->getParameters());
        if($parameters_count !== 1) {
            throw new TGException('The update handler must accept one parameter.');
        }
        try {
            $parameter_type = $function_info->getParameters()[0]->getType()->getName();
        } catch(\ReflectionException $e) {
            throw new TGException('The parameter type must be defined.');
        }
        if($parameter_type != 'object' && $parameter_type != 'array') {
            throw new TGException('The function parameter must be either an array or an object.');
        }
        return TRUE;
    }


    public function getUpdates(int $offset = -1, ?array $allowed = [], ?int $timeout = NULL, ?int $limit = NULL) {
        $request = new HttpRequest('getUpdates', ['offset' => $offset, 'allowed_updates' => $allowed, 'timeout' => $timeout, 'limit' => $limit], self::$telegram);
        if(!$request->getResponse()) {
            throw new TGException('An error occurred while trying to get update: '.$request->getResponse(){'description'});
        }

        return $request->getResponse(){'result'};
    }


    public function loop() {
        if(!function_exists('pcntl_fork')) {
            throw new TGException('pcntl is not installed, please install it before starting multithreading.');
        }

        $offset = -1;
        $current_running_threads = 0;
        while(true) {
            $ups = $this->getUpdates($offset);
            foreach($ups as $update) {
                if(!empty($update)) var_dump($update);
                $offset = $update{'update_id'} + 1;
                $pid = pcntl_fork();
                if($pid===-1) {
                    throw new TGException('An error occurred while forking.');
                } elseif($pid) {
                    $current_running_threads++;
                    if($current_running_threads>=$this->max_threads) {
                        pcntl_wait($status);
                        $current_running_threads--;
                    }
                } else {
                    call_user_func($this->user_function_handler, $update);
                    die();
                }
            }
        }
    }
}