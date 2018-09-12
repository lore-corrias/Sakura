<?php

namespace sakura;


use Sakura\Exception\TGException;

/**
 * Class PluginsManager, part of the "Sakura" package.
 *
 * This class can be used to verify and load plugins from official/unofficial sources.
 *
 * @see PluginsManagerInterface _This class is also an implementation of the PluginsManagerInterface._
 *
 * @implements PluginsManagerInterface
 * @package sakura
 */
class PluginsManager
{
    /**
     * Constant containing the raw base directory of the official Sakura's Addons (inside the GitHub repository).
     *
     * @const string
     */
    const REPOSITORY_LINK = 'https://raw.githubusercontent.com/justlel/Sakura/master/src/Addons/';
    /**
     * An array of plugins to be loaded.
     *
     * @var array
     */
    private $plugins_to_load;


    /**
     * PluginsManager constructor.
     *
     * The constructor will check if the plugin is official or unofficial,
     * will validate its content and then will finally download it.
     * @param array $plugin_list
     * @throws TGException
     */
    public function __construct(array $plugin_list)
    {
        $this->plugins_to_load = $plugin_list;
        foreach (array_diff(scandir(__DIR__ . '/Addons'), ['..', '.']) as $unload) unlink(__DIR__ . '/Addons/' . $unload);
        $this->checkPluginsValidity();
    }

    /**
     * Function to check if the plugin is official or not.
     *
     * @param string $plugin
     * @return bool
     * @throws TGException
     */
    public function isOfficialPlugin(string $plugin): bool
    {
        if (!filter_var($plugin, FILTER_VALIDATE_URL)) {
            if (get_headers(self::REPOSITORY_LINK . $plugin)[0] === 'HTTP/1.1 404 Not Found') {
                throw new TGException('Plugin not valid.');
            }
            return TRUE;
        } else {
            if (!pathinfo($plugin, PATHINFO_EXTENSION) === '.php') {
                throw new TGException('Plugin not valid.');
            }
            return FALSE;
        }
    }

    /**
     * Function to download a plugin in the "Addons" folder.
     *
     * Simply, if the plugin is official, the content to download is taken from the
     * official repository link, else, it's downloaded from the given link.
     * @param string $plugin
     * @throws TGException
     */
    public function downloadPlugin(string $plugin): void
    {
        if (!$this->isOfficialPlugin($plugin)) {
            Logger::log('PAY ATTENTION!', Logger::WARN);
            Logger::log('Loading plugin from an untrusted source is highly unrecommended! Be careful!', Logger::WARN);
            file_put_contents(__DIR__ . '/Addons/' . pathinfo($plugin, PATHINFO_BASENAME), @file_get_contents($plugin));
        } else {
            Logger::log('Downloading plugin ' . $plugin, Logger::NOTICE);
            file_put_contents(__DIR__ . '/Addons/' . $plugin, @file_get_contents(self::REPOSITORY_LINK . $plugin));
        }
    }

    /**
     * Function to validate the content of the plugin class.
     *
     *
     * @throws TGException
     */
    public function checkPluginsValidity(): void
    {
        foreach ($this->plugins_to_load as $plugin) {
            $this->downloadPlugin($plugin);
            $plugin_class_name = str_replace('.php', '', filter_var($plugin, FILTER_VALIDATE_URL) ? pathinfo($plugin, PATHINFO_BASENAME) : $plugin);
            require_once __DIR__ . '/Addons/' . $plugin_class_name . '.php';
            try {
                $class = new \ReflectionClass('\\Sakura\\Addons\\' . $plugin_class_name);
            } catch (\ReflectionException $e) {
                throw new TGException('The name of the plugin\'s class must be the same of the file name');
            }
            if (!$class->hasConstant('INFO')) {
                throw new TGException('No valid information found for the plugin ' . $plugin);
            } else {
                $content = $class->getConstant('INFO');
            }
            if (!is_array($content) || !((array_key_exists('author', $content) && array_key_exists('version', $content) && array_key_exists('description', $content)))) {
                throw new TGException('The info constant of the class is incomplete.');
            }
        }
    }


}