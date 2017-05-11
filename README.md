# Accord\MandrillSwiftMailer

[![Build Status](https://travis-ci.org/AccordGroup/MandrillSwiftMailer.svg?branch=master)](https://travis-ci.org/AccordGroup/MandrillSwiftMailer)

A SwiftMailer transport implementation for Mandrill

If you'd like us to implement more [Mandrill-specific features](https://mandrillapp.com/api/docs/messages.JSON.html), let us know by [submitting an issue](https://github.com/AccordGroup/MandrillSwiftMailer/issues/new).

## Installation

Require the package with composer

    composer require accord/mandrill-swiftmailer

## Usage Example

    $transport = new MandrillTransport($dispatcher);
    $transport->setApiKey('ABCDEFG12345');
    $transport->setAsync(true); # Optional
    $transport->send($message);
    
## Using Mandrill-specific Features

### Asynchronous Mode

Enable a background sending mode that is optimized for bulk sending

    $transport->setAsync(true);

### Auto Text

Automatically generate a text part for messages that are not given text

    $message->getHeaders()->addTextHeader('X-MC-Autotext', true);
    
    
### Google Analytics tracking

Mandrill supports automatic Google Analytics tracking for your links. [docs](https://mandrill.zendesk.com/hc/en-us/articles/205582577-About-Google-Analytics-Tracking)

    $message->getHeaders()->addTextHeader('X-MC-GoogleAnalytics', 'example.com,www.example.com');
    // optional defaults to from address
    $message->getHeaders()->addTextHeader('X-MC-GoogleAnalyticsCampaign', 'campaign');
    
### Tags

An array of string to tag the message with

    $message->getHeaders()->addTextHeader('X-MC-Tags', 'foo,bar');
    
or

    $message->getHeaders()->addTextHeader('X-MC-Tags', array('foo','bar'));
    
### Inline CSS

Automatically inline all CSS styles provided in the message HTML - only for HTML documents less than 256KB in size

    $message->getHeaders()->addTextHeader('X-MC-InlineCSS', true);
    
### List Unsubscribe

Mandrill automatically adds a List-Unsubscribe header to all emails that include a Mandrill-generated unsubscribe link. If recipients use an email program that supports the List-Unsubscribe header (like Hotmail, AOL, or Yahoo), they can use the option in their email program to unsubscribe.
   
    $message->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@example.com>');
