<?php

namespace LaravelCommon\App\Services;

use LaravelCommon\App\Queries\LoggingConfigQuery;
use Rollbar\Payload\Level;
use Rollbar\Rollbar;

class RollbarLoggerService
{
    /**
     * Undocumented variable
     *
     * @var string
     */
    protected string $accessToken = '';

    /**
     * @var LoggingConfigQuery
     */
    protected LoggingConfigQuery $loggingConfigQuery;

    /**
     * @param LoggingConfigQuery $loggingConfigQuery
     */
    public function __construct(
        LoggingConfigQuery $loggingConfigQuery
    ) {
        $this->loggingConfigQuery = $loggingConfigQuery;
        // $appEnv = env('APP_ENV');
        // $this->accessToken = config('common-config')['env'][env('APP_ENV')]['rollbar_access_token'];
        // Rollbar::init(
        //     [
        //         'access_token' => $this->accessToken,
        //         'environtment' => $appEnv
        //     ]
        // );
    }

    /**
     * Has rollbar setup
     *
     * @return boolean
     */
    public function isSetup(): bool
    {
        return false;
        return !empty($this->accessToken);
    }

    /**
     * Log emergency message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function emergency(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::EMERGENCY, $message, $trace);
    }

    /**
     * Log alert message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function alert(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::ALERT, $message, $trace);
    }

    /**
     * Log critical message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function critical(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::CRITICAL, $message, $trace);
    }

    /**
     * Log error message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function error(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::ERROR, $message, $trace);
    }

    /**
     * Log warning message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function warning(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::WARNING, $message, $trace);
    }

    /**
     * Log notice message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function notice(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::NOTICE, $message, $trace);
    }

    /**
     * Log info message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function info(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::INFO, $message, $trace);
    }

    /**
     * Log debug message
     *
     * @param string $loggingName
     * @param string $message
     * @return void
     */
    public function debug(string $loggingName, string $message, array $trace = []): void
    {
        $this->log($loggingName, Level::DEBUG, $message, $trace);
    }

    /**
     * Do log to rollbar
     *
     * @param string $loggingName
     * @param string $level
     * @param string $message
     * @return void
     */
    private function log(string $loggingName, string $level, string $message, array $trace = []): void
    {
        $loggingName .= "_" . $level . "_rollbar_log";
        $loggingQuery = $this->loggingConfigQuery
            ->whereName($loggingName)
            ->whereIsEnabled();

        if ($loggingQuery->getIterator()->count() > 0) {
            Rollbar::log($level, $message, $trace);
        }
    }
}
