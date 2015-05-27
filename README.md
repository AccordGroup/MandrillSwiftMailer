# Accord\MandrillSwiftMailer

A SwiftMailer transport implementation for Mandrill

## Installation

Require the package with composer

    composer require accord/mandrill-swiftmailer

## Usage

    $transport = new MandrillTransport($dispatcher);
    $transport->setApiKey('ABCDEFG12345');
    $transport->send($message);