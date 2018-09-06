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

/**
 * EmailConsumer
 *
 * @author Jonadabe de Souza Nascimento <jhonndabi.s.n@gmail.com>
 */
class EmailConsumer
{
    /** @var Swift_Transport */
    protected $transport;

    /**
     * EmailConsumer constructor.
     * @param Swift_Transport $transport
     */
    public function __construct(Swift_Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param AMQPMessage $msg
     * @return bool
     */
    public function execute(AMQPMessage $msg)
    {
        return $this->processMessage($msg);
    }

    /**
     * @param AMQPMessage $msg
     * @return int
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
     */
    private function sendEmail(Swift_Mime_Message $message)
    {
        if (! $this->transport->isStarted()) {
            $this->transport->start();
        }

        $this->transport->send($message);
        $this->transport->stop();
    }
}
