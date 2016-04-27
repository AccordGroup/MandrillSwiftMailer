# Accord\MandrillSwiftMailer

[![Build Status](https://travis-ci.org/AccordGroup/MandrillSwiftMailer.svg?branch=master)](https://travis-ci.org/AccordGroup/MandrillSwiftMailer)

A SwiftMailer transport implementation for Mandrill

## Installation

Require the package with composer

    composer require accord/mandrill-swiftmailer

## Usage Example

    $transport = new MandrillTransport($dispatcher);
    $transport->setApiKey('ABCDEFG12345');
    $transport->setAsync(true); # Optional
    $transport->send($message);
    
## Using Mandrill-specific Features

### auto_text

Automatically generate a text part for messages that are not given text

    $message->getHeaders()->addTextHeader('X-MC-Autotext', true);
    
### tags

    $message->getHeaders()->addTextHeader('X-MC-Tags', 'foo,bar');
    
or

    $message->getHeaders()->addTextHeader('X-MC-Tags', array('foo','bar'));
    
### inline_css

    $message->getHeaders()->addTextHeader('X-MC-InlineCSS', true);
    
### List-Unsubscribe
   
    $message->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@example.com>');
