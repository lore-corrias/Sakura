<?php declare(strict_types=1);

namespace Sakura;


use Sakura\Exception\TGException;
use Sakura\Interfaces\InterfaceAPIRequest;


/**
 * Class HttpRequest, part of the "Sakura" package.
 *
 * This class is designed to send request to the **official
 * Telegram api**, but can also be used to send an HttpRequest
 * of any type, simply by changing the **destination URL** and the **request method**.
 *
 * @see InterfaceAPIRequest _This class is also an implementation of the interface InterfaceAPIRequest._
 *
 * @package Sakura
 * @implements InterfaceAPIRequest
 */
class HttpRequest implements InterfaceAPIRequest
{
    /**
     * Property were the call's response will be stored.
     * It can either be an array or an object, depending
     * on the setting "as_array".
     *
     * @var object|array
     */
    private $response;
    /**
     * One of the Telegram methods used to have the bot perform actions.
     * If the HttpRequest is not executed to https://api.telegram.org/,
     * then the method can simply be one of the method that the site
     * you specified accepts.
     *
     * @var string
     */
    private $method;
    /**
     * Array of parameters passed to the Telegram APIs
     * with the method. Pass NULL if the method doesn't need
     * any parameter.
     *
     * @var array|null
     */
    private $parameters;
    /**
     * URL to call during the request. Default is "TGBot::API_URL".
     *
     * @var string
     */
    private $url;
    /**
     * An instance of the class TGBot. Easy.
     *
     * @var TGBot|null
     */
    public static $telegram;

    /**
     * HttpRequest constructor.
     *
     * This method can be used to initiate an HttpRequest.
     * Pass a method, an array of parameters (optional), an
     * instance of the class TGBot (optional) and a URL (also optional).
     * The execution is automatic, simply grab the result with getResponse()
     *
     * @param string $method Telegram API's method to call.
     * @param array|null $params Parameters for the API method. Optional.
     * @param TGBot|null $tg Instance of TGBot class. Optional, utilizable instead of defineInstance.
     * @param string $site URL site. Optional.
     * @throws TGException
     */
    public function __construct(string $method, ?array $params = [], ?TGBot $tg = NULL, string $site = TGBot::API_URL)
    {
        $this->method = $method;
        $this->parameters = $params;
        $this->url = $site;

        if (!is_null($tg) && !is_null(self::$telegram) && self::$telegram->settings->getSettings()['save_instance']) {
            $saved = self::$telegram; // saving instance if "save_instance" is true and a new instance is provided.
        }
        if (is_null($tg) && is_null(self::$telegram)) {
            throw new TGException("No valid TGBot instance was found. You can set one either by using the method defineInstance or by passing it as function parameter.");
        } elseif (!is_null($tg)) {
            self::$telegram = $tg;
        }

        // automatic execution of the request
        $this->execute();

        // removing current instance from the class if
        // it has been passed via parameter of the function
        // and if "save_instance" is not enabled.
        if (!is_null($tg) && isset($saved)) {
            self::$telegram = $saved;
        } elseif (!is_null($tg) && !isset($saved)) {
            self::$telegram = NULL;
        }
    }

    /**
     * Public method used to define a default instance
     * used by the class.
     *
     * @param TGBot $tg
     */
    public function defineInstance(TGBot $tg): void
    {
        self::$telegram = $tg;
    }

    /**
     * Method to execute the request previously defined
     * via the class' constructor.
     *
     * @throws TGException
     */
    private function execute(): void
    {
        $ch = curl_init();
        if ($this->url !== TGBot::API_URL) {
            $url = $this->url . $this->method;
        } else {
            $url = $this->url . self::$telegram->token . '/' . $this->method;
        }
        curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => http_build_query($this->parameters),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => self::$telegram->settings->getSettings()['request_timeout'],
                CURLOPT_USERAGENT => self::$telegram->settings->getSettings()['request_useragent'],
                CURLOPT_FOLLOWLOCATION => 0,
            ]
        );
        $raw = curl_exec($ch);
        if ($err = curl_errno($ch)) {
            // request failed, updating the response.
            $this->response = ['ok' => false, 'error_code' => $err, 'description' => curl_error($ch)];
            Logger::log(sprintf("The HttpRequest to the url %s failed with error code %d (%s)", $url, $err, curl_error($ch)), Logger::WARN);
            return;
        }
        if (self::$telegram->settings->getSettings()['as_array']) {
            $this->response = json_decode($raw, true);
        } else {
            $this->response = json_decode($raw);
        }
        curl_close($ch);
    }

    /**
     * Public method. Use this to obtain the result of a previously
     * executed request.
     *
     * @return array|object
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Deprecated function, there's no need
     * to obtain the current instance used by
     * this class after the "UpdateHandler" class
     * update.
     *
     * @deprecated
     */
    //public static function getCurrentInstance() {
    //	return self::$telegram;
    //}
}