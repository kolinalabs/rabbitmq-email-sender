<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Fixtures;

/**
 * SwiftFailTransport
 *
 * @author Jonadabe de Souza Nascimento <jhonndabi.s.n@gmail.com>
 */
class SwiftFailTransport implements \Swift_Transport
{
    /**
     * @inheritdoc
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function stop()
    {
        //
    }

    /**
     * @inheritdoc
     * @throws \Swift_TransportException
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        throw new \Swift_TransportException('send failed!');
    }

    /**
     * @inheritdoc
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        //
    }
}
