<?php declare(strict_types=1);

namespace Sakura;


use Sakura\Exception\ExceptionHandler;
use Sakura\Exception\TGException;
use Sakura\Interfaces\TGInterface;

/**
 * Class TGBot, part of the "Sakura" namespace.
 *
 * This class is the core of the entire script. Here all the
 * variables required for the proper functioning of the bot will be set.
 *
 * @see TGInterface _This class is also an implementation of the interface TGInterface._
 *
 * @package Sakura
 * @implements TGInterface
 */
class TGBot implements TGInterface
{
    /**
     * A constant representing the default Telegram API url.
     * Don't change it, you don't have to :/
     *
     * @constant string
     */
    const API_URL = 'https://api.telegram.org/bot';
    /**
     * The token of the bot, provided by the Telegram bot (BotFather)[t.me/BotFather].
     *
     * This token, in combination with the API_URL and the request method,
     * is used to command your bot and make him execute actions.
     * Never share it! Anyone who gains access to this token could use your
     * bot as they please!
     *
     * @var string
     */
    private $token;
    /**
     * Simple username of the bot provided by the "getMe" method.
     *
     * This property doesn't have any real utility, you can just
     * use it if you want to get your bot's username from the script.
     *
     * @var string
     */
    private $username;
    /**
     * Unique Telegram identifier of the bot.
     *
     * Like the "username" property, this field doesn't have
     * any real utility or meaning for the script itself.
     *
     * @var string
     */
    private $ID;
    /**
     * An array containing a list of Telegram IDs.
     *
     * If you want, you can access this variable to define,
     * for example, a command which can be executed only by
     * some people. Can be empty, of course.
     *
     * @var 0array
     */
    private $admin_list;

    /**
     * Instance of the "Sakura\Exception\ExceptionHandler" class.
     *
     * It may be used to access the user-defined handler, if set.
     *
     * @var ExceptionHandler
     */
    public $handler;
    /**
     * Instance of the "Sakura\Settings" class.
     *
     * Simply use it if you want to get/edit/reset/export your
     * bot's configurations.
     *
     * @var Settings
     */
    public $settings;

    /**
     * TGBot constructor.
     *
     * @param string $token A string evaluated and then set as the value of the "token" property.
     * @param array $admin_list An array containing the list of the bot's admin. Optional.
     * @param array|null $settings An associative array containing
     * a list of config_name => config_value to be set instead of
     * the default values, stored in Settings::DEF_SETTINGS. Optional.
     * @param callable|null $handler A callable function which will be set
     * as exception_handler instead of the default one,
     * stored in "Sakura\Exception\ExceptionHandler", "handler" method.
     * @throws TGException
     * @throws \ReflectionException
     */
    public function __construct(string $token, array $admin_list = [], array $settings = Settings::DEF_SETTINGS, ?callable $handler = NULL)
    {
        $this->settings = new Settings($settings);
        $this->handler = new ExceptionHandler($this->settings->getSettings()['log_dir'], $handler);
        new Logger($this->settings->getSettings()['logger'], $this->settings->getSettings()['safe_mode']);
        $this->token = $token;
        $this->admin_list = $admin_list;

        $this->setBotInfo();
    }

    /**
     * Private method, used by the constructor to validate
     * the bot token and to set the "username" and "ID" properties.
     *
     * Throws TGException if the token is invalid.
     *
     * @throws TGException
     */
    private function setBotInfo(): void
    {
        $request = new HttpRequest('getMe', [], $this);
        if (!(object) $request->getResponse()->ok) {
            throw new TGException('The token provided does not exist.');
        } else {
            $this->username = (object) $request->getResponse()->result->username;
            $this->ID = (object) $request->getResponse()->result->id;
            $request->defineInstance($this);
        }
    }

    /**
     * PHP magic method __get().
     *
     * You can use this method if you want to access
     * either the variable token, username, ID or admin_list.
     *
     * @param $variable
     * @return mixed
     * @throws TGException
     */
    public function __get($variable)
    {
        $accessible = ['token', 'username', 'ID', 'admin_list'];
        if (!in_array($variable, $accessible)) {
            Logger::log(sprintf("You can't obtain the variable %s via __get(), the only variables you can obtain are: %s", $variable, implode(', ', $accessible)), Logger::WARN);
        }

        return $this->{$variable};
    }

    /**
     * PHP magic method call.
     *
     * By using this method you can call every action of
     * Telegram APIs. Just create an instance of TGBot and
     * call an action like if it was a method of the class.
     * Example of sendMessage:
     * $TGBot->sendMessage(['chat_id' => 12345678, 'text' => 'Hey!'], $TGBot, https://api.telegram.org/bot);
     *
     * @param $name string Name of the APIs method.
     * @param $arguments array An array of parameters for the method. Optional.
     * @return array|object
     * @throws TGException
     */
    public function __call($name, $arguments) // thanks to https://github.com/peppelg/EzTG <3
    {
        if (empty($arguments)) {
            $req = new HttpRequest($name);
        } else {
            $req = new HttpRequest($name, ...$arguments);
        }

        return $req->getResponse();
    }
}
