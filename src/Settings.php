<?php /** @noinspection ALL */
declare(strict_types=1);

namespace Sakura;

use Sakura\Exception\TGException;
use Sakura\Interfaces\SettingsInterface;


/**
 * Class Settings, part of the "Sakura" namespace.
 *
 * This class can be used to manage the bot's settings in any time.
 * The constructor of the class "Sakura/TGBot" will automatically create an instance
 * of this class and will save it in the variable "$settings".
 *
 * @see SettingsInterface _This class is also an implementation of the interface SettingsInterface._
 *
 * @package Sakura
 * @implements SettingsInterface
 */
class Settings implements SettingsInterface
{
    /**
     * Array used to store the default bot's settings.
     * This settings will be used if no others will be supplied.
     *
     * @constant (bool|string|int)[]
     */
    const DEF_SETTINGS = [
        'request_timeout' => 0,
        'log_dir' => 'Sakura_logs',
        'request_useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0',
        'as_array' => false,
        'save_instance' => true,
        'logger' => 0,
        'safe_mode' => false,
        'config_dir' => 'configs',
        'autoload_backup' => true,
    ];
    /**
     * Extension of the config files.
     *
     * @var string
     */
    const CONF_EXT = '.sakura';
    /**
     * Array where all the settings defined by the user are stored.
     * You can access this variable using the getSettings method.
     * Is also possible to reset some (or all) of these settings to the values stored in DEF_SETTINGS by using resetSettings().
     *
     * @var (bool|int|string)[]
     */
    private $settings;

    /**
     * Settings constructor. The constructor is automatically called in the TGBot class.
     *
     * @param array|null $settings An array of custom settings. Optional.
     * @throws TGException
     */
    public function __construct(array $settings = self::DEF_SETTINGS)
    {
        if ($this->settings['autoload_backup'] && $this->tryLoadBackup($this->settings['config_dir'])) {
            return; // avoiding settings from being overwritten.
        }
        // cleaning the settings from invalid variables.
        if (count($settings) < count(self::DEF_SETTINGS)) {
            $this->settings = $this->parseSettings(array_merge(self::DEF_SETTINGS, $settings));
        } else {
            $this->settings = $this->parseSettings($settings);
        }
        $this->exportSettings($this->settings['config_dir']);
    }

    /**
     * Private function, called if the field "path" in the constructor is not empty.
     *
     * This function evaluates the integrity of a settings file. If valid, it returns
     * true and set the current variable $settings to the file's content, else, it returns false.
     *
     * @param string $path
     * @return bool
     * @throws TGException
     */
    private function checkFile(string $path): bool
    {
        if (substr($path, -7, 7) !== self::CONF_EXT) {
            Logger::log(sprintf('The config file must be a %s', self::CONF_EXT), Logger::WARN);
            $retry = readline('Insert a new path (no entry = abort): ');
            if (empty($retry)) {
                return FALSE;
            }
            $this->{__FUNCTION__}($retry); // recursive call of the function until the operation aborts or a valid path is found.
        } else {
            $this->settings = $this->parseSettings(unserialize(@file_get_contents($path), [])); // safe unserialize.
        }
        return TRUE;
    }

    private function tryLoadBackup($dir): bool
    {
        $backup_files = glob($dir . '/*.Sakura');
        if (empty($backup_files)) {
            Logger::log('No backup file found. Loading configs from array.', Logger::WARN);
            return FALSE;
        } else {
            $answer = '';
            while (!in_array(strtolower($answer), ['y', 'n'])) {
                Logger::log('A setting file was found in the directory specified in the settings. Would you like to load the configs from that file? ', Logger::WARN);
                $answer = readline('(Y/n) : ');
                if (empty($answer)) {
                    $answer = 'Y';
                }
            }
            if ($answer !== 'Y') {
                Logger::log('Settings not loaded from file. Loading settings from the provided array.', Logger::WARN);
                return FALSE;
            }
        }
        foreach ($backup_files as $edited) {
            $created_date[$edited] = strtotime(str_replace('.Sakura', '', basename($edited))); // obtaining each file's created date in timestamp.
        }
        $last_edited_file = array_search(max($created_date), $created_date); // last file created.
        if (!$this->checkFile($last_edited_file)) {
            Logger::log('The settings file is invalid. Loading configs from the provided array.', Logger::WARN);
            return FALSE;
        }
        Logger::log(sprintf('Settings successfully loaded from file %s!', $last_edited_file), Logger::NOTICE);
        return TRUE;
    }

    /**
     * Private function, used to parse a variable containing all the settings.
     *
     * If the array is correctly set, the function simply returns the same array you specified,
     * else, it returns a "fixed" version of the array, usable as a normal settings array.
     *
     * @param array $parse
     * @return array
     * @throws TGException
     */
    private function parseSettings(array $parse): array
    {
        foreach ($parse as $conf => $value) {
            switch ($conf) {
                case 'as_array':
                case 'safe_mode':
                case 'autoload_backup':
                case 'save_instance':
                    if (!is_bool($value)) {
                        $parse[$conf] = self::DEF_SETTINGS[$conf];
                        Logger::log(sprintf('The variable %s must be of boolean type, %s given.', $conf, gettype($value)), Logger::WARN);
                    }
                    break;
                case 'request_timeout':
                    if (!is_int($value)) {
                        $parse[$conf] = self::DEF_SETTINGS[$conf];
                        Logger::log(sprintf('The variable %s must be  type, %s given.', $conf, gettype($value)), Logger::WARN);
                    }
                    break;
                case 'logger':
                    if (!in_array($value, [-1, 0, 1, 2, 3])) {
                        throw new TGException(sprintf('The logger level must be a number between -1 and 3, %d given', $value), Logger::WARN);
                    }
                    break;
                case 'log_dir':
                    if (!is_string($value)) {
                        $parse[$conf] = self::DEF_SETTINGS[$conf];
                        $value = self::DEF_SETTINGS[$conf];
                        Logger::log(sprintf('The setting %s must be a string, %s given', $conf, gettype($value)), Logger::WARN);
                        break;
                    }
                    if (empty($value) && !empty(ini_get('error_log'))) {
                        // if no valid path is supplied, the config is set to the default value of the system.
                        $parse[$conf] = ini_get('error_log');
                    } elseif (empty($value) && empty(ini_get('error_log'))) {
                        $parse[$conf] = self::DEF_SETTINGS[$conf];
                    }
                    if (!is_dir($value)) {
                        mkdir($value, 0777, true);
                    }
                    break;
                case 'request_useragent':
                    if (!is_string($value)) {
                        $parse[$conf] = self::DEF_SETTINGS[$conf];
                        Logger::log(sprintf('The setting %s must be a string, %s given', $conf, gettype($value)), Logger::WARN);
                    }
                    break;
                case 'config_dir':
                    if (!is_string($value) || (is_string($value) && empty($value))) {
                        $parse[$conf] = self::DEF_SETTINGS[$conf];
                        Logger::log(sprintf('The setting %s must be a path to the config file, %d given', $conf, gettype($value)), Logger::WARN);
                        break;
                    }
                    if (!is_dir($value) && !empty($value)) {
                        mkdir($value, 0777, true);
                    }
                    break;
                default:
                    unset($parse[$conf]);
                    Logger::log(sprintf("The setting %s does not exist, deleting it.", $conf), Logger::WARN);
            }
        }
        return $parse;
    }


    /**
     * Public function, used to reset the indicated settings to their default values, stored in DEF_SETTINGS.
     *
     * @param array $settings An array containing the variables to reset.
     * @throws TGException
     */
    public function resetSettings(array $settings): void
    {
        if (count($settings) < count(self::DEF_SETTINGS)) {
            foreach ($settings as $reset) {
                if (!isset($this->settings[$reset])) continue;
                else $this->settings[$reset] = self::DEF_SETTINGS[$reset];
            }
        } elseif (array_diff_key($settings, self::DEF_SETTINGS) === []) {
            $this->settings = self::DEF_SETTINGS;
        } else {
            Logger::log('Invalid configs provided.', Logger::WARN);
        }
    }

    /**
     * Public function, whose job is to return the private settings array.
     * And yes, that's all.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Public function, use it if you want to change any of the settings
     * during the program execution.
     *
     * @param array $change
     * @throws TGException
     */
    public function changeSettings(array $change): void
    {
        $change_parsed = $this->parseSettings($change);
        foreach ($change_parsed as $setting => $value) {
            $this->settings[$setting] = $value;
        }
    }

    /**
     * Public method, you can use it if you want to export your settings.
     *
     * @param string $dir Directory where the file will be saved.
     * @throws TGException
     */
    public function exportSettings(string $dir)
    {
        $path = $dir . '/' . date('d.m.Y') . self::CONF_EXT;
        @file_put_contents($path, serialize($this->settings));
        Logger::log(sprintf('Settings successfully exported! You can find them at %s', $path), Logger::NOTICE);
    }
}