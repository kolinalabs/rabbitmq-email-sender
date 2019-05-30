<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Consumer;

use App\Consumer\EmailConsumer;
use App\Consumer\MinuteRateController;
use App\Tests\Fixtures\SwiftFailTransport;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * EmailConsumerTest
 *
 * @author Jonadabe de Souza Nascimento <jhonndabi.s.n@gmail.com>
 *
 * @see https://symfony.com/blog/new-in-symfony-2-8-clock-mocking-and-time-sensitive-tests
 * @see https://symfony.com/doc/current/testing/database.html
 *
 * @group time-sensitive
 */
class EmailConsumerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testEmailSendFailRetry()
    {
        $amqpMessage = $this->prepareAMQPMessage();

        $emailConsumer = $this->prepareEmailConsumerWithFail();

        $dateTimeBeforeStart = \DateTime::createFromFormat('U', time());

        $emailConsumer->processMessage($amqpMessage);

        $dateTimeAfterStart = \DateTime::createFromFormat('U', time());

        $duration = $dateTimeAfterStart->diff($dateTimeBeforeStart)->s;

        $this->assertEquals(36, $duration);
    }

    /**
     * @throws \Exception
     */
    public function testEmailSendSuccess()
    {
        $amqpMessage = $this->prepareAMQPMessage();

        $emailConsumer = $this->prepareEmailConsumer();

        $this->assertEquals(
            ConsumerInterface::MSG_ACK,
            $emailConsumer->processMessage($amqpMessage)
        );
    }

    protected function prepareEmailConsumer()
    {
        $minuteRateController = new MinuteRateController();
        $swiftMailerTransport = $this->createMock(\Swift_Transport::class);

        return new EmailConsumer($minuteRateController, $swiftMailerTransport);
    }

    protected function prepareEmailConsumerWithFail()
    {
        $minuteRateController = new MinuteRateController();
        $swiftMailerTransport = new SwiftFailTransport();

        return new EmailConsumer($minuteRateController, $swiftMailerTransport);
    }

    protected function prepareAMQPMessage()
    {
        $message = $this->getMockBuilder(\Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new AMQPMessage(serialize($message));
    }
}
