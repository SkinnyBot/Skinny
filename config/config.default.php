<?php
return [
/**
 * Debug Level:
 *
 * Production Mode:
 * false: No error messages, errors, or warnings shown.
 *
 * Development Mode:
 * true: Errors and warnings shown.
 */
    'debug' => true,

/**
 * Configure basic information about the application.
 *
 * - namespace - The namespace to find app classes under.
 */
    'App' => [
        'namespace' => 'Bot',
    ],

/**
 * Configure basic information about the bot.
 */
    'Bot' => [
        'token' => '',

        //Admins of the bot. (IDs only)
        'admins' => []

        //Restrict the bot to only listen to certain text channels. (IDs only)
        'chatChannels' => [],

        //Join a voice channel on startup. (IDs only)
        'voiceChannel' => ''
    ],

/**
 * Configure basic information about the the commands.
 *
 * - prefix - Prefix used with command.
 */
    'Commands' => [
        'prefix' => '!'
    ],

/**
 * Configure information about Pastebin.
 */
    'Pastebin' => [
        'apiDevKey' => 'zz',
        'apiPastePrivate' => '1',
        'apiPasteExpireDate' => '1M'
    ]
];
