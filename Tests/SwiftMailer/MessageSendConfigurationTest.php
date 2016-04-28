<?php

namespace Accord\MandrillSwiftMailer\Tests\SwiftMailer;

use Symfony\Component\Config\Definition\Processor;

class MessageSendConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testConfiguration()
    {
        $path = __DIR__ . '/../Resources/message.json';
        if(!file_exists($path)){
            $this->fail(sprintf(
                '"%s" could not be found',
                $path
            ));
            return;
        }

        $message = json_decode(file_get_contents($path), true);
        if(json_last_error() !== JSON_ERROR_NONE){
            $this->fail(sprintf(
                '"%s" could not be parsed, %s',
                $path,
                json_last_error_msg()
            ));
            return;
        }

        $parameters = array('message' => $message);

        try{
            $configuration = new MessageSendConfiguration();
            $processor = new Processor();
            $processor->processConfiguration($configuration, $parameters);
        }
        catch(\Exception $e){
            $this->fail(sprintf(
                "Error in %s, %s\n\n%s",
                $path,
                $e->getMessage(),
                json_encode($parameters['message'], JSON_PRETTY_PRINT)
            ));
        }

    }

}