<?php declare(strict_types=1);

namespace Sakura;

use Sakura\Exception\TGException;
use Sakura\Interfaces\LoggerInterface;


/**
 * Class Logger, part of the "Sakura" package.
 *
 * This is a very very basic class used to print messages
 * to the terminal output. There's nothing hard in that, it's
 * simply
 *
 * *COLORFUL*
 *
 * @see LoggerInterface _This class is also an implementation of the interface LoggerInterface._
 *
 * @package Sakura
 * @implements LoggerInterface
 */
class Logger implements LoggerInterface {
    /**
     * Boolean variable, which can be either true
     * or false to enable or disable the safe mode.
     * The safe mode is a bot's execution mode, which
     * automatically kill the process if any WARN
     * or FATAL log is printed. That's still in an
     * experimental way, so I advice you to leave it disabled :>
     *
     * @var bool
     */
    private static $mode;
    /**
     * Level of strings reporting. It must be a number
     * between -1 and 3, where:
     * -1 => No output.
     * 0 => Every kind of output.
     * 1 => Only notices.
     * 2 => Only warns.
     * 3 => Only fatals.
     *
     * @var int
     */
    private static $logger_level;
    /*
     * Log levels.
     */
	const NOTICE = 1;
	const WARN = 2;
	const FATAL = 3;
    /**
     * A bi-dimensional array containing the
     * codes of each CLI color.
     * @link https://gist.github.com/sallar/5257396 Example of CLI colors.
     *
     * @constant (array)[]
     */
	const COLORS = [
			'background' => [
				'black' => '40',
				'red' => '41',
				'green' => '42',
				'yellow' => '43',
				'blue' => '44',
				'magenta' => '45',
				'cyan' => '46',
				'light_gray' => '47',
			],
			'string' => [
				'red' => '0;31',
				'blue' => '0;34',
				'yellow' => '1;33',
				'purple' => '0;35',
				'white' => '1;37',
				'green' => '0;32',
				'cyan' => '0;36',
				'black' => '0;30',
				'brown' => '0;33',
				'light_gray' => '0;37',
			],
		
		];

    /**
     * Logger constructor.
     *
     * @param int $logger Logger level. Optional.
     * @param bool $safe_mode Boolean value for the safe mode. Optional.
     */
	public function __construct($logger = 0, $safe_mode = true)
    {
        self::$logger_level = $logger;
        self::$mode = $safe_mode;
    }

    /**
     * Real log function.
     *
     * Call this function to print colored strings.
     *
     * @param string $message String to print.
     * @param int $type Type of the printed string. Optional.
     * @param string $background Background color of the string. Optional.
     * @param string $string_color Color of the string. Optional.
     * @return null|string
     * @throws TGException
     */
    public static function log(string $message, int $type = self::NOTICE, string $background = '', string $string_color = ''): ?string {
		switch(self::$logger_level) { // checking logger level
			case -1:
				return NULL;
			case 0:
				break;
			case 1:
				if($type === self::FATAL || $type === self::WARN) {
                    return NULL;
				}
				break;
			case 2:
				if($type === self::NOTICE || $type === self::FATAL) {
                    return NULL;
				}
				break;
			case 3:
				if($type === self::NOTICE || $type === self::WARN) {
                    return NULL;
				}
				break;
			default:
				throw new TGException('Unrecognized type of string given.');
		}

		// setting default colors for self::WARN and self::FATAL
		if(!empty($background) && !in_array($background, array_keys(self::COLORS['background']))) {
			throw new TGException('Unrecognized background color.');
		}
		if(!empty($string_color) && !in_array($string_color, array_keys(self::COLORS['string']))) {
			throw new TGException('Unrecognized string color.');
		}

		// building the string
		$msg = '';
		if(empty($string_color)) {
            $msg .= "\033[" . self::COLORS['string']['white'];
		} else {
			$msg .= "\033[" . self::COLORS['string'][$string_color];
		}
		$msg .= 'm';
		if(empty($background)) {
			switch($type) {
				case self::NOTICE:
					$msg .= "\033[" . self::COLORS['background']['green'];
					break;
				case self::WARN:
					$msg .= "\033[" . self::COLORS['background']['yellow'];
					break;
				case self::FATAL:
					$msg .= "\033[" . self::COLORS['background']['red'];
					break;
			}
		} else {
			$msg .= "\033[" . self::COLORS['background'][$background];
		}
		$msg .= "m" . $message . "\033[0m" . PHP_EOL; // adding final stuff
		
		print($msg);

		if(($type === self::WARN || $type === self::FATAL) && self::$mode) { // die if safe_mode is enabled
			die;
		}
		
		return $msg; // returning colored string
	}
}