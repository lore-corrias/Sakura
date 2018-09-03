<?php declare(strict_types=1);

namespace Sakura\Exception;


use Sakura\Interfaces\ExceptionHandlerInterface;
use \Sakura\Logger;

/**
 * Class ExceptionHandler, part of the "Sakura/Exception" namespace.
 *
 * This class is used to store and set the default exception_handler for the script,
 * which can be either defined by the user or by the script itself.
 *
 * @see ExceptionHandlerInterface _This class is also an implementation of the interface ExceptionHandlerInterface._
 *
 * @package Sakura\Exception
 * @implements ExceptionHandlerInterface
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * If set, this variable contains the handler defined by the user.
     * Otherwise, this is simply NULL
     *
     * @var callable|null
     */
    private $user_handler;

    /**
     * If the default handler is used, this will be the path
     * used by it to log exceptions.
     *
     * @var null|string
     */
    private $log_directory;

    /**
     * Public method, which will be set as exception handler if no other
     * function is provided via the class' constructor.
     *
     * @param \Throwable $exception Various info about the exception thrown
     * @throws TGException
     */
    public function handler(\Throwable $exception)
    {
        error_log($exception->getMessage(), 3, $this->log_directory . '/' . date('d-m-Y h:i:s'));
        $message = "[%s] Exception caught: %d\n Message: %s\n File: %s\n Line: %d\n";
        Logger::log(sprintf($message, date('d-m-Y h:i:s'), $exception->getCode(), $exception->getMessage(), $exception->getTrace()['0']['file'], $exception->getTrace()['0']['line']), Logger::FATAL);
    }

    /**
     * ExceptionHandler constructor.
     *
     * This constructor is automatically called in the TGBot class.
     *
     * @param callable|null $handler A valid callable for the {@link http://php.net/manual/en/function.set-exception-handler.php "set_exception_handler"} function. Optional.
     * @param string|null $log_directory Path to the log directory. Required if no $handler is provided.
     * @throws TGException
     * @throws \ReflectionException
     */
    public function __construct(?string $log_directory = NULL, ?callable $handler = NULL)
    {
        set_exception_handler([$this, 'handler']);
        if (is_null($handler)) {
            if (!is_dir($log_directory)) {
                throw new TGException('Invalid log path given.');
            }
            $this->log_directory = $log_directory;
            set_exception_handler([$this, 'handler']);
        } else {
            $this->user_handler = $handler;
            $this->validateHandlerFunction($handler);
            set_exception_handler($this->user_handler);
        }
    }

    /**
     * Public method, used by the class to check the validity of the user-defined handler.
     *
     * @param callable $handler Callable to validate.
     * @return bool
     * @throws \ReflectionException
     * @throws TGException
     */
    private function validateHandlerFunction(callable $handler): bool
    {
        $function_info = new \ReflectionFunction($handler);
        $parameters = $function_info->getParameters(); // obtaining the function's parameters.
        $parameters_count = count($parameters);
        if ($parameters_count !== 1) {          // callable must only accept one parameter
            throw new TGException(sprintf('The exception handler must accept only one parameter, %d given', $parameters_count));
        }
        $parameter_type = $parameters[0]->getClass();
        try {
            $parameter_class_name = $parameter_type->getName();
        } catch (\ReflectionException $e) {
            throw new TGException('The parameter of the function must be part of the Throwable interface.');
        }
        if ($parameter_class_name !== 'Throwable') { // parameter must be part of the Throwable interface
            throw new TGException(sprintf("The callable must have one parameter, which has to be part of the \"Throwable\" interface. %s given.", $parameter_class_name));
        }
        return TRUE;
    }

    /**
     * Public method, use it to get the current handler defined by the user
     * (if any).
     *
     * @return callable
     */
    public function getUserHandler(): ?callable
    {
        return $this->user_handler;
    }
}