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
 *
 * - token : The toekn used to connect to the discord server.
 * - admins : The list of bot's administrators. (IDs only)
 * - chatChannels : Restrict the bot to only listen to certain text channels. (IDs only)
 * - voiceChannel : Join a voice channel on startup. (IDs only)
 */
    'Bot' => [
        'token' => '',
        'admins' => [],
        'chatChannels' => [],
        'voiceChannel' => ''
    ],

/**
 * Configure Module manager.
 *
 * - priority - All modules that need to be loaded before others.
 */
    'Modules' => [
        'priority' => []
    ],

/**
 * Configure basic information about the the commands.
 *
 * - prefix - Prefix used with command.
 */
    'Commands' => [
        'prefix' => '!'
    ]
];
