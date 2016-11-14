<?php
namespace Skinny\Message;

/**
 * @filesource
 */
class Message
{
    use StaticMessageTrait;

    /**
     * Parse the message and return an array with informations about the message.
     *
     * ### Usage
     *```
     * Message::parse('!say hi');
     *```
     * Wil return this array:
     *```
     * [
     *     'raw' => '!say hi',
     *     'parts' => [
     *          (int) 0 => '!say',
     *          (int) 1 => 'hi'
     *     ],
     *     'command' => 'say',
     *     'message' => 'hi',
     *     'commandCode' => '!',
     *     'arguments' => [
     *         (int) 0 => 'hi'
     *     ]
     * ]
     *
     *```
     *
     * @param string $message The message to parse
     *
     * @return array The data.
     */
    public static function parse($message)
    {
        static::resetConfig();

        $chr32 = chr(32);
        $config = [];

        if (empty($message) || !is_string($message)) {
            return static::read();
        }

        $config += [
            'raw' => $message,
            'parts' => explode($chr32, trim($message), 2)
        ];

        $config += [
            'commandCode' => substr($config['parts'][0], 0, 1),
            'command' => strtolower(substr($config['parts'][0], 1))
        ];

        if (count($config['parts']) > 1) {
            $config += [
                'message' => $config['parts'][1],
                'arguments' => array_map(
                    'strtolower',
                    explode(
                        $chr32,
                        $config['parts'][1]
                    )
                )
            ];
        }
        static::config($config);

        return static::read();
    }
}
