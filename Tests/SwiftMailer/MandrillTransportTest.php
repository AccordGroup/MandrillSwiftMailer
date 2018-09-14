<?php

namespace Accord\MandrillSwiftMailer\Tests\SwiftMailer;

use Accord\MandrillSwiftMailer\SwiftMailer\MandrillTransport;
use Symfony\Component\Config\Definition\Processor;

class MandrillTransportTest extends \PHPUnit_Framework_TestCase{

    const MANDRILL_TEST_API_KEY = 'ABCDEFG1234567';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Swift_Events_EventDispatcher
     */
    protected $dispatcher;

    protected function setUp()
    {
        $this->dispatcher = $this->createMock('\Swift_Events_EventDispatcher');
    }

    /**
     * Returns an instance of the transport through which test messages can be sent
     *
     * @return MandrillTransport
     */
    protected function createTransport()
    {
        $transport = new MandrillTransport($this->dispatcher);
        $transport->setApiKey(self::MANDRILL_TEST_API_KEY);
        return $transport;
    }

    protected function createTransportAsync()
    {
        $transport = new MandrillTransport($this->dispatcher);
        $transport->setApiKey(self::MANDRILL_TEST_API_KEY);
        $transport->setAsync(true);
        return $transport;
    }

	protected function createPngContent()
	{
		return base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEX/TQBcNTh/AAAAAXRSTlPM0jRW/QAAAApJREFUeJxjYgAAAAYAAzY3fKgAAAAASUVORK5CYII=");
	}

    public function testInlineCss()
    {
        $transport = $this->createTransport();
        $message = new \Swift_Message('Test Subject', 'Foo bar');
        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;
        $message->getHeaders()->addTextHeader('X-MC-InlineCSS', true);
        $mandrillMessage = $transport->getMandrillMessage($message);
        $this->assertEquals(true, $mandrillMessage['inline_css']);
        $this->assertMessageSendable($message);
    }

    public function testGoogleAnalytics()
    {
        $transport = $this->createTransport();
        $message = new \Swift_Message('Test Subject', 'Foo bar');
        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;
        $message->getHeaders()->addTextHeader('X-MC-GoogleAnalytics', 'example.com,www.example.com');
        $mandrillMessage = $transport->getMandrillMessage($message);
        $this->assertEquals(['example.com','www.example.com'], $mandrillMessage['google_analytics_domains']);
        $this->assertMessageSendable($message);
    }

    public function testGoogleAnalyticsCampaign()
    {
        $transport = $this->createTransport();
        $message = new \Swift_Message('Test Subject', 'Foo bar');
        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;
        $message->getHeaders()->addTextHeader('X-MC-GoogleAnalyticsCampaign', 'campaign');
        $mandrillMessage = $transport->getMandrillMessage($message);
        $this->assertEquals('campaign', $mandrillMessage['google_analytics_campaign']);
        $this->assertMessageSendable($message);
    }

    public function testTags()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $message->getHeaders()->addTextHeader('X-MC-Tags', 'foo,bar');

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('foo', $mandrillMessage['tags'][0]);
        $this->assertEquals('bar', $mandrillMessage['tags'][1]);

        $this->assertMessageSendable($message);
    }

    public function testTagsArray()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $message->getHeaders()->addTextHeader('X-MC-Tags', array('foo','bar'));

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('foo', $mandrillMessage['tags'][0]);
        $this->assertEquals('bar', $mandrillMessage['tags'][1]);

        $this->assertMessageSendable($message);
    }

	public function testCustomHeaders()
	{
		$transport = $this->createTransport();
		$message = new \Swift_Message('Test Subject', 'Foo bar');
		$message
			->addTo('to@example.com', 'To Name')
			->addFrom('from@example.com', 'From Name')
		;
		$message->getHeaders()->addTextHeader('X-Test-Header', true);
		$mandrillMessage = $transport->getMandrillMessage($message);

		$this->assertEquals(true, $mandrillMessage['headers']['X-Test-Header']);
		$this->assertMessageSendable($message);
	}

    public function testSubAccount()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar');
        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertArrayNotHasKey('subaccount', $mandrillMessage);

        $transport->setSubaccount('account-123');

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertArrayHasKey('subaccount', $mandrillMessage);
        $this->assertEquals('account-123', $mandrillMessage['subaccount']);

        $this->assertMessageSendable($message);
    }

    public function testListUnsubscribe()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $message->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@example.com>');

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('<mailto:unsubscribe@example.com>', $mandrillMessage['headers']['List-Unsubscribe']);

        $this->assertMessageSendable($message);
    }

    public function testMultipartNullContentType()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar');

        $message
            ->addPart('<p>Foo bar</p>', 'text/html')
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('Foo bar', $mandrillMessage['text'], 'Multipart email should contain plaintext message');
        $this->assertEquals('<p>Foo bar</p>', $mandrillMessage['html'], 'Multipart email should contain HTML message');

        $this->assertMessageSendable($message);
    }

    public function testMultipartPlaintextFirst()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar', 'text/plain');

        $message
            ->addPart('<p>Foo bar</p>', 'text/html')
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('Foo bar', $mandrillMessage['text'], 'Multipart email should contain plaintext message');
        $this->assertEquals('<p>Foo bar</p>', $mandrillMessage['html'], 'Multipart email should contain HTML message');

        $this->assertMessageSendable($message);
    }

    public function testMultipartHtmlFirst()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', '<p>Foo bar</p>', 'text/html');

        $message
            ->addPart('Foo bar', 'text/plain')
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('Foo bar', $mandrillMessage['text'], 'Multipart email should contain plaintext message');
        $this->assertEquals('<p>Foo bar</p>', $mandrillMessage['html'], 'Multipart email should contain HTML message');

        $this->assertMessageSendable($message);
    }

    public function testPlaintextMessage()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', 'Foo bar', 'text/plain');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertNull($mandrillMessage['html'], 'Plaintext only email should not contain HTML counterpart');
        $this->assertEquals('Foo bar', $mandrillMessage['text']);

        $this->assertMessageSendable($message);
    }

    public function testPlaintextMessageWithAsyncTransport()
    {
        $transport = $this->createTransportAsync();

        $message = new \Swift_Message('Test Subject', 'Foo bar', 'text/plain');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $this->assertMessageSendable($message, $transport);
    }

    public function testMessageWithAutoText()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', '<p>Foo bar</p>', 'text/html');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $message->getHeaders()->addTextHeader('X-MC-Autotext', 'y');

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertTrue($mandrillMessage['auto_text'], 'auto_text is not set to true');

        $this->assertMessageSendable($message);
    }

    public function testMessageWithBoolAutoText()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', '<p>Foo bar</p>', 'text/html');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $message->getHeaders()->addTextHeader('X-MC-Autotext', true);

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertTrue($mandrillMessage['auto_text'], 'auto_text is not set to true');
    }

    public function testHtmlMessage()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', '<p>Foo bar</p>', 'text/html');

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertNull($mandrillMessage['text'], 'HTML only email should not contain plaintext counterpart');
        $this->assertEquals('<p>Foo bar</p>', $mandrillMessage['html']);

        $this->assertMessageSendable($message);
    }

    public function testMessage()
    {
        $transport = $this->createTransport();

        $message = new \Swift_Message('Test Subject', '<p>Foo bar</p>', 'text/html');

        $attachment = new \Swift_Attachment('FILE_CONTENTS', 'filename.txt', 'text/plain');
        $message->attach($attachment);

		$image = new \Swift_Image($this->createPngContent(), 'pixel.png', 'image/png');
		$cid = $message->embed($image);

        $message
            ->addTo('to@example.com', 'To Name')
            ->addFrom('from@example.com', 'From Name')
            ->addCc('cc-1@example.com', 'CC 1 Name')
            ->addCc('cc-2@example.com', 'CC 2 Name')
            ->addBcc('bcc-1@example.com', 'BCC 1 Name')
            ->addBcc('bcc-2@example.com', 'BCC 2 Name')
            ->addReplyTo('reply-to@example.com', 'Reply To Name')
        ;

        $mandrillMessage = $transport->getMandrillMessage($message);

        $this->assertEquals('<p>Foo bar</p>', $mandrillMessage['html']);
        $this->assertNull($mandrillMessage['text'], 'HTML only email should not contain plaintext counterpart');
        $this->assertEquals('Test Subject', $mandrillMessage['subject']);
        $this->assertEquals('from@example.com', $mandrillMessage['from_email']);
        $this->assertEquals('From Name', $mandrillMessage['from_name']);

        $this->assertMandrillMessageContainsRecipient('to@example.com', 'To Name', 'to', $mandrillMessage);
        $this->assertMandrillMessageContainsRecipient('cc-1@example.com', 'CC 1 Name', 'cc', $mandrillMessage);
        $this->assertMandrillMessageContainsRecipient('cc-2@example.com', 'CC 2 Name', 'cc', $mandrillMessage);
        $this->assertMandrillMessageContainsRecipient('bcc-1@example.com', 'BCC 1 Name', 'bcc', $mandrillMessage);
        $this->assertMandrillMessageContainsRecipient('bcc-2@example.com', 'BCC 2 Name', 'bcc', $mandrillMessage);

        $this->assertMandrillMessageContainsAttachment('text/plain', 'filename.txt', 'FILE_CONTENTS', $mandrillMessage);
        $this->assertMandrillMessageContainsImage('image/png', $cid, $this->createPngContent(), $mandrillMessage);


        $this->assertArrayHasKey('Reply-To', $mandrillMessage['headers']);
        $this->assertEquals('reply-to@example.com <Reply To Name>', $mandrillMessage['headers']['Reply-To']);

        $this->assertMessageSendable($message);
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $content
     * @param array $message
     */
    protected function assertMandrillMessageContainsAttachment($type, $name, $content, array $message){
        foreach($message['attachments'] as $attachment){
            if($attachment['type'] === $type && $attachment['name'] === $name){
                $this->assertEquals($content, base64_decode($attachment['content']));
                return;
            }
        }
        $this->fail(sprintf('Expected Mandrill message to contain a %s attachment named %s', $type, $name));
    }


    /**
     * @param string $type
     * @param string $cid
     * @param string $content
     * @param array $message
     */
    protected function assertMandrillMessageContainsImage($type, $cid, $content, array $message){
        foreach($message['images'] as $image){
            if($image['type'] === $type && sprintf('cid:%s',$image['name']) === $cid ){
                $this->assertEquals($content, base64_decode($image['content']));
                return;
            }
        }
        $this->fail(sprintf('Expected Mandrill message to contain an %s image with cid %s', $type, $cid));
    }

    /**
     * @param string $email
     * @param string $name
     * @param string $type
     * @param array $message
     */
    protected function assertMandrillMessageContainsRecipient($email, $name, $type, array $message){
        foreach($message['to'] as $recipient){
            if($recipient['email'] === $email && $recipient['name'] === $name && $recipient['type'] === $type){
                $this->assertTrue(true);
                return;
            }
        }
        $this->fail(sprintf('Expected Mandrill message "to" contain %s recipient %s <%s>', $type, $email, $name));
    }

    /**
     * Performs a test send through the Mandrill API. Provides details of failure if there are any problems.
     * @param MandrillTransport|null $transport
     * @param \Swift_Message $message
     */
    protected function assertMessageSendable(\Swift_Message $message, $transport = null)
    {
        if(!$transport) $transport = $this->createTransport();

        $this->assertNotNull($transport->getApiKey(), 'No API key specified');

        $parameters = array(
            'message' => $transport->getMandrillMessage($message)
        );

        try{
            $configuration = new MessageSendConfiguration();
            $processor = new Processor();
            $processor->processConfiguration($configuration, $parameters);
        }
        catch(\Exception $e){
            $this->fail(sprintf(
                "Mandrill message contains errors, %s\n\n%s",
                $e->getMessage(),
                json_encode($parameters['message'], JSON_PRETTY_PRINT)
            ));
        }
    }

    protected function assertResultApiItemQueuedOrSent(array $item)
    {
        $status = (isset($item['status']) ? $item['status'] : 'unknown_status');
        $reason = (isset($item['reject_reason']) ? $item['reject_reason'] : 'unknown_reason');

        if($status !== 'queued' && $status !== 'sent'){
            $this->fail(
                sprintf(
                    'Mandrill Test API could not process message (%s: %s)',
                    $status,
                    $reason
                )
            );
        }
    }

}
