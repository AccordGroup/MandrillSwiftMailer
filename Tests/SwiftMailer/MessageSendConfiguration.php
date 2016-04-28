<?php

namespace Accord\MandrillSwiftMailer\Tests\SwiftMailer;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * A configuration matching the schema of a message send API call
 *
 * https://mandrillapp.com/api/docs/messages.html
 */
class MessageSendConfiguration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('message');

        $rootNode->validate()->ifTrue(function(array $message){
            $html = (isset($message['html']) ? $message['html'] : null);
            $text = (isset($message['text']) ? $message['text'] : null);
            return (!$html && !$text);
        })->thenInvalid('html or text must be provided');

        $rootNode->children()->scalarNode('html');
        $rootNode->children()->scalarNode('text');
        $rootNode->children()->scalarNode('subject')->isRequired();
        $rootNode->children()->scalarNode('from_email')->isRequired();
        $rootNode->children()->scalarNode('from_name');
        $this->buildToSection($rootNode);
        $rootNode->children()->arrayNode('headers')->ignoreExtraKeys();
        $rootNode->children()->booleanNode('important');
        $rootNode->children()->booleanNode('track_opens');
        $rootNode->children()->booleanNode('track_clicks');
        $rootNode->children()->booleanNode('auto_text');
        $rootNode->children()->booleanNode('auto_html');
        $rootNode->children()->booleanNode('inline_css');
        $rootNode->children()->booleanNode('url_strip_qs');
        $rootNode->children()->booleanNode('preserve_recipients');
        $rootNode->children()->booleanNode('view_content_link');
        $rootNode->children()->scalarNode('bcc_address');
        $rootNode->children()->scalarNode('tracking_domain');
        $rootNode->children()->scalarNode('signing_domain');
        $rootNode->children()->scalarNode('return_path_domain');
        $rootNode->children()->booleanNode('merge');
        $rootNode->children()->scalarNode('merge_language');
        $this->buildGlobalMergeVarsSection($rootNode);
        $this->buildMergeVarsSection($rootNode);
        $rootNode->children()->arrayNode('tags')->ignoreExtraKeys();
        $rootNode->children()->scalarNode('subaccount');
        $rootNode->children()->arrayNode('google_analytics_domains')->ignoreExtraKeys();
        $rootNode->children()->scalarNode('google_analytics_campaign');
        $rootNode->children()->arrayNode('metadata')->ignoreExtraKeys();
        $this->buildRecipientMetadataSection($rootNode);
        $this->buildAttachmentsSection($rootNode);
        $this->buildImagesSection($rootNode);

        return $treeBuilder;
    }

    protected function buildRecipientMetadataSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $metadataNode */
        $metadataNode = $rootNode->children()->arrayNode('recipient_metadata')->prototype('array');

        $metadataNode->children()->scalarNode('rcpt')->isRequired();
        $metadataNode->children()->arrayNode('values')->ignoreExtraKeys();
    }

    protected function buildMergeVarsSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $mergeNode */
        $mergeNode = $rootNode->children()->arrayNode('merge_vars')->prototype('array');

        $mergeNode->children()->scalarNode('rcpt')->isRequired();

        /** @var ArrayNodeDefinition $varsNode */
        $varsNode = $mergeNode->children()->arrayNode('vars')->prototype('array');

        $varsNode->children()->scalarNode('name')->isRequired();
        $varsNode->children()->scalarNode('content')->isRequired();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    protected function buildGlobalMergeVarsSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $varsNode */
        $varsNode = $rootNode->children()->arrayNode('global_merge_vars')->prototype('array');
        $varsNode->children()->scalarNode('name')->isRequired();
        $varsNode->children()->scalarNode('content')->isRequired();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    protected function buildAttachmentsSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $attachmentsNode */
        $attachmentsNode = $rootNode->children()->arrayNode('attachments')->prototype('array');
        $attachmentsNode->children()->scalarNode('type')->isRequired();
        $attachmentsNode->children()->scalarNode('name')->isRequired();
        $attachmentsNode->children()->scalarNode('content')->isRequired();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    protected function buildToSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $toNode */
        $toNode = $rootNode->children()->arrayNode('to')->prototype('array');

        $toNode->children()->scalarNode('email')->isRequired();
        $toNode->children()->scalarNode('name');

        $toTypeNode = $toNode->children()->scalarNode('type');
        $toTypeNode->defaultValue('to');
        $toTypeNode->validate()->ifNotInArray(array('to','cc','bcc'))->thenInvalid('Invalid "to" type %s');
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    protected function buildImagesSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $imagesNode */
        $imagesNode  = $rootNode->children()->arrayNode('images')->prototype('array');

        $imageTypeNode = $imagesNode->children()->scalarNode('type');
        $imageTypeNode->isRequired();

        $imageTypeNode->validate()
            ->ifTrue(function($value){
                return !preg_match('#^image/#', $value);
            })
            ->thenInvalid('Image type must being with "image/"');

        $imagesNode->children()->scalarNode('name')->isRequired();
        $imagesNode->children()->scalarNode('content')->isRequired();
    }

}