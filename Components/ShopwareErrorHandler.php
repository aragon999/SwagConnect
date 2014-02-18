<?php

namespace Shopware\Bepado\Components;

use Bepado\SDK\ErrorHandler;
use Bepado\SDK\Struct\Error;

class ShopwareErrorHandler extends ErrorHandler
{

    /**
     * Handle error
     *
     * @param Error $error
     * @throws \Exception
     * @return void
     */
    public function handleError(Error $error)
    {
        throw new \Exception($error->message);
    }

    /**
     * Handle exception
     *
     * @param \Exception $exception
     * @throws \Exception
     * @return void
     */
    public function handleException(\Exception $exception)
    {
        $logger = new Logger(Shopware()->Db());
        $logger->write(true, null, $exception);

        throw $exception;
    }

}