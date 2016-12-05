<?php

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => '[{$scaffold->id}]',
    'title'       => '[{$scaffold->vendor|upper}] :: [{$scaffold->title}]',
    'description' => '',
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '0.0.1-DEV',
    'author'      => '[{$scaffold->author}]',
    'url'         => '[{$scaffold->url}]',
    'email'       => '[{$scaffold->email}]',
    'extend'      => array(
        // '<oxclass>'   => '<vendor/module/path_to_file>',
    ),
    'files'       => array(
        '[{$scaffold->vendor}][{$scaffold->name|lower}]events' => '[{if $scaffold->vendor}][{$scaffold->vendor}]/[{/if}][{$scaffold->name|lower}]/core/[{$scaffold->vendor}][{$scaffold->name|lower}]events.php',
    ),
    'templates'   => array(
        // '<template.tpl>' => '<vendor/module/path_to_template.tpl>',
    ),
    'blocks'      => array(
        // array(
        //     'template' => '<path_to_core_template.tpl>',
        //     'block'    => '<block_name>',
        //     'file'     => '<views/block/my_block.tpl>'
        // ),
    ),
    'settings'    => array(),
    'events'      => array(
        'onActivate'   => '[{$scaffold->vendor}][{$scaffold->name}]Events::onActivate',
        'onDeactivate' => '[{$scaffold->vendor}][{$scaffold->name}]Events::onDeactivate',
    ),
);
