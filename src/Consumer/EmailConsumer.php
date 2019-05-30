<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Swift_Transport;
use Swift_Mime_Message;
use Swift_TransportException;

/**
 * EmailConsumer
 *
 * @author Jonadabe de Souza Nascimento <jhonndabi.s.n@gmail.com>
 */
class EmailConsumer
{
    const MAX_RETRY = 3;

    const RETRY_AFTER_FACTOR = 3;

    /** @var MinuteRateController */
    private $minutesRateController;

    /** @var Swift_Transport */
    protected $transport;

    /**
     * EmailConsumer constructor.
     * @param MinuteRateController $minutesRateController
     * @param Swift_Transport $transport
     */
    public function __construct(MinuteRateController $minutesRateController, Swift_Transport $transport)
    {
        $this->minutesRateController = $minutesRateController;
        $this->transport = $transport;
    }

    /**
     * @param AMQPMessage $msg
     * @return int
     * @throws \Exception
     */
    public function execute(AMQPMessage $msg)
    {
        if (! $this->minutesRateController->checkCanSend()) {
            return ConsumerInterface::MSG_REJECT_REQUEUE;
        }

        return $this->processMessage($msg);
    }

    /**
     * @param AMQPMessage $msg
     * @return int
     * @throws \Exception
     */
    public function processMessage(AMQPMessage $msg)
    {
        /** @var Swift_Mime_Message $message */
        $message = unserialize($msg->getBody());

        $this->sendEmail($message);

        return ConsumerInterface::MSG_ACK;
    }

    /**
     * @param Swift_Mime_Message $message
     * @param int $failures
     */
    private function sendEmail(Swift_Mime_Message $message, $failures = 0)
    {
        if (! $this->transport->isStarted()) {
            $this->transport->start();
        }

        try {
            $this->transport->send($message);
        } catch (Swift_TransportException $e) {
            $this->transport->stop();

            if ($failures >= self::MAX_RETRY) {
                return;
            }

            $failures++;
            sleep($failures ** self::RETRY_AFTER_FACTOR);
            $this->sendEmail($message, $failures);
        } catch (\Exception $e) {
            $this->transport->stop();
            return;
        } finally {
            $this->transport->stop();
        }
    }
}
