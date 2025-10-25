
<?php
namespace App\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

final class LoggerFactory {
    public static function make(string $name, string $path): Logger {
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0777, true);
        }
        $logger = new Logger($name);
        $handler = new StreamHandler($path, Logger::INFO, true);
        $handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($handler);
        return $logger;
    }
}
