<?php
use Skinny\Core\Configure;
use Skinny\Error\Debugger;

if (!function_exists('debug')) {
    /**
     * Prints out debug information about given variable.
     *
     * Only runs if debug level is greater than zero.
     *
     * @param mixed $var Variable to show debug information for.
     * @param bool $discord Return the generated content.
     * @param string $type The type of content.
     *
     * @return void
     */
    function debug($var, $discord = false, $type = 'php')
    {
        if (!Configure::read('debug')) {
            return;
        }

        $trace = Debugger::trace(['start' => 1, 'depth' => 2, 'format' => 'array']);
        $search = [ROOT];

        $file = str_replace($search, '', $trace[0]['file']);
        $line = $trace[0]['line'];
        $lineInfo = sprintf('%s (line %s)', $file, $line);

        $template = <<<TEXT
%s
########## DEBUG ##########
%s
###########################
%s
TEXT;

        $discordTemplate = <<<TEXT
**########## DEBUG ##########**
`%s`
```%s
%s
```
%s
TEXT;
        $var = Debugger::exportVar($var, 25);

        if ($discord === true) {
            return sprintf($discordTemplate, $lineInfo, $type, $var, PHP_EOL);
        }

        printf($template, $lineInfo, $var, PHP_EOL);
    }
}


if (!function_exists('pluginSplit')) {
    /**
     * Splits a dot syntax plugin name into its plugin and class name.
     * If $name does not have a dot, then index 0 will be null.
     *
     * Commonly used like
     * ```
     * list($plugin, $name) = pluginSplit($name);
     * ```
     *
     * @param string $name The name you want to plugin split.
     * @param bool $dotAppend Set to true if you want the plugin to have a '.' appended to it.
     * @param string|null $plugin Optional default plugin to use if no plugin is found. Defaults to null.
     *
     * @return array Array with 2 indexes. 0 => plugin name, 1 => class name.
     */
    function pluginSplit($name, $dotAppend = false, $plugin = null)
    {
        if (strpos($name, '.') !== false) {
            $parts = explode('.', $name, 2);
            if ($dotAppend) {
                $parts[0] .= '.';
            }

            return $parts;
        }

        return [$plugin, $name];
    }
}
