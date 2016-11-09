<?php
namespace Bot\Utility;

use Bot\Utility\Text;

class Hash
{
    /**
     * Insert $values into an array with the given $path. You can use
     * `{n}` and `{s}` elements to insert $data multiple times.
     *
     * @param array $data The data to insert into.
     * @param string $path The path to insert at.
     * @param array|null $values The values to insert.
     *
     * @return array The data with $values inserted.
     */
    public static function insert(array $data, $path, $values = null)
    {
        $noTokens = strpos($path, '[') === false;
        if ($noTokens && strpos($path, '.') === false) {
            $data[$path] = $values;

            return $data;
        }

        if ($noTokens) {
            $tokens = explode('.', $path);
        } else {
            $tokens = Text::tokenize($path, '.', '[', ']');
        }

        if ($noTokens && strpos($path, '{') === false) {
            return static::_simpleOp('insert', $data, $tokens, $values);
        }

        $token = array_shift($tokens);
        $nextPath = implode('.', $tokens);

        list($token, $conditions) = static::_splitConditions($token);

        foreach ($data as $k => $v) {
            if (static::_matchToken($k, $token)) {
                if ($conditions && static::_matches($v, $conditions)) {
                    $data[$k] = array_merge($v, $values);
                    continue;
                }
                if (!$conditions) {
                    $data[$k] = static::insert($v, $nextPath, $values);
                }
            }
        }

        return $data;
    }

    /**
     * Get a single value specified by $path out of $data.
     * Does not support the full dot notation feature set,
     * but is faster for simple read operations.
     *
     * @param array $data Array of data to operate on.
     * @param string|array $path The path being searched for. Either a dot
     *   separated string, or an array of path segments.
     * @param mixed $default The return value when the path does not exist
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed The value fetched from the array, or null.
     */
    public static function get(array $data, $path, $default = null)
    {
        if (empty($data)) {
            return $default;
        }

        if (is_string($path) || is_numeric($path)) {
            $parts = explode('.', $path);
        } else {
            if (!is_array($path)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid Parameter %s, should be dot separated path or array.',
                    $path
                ));
            }

            $parts = $path;
        }

        switch (count($parts)) {
            case 1:
                return isset($data[$parts[0]]) ? $data[$parts[0]] : $default;
            case 2:
                return isset($data[$parts[0]][$parts[1]]) ? $data[$parts[0]][$parts[1]] : $default;
            case 3:
                return isset($data[$parts[0]][$parts[1]][$parts[2]]) ? $data[$parts[0]][$parts[1]][$parts[2]] : $default;
            default:
                foreach ($parts as $key) {
                    if (is_array($data) && isset($data[$key])) {
                        $data = $data[$key];
                    } else {
                        return $default;
                    }
                }
        }

        return $data;
    }

    /**
     * Remove data matching $path from the $data array.
     * You can use `{n}` and `{s}` to remove multiple elements
     * from $data.
     *
     * @param array $data The data to operate on.
     * @param string $path A path expression to use to remove.
     * @return array The modified array.
     */
    public static function remove(array $data, $path)
    {
        $noTokens = strpos($path, '[') === false;
        $noExpansion = strpos($path, '{') === false;

        if ($noExpansion && $noTokens && strpos($path, '.') === false) {
            unset($data[$path]);

            return $data;
        }

        $tokens = $noTokens ? explode('.', $path) : Text::tokenize($path, '.', '[', ']');

        if ($noExpansion && $noTokens) {
            return static::_simpleOp('remove', $data, $tokens);
        }

        $token = array_shift($tokens);
        $nextPath = implode('.', $tokens);

        list($token, $conditions) = self::_splitConditions($token);

        foreach ($data as $k => $v) {
            $match = static::_matchToken($k, $token);
            if ($match && is_array($v)) {
                if ($conditions && static::_matches($v, $conditions)) {
                    unset($data[$k]);
                    continue;
                }
                $data[$k] = static::remove($v, $nextPath);
                if (empty($data[$k])) {
                    unset($data[$k]);
                }
            } elseif ($match && empty($nextPath)) {
                unset($data[$k]);
            }
        }

        return $data;
    }

    /**
     * Expands a flat array to a nested array.
     *
     * For example, unflattens an array that was collapsed with `Hash::flatten()`
     * into a multi-dimensional array. So, `['0.Foo.Bar' => 'Far']` becomes
     * `[['Foo' => ['Bar' => 'Far']]]`.
     *
     * @param array $data Flattened array.
     * @param string $separator The delimiter used.
     *
     * @return array
     */
    public static function expand(array $data, $separator = '.')
    {
        $result = [];
        foreach ($data as $flat => $value) {
            $keys = explode($separator, $flat);
            $keys = array_reverse($keys);
            $child = [
            $keys[0] => $value
            ];
            array_shift($keys);
            foreach ($keys as $k) {
                $child = [
                $k => $child
                ];
            }

            $stack = [[$child, &$result]];
            static::_merge($stack, $result);
        }

        return $result;
    }

    /**
     * This function can be thought of as a hybrid between PHP's `array_merge` and `array_merge_recursive`.
     *
     * The difference between this method and the built-in ones, is that if an array key contains another array, then
     * Hash::merge() will behave in a recursive fashion (unlike `array_merge`). But it will not act recursively for
     * keys that contain scalar values (unlike `array_merge_recursive`).
     *
     * Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into arrays.
     *
     * @param array $data Array to be merged.
     * @param mixed $merge Array to merge with. The argument and all trailing arguments will be array cast when merged.
     *
     * @return array Merged array
     */
    public static function merge(array $data, $merge)
    {
        $args = array_slice(func_get_args(), 1);
        $return = $data;

        foreach ($args as &$curArg) {
            $stack[] = [(array)$curArg, &$return];
        }
        unset($curArg);
        static::_merge($stack, $return);

        return $return;
    }

    /**
     * Split token conditions.
     *
     * @param string $token the token being splitted.
     *
     * @return array [token, conditions] with token splitted
     */
    protected static function _splitConditions($token)
    {
        $conditions = false;
        $position = strpos($token, '[');
        if ($position !== false) {
            $conditions = substr($token, $position);
            $token = substr($token, 0, $position);
        }

        return [$token, $conditions];
    }

    /**
     * Check a key against a token.
     *
     * @param string $key The key in the array being searched.
     * @param string $token The token being matched.
     *
     * @return bool
     */
    protected static function _matchToken($key, $token)
    {
        if ($token === '{n}') {
            return is_numeric($key);
        }
        if ($token === '{s}') {
            return is_string($key);
        }
        if (is_numeric($token)) {
            return ($key == $token);
        }

        return ($key === $token);
    }

    /**
     * Checks whether or not $data matches the attribute patterns.
     *
     * @param array $data Array of data to match.
     * @param string $selector The patterns to match.
     * @return bool Fitness of expression.
     */
    protected static function _matches(array $data, $selector)
    {
        preg_match_all(
            '/(\[ (?P<attr>[^=><!]+?) (\s* (?P<op>[><!]?[=]|[><]) \s* (?P<val>(?:\/.*?\/ | [^\]]+)) )? \])/x',
            $selector,
            $conditions,
            PREG_SET_ORDER
        );

        foreach ($conditions as $cond) {
            $attr = $cond['attr'];
            $op = isset($cond['op']) ? $cond['op'] : null;
            $val = isset($cond['val']) ? $cond['val'] : null;

            // Presence test.
            if (empty($op) && empty($val) && !isset($data[$attr])) {
                return false;
            }

            // Empty attribute = fail.
            if (!(isset($data[$attr]) || array_key_exists($attr, $data))) {
                return false;
            }

            $prop = null;
            if (isset($data[$attr])) {
                $prop = $data[$attr];
            }
            $isBool = is_bool($prop);
            if ($isBool && is_numeric($val)) {
                $prop = $prop ? '1' : '0';
            } elseif ($isBool) {
                $prop = $prop ? 'true' : 'false';
            }

            // Pattern matches and other operators.
            if ($op === '=' && $val && $val[0] === '/') {
                if (!preg_match($val, $prop)) {
                    return false;
                }
            } elseif (($op === '=' && $prop != $val) ||
            ($op === '!=' && $prop == $val) ||
            ($op === '>' && $prop <= $val) ||
            ($op === '<' && $prop >= $val) ||
            ($op === '>=' && $prop < $val) ||
            ($op === '<=' && $prop > $val)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Perform a simple insert/remove operation.
     *
     * @param string $op The operation to do.
     * @param array $data The data to operate on.
     * @param array $path The path to work on.
     * @param mixed $values The values to insert when doing inserts.
     *
     * @return array data.
     */
    protected static function _simpleOp($op, $data, $path, $values = null)
    {
        $_list =& $data;

        $count = count($path);
        $last = $count - 1;
        foreach ($path as $i => $key) {
            if ((is_numeric($key) && (int)($key) > 0 || $key === '0') &&
            strpos($key, '0') !== 0
            ) {
                $key = (int)$key;
            }
            if ($op === 'insert') {
                if ($i === $last) {
                    $_list[$key] = $values;

                    return $data;
                }
                if (!isset($_list[$key])) {
                    $_list[$key] = [];
                }
                $_list =& $_list[$key];
                if (!is_array($_list)) {
                    $_list = [];
                }
            } elseif ($op === 'remove') {
                if ($i === $last) {
                    unset($_list[$key]);

                    return $data;
                }
                if (!isset($_list[$key])) {
                    return $data;
                }
                $_list =& $_list[$key];
            }
        }
    }

    /**
     * Merge helper function to reduce duplicated code between merge() and expand().
     *
     * @param array $stack The stack of operations to work with.
     * @param array $return The return value to operate on.
     *
     * @return void
     */
    protected static function _merge($stack, &$return)
    {
        while (!empty($stack)) {
            foreach ($stack as $curKey => &$curMerge) {
                foreach ($curMerge[0] as $key => &$val) {
                    if (!empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                        $stack[] = [&$val, &$curMerge[1][$key]];
                    } elseif ((int)$key === $key && isset($curMerge[1][$key])) {
                        $curMerge[1][] = $val;
                    } else {
                        $curMerge[1][$key] = $val;
                    }
                }
                unset($stack[$curKey]);
            }
            unset($curMerge);
        }
    }
}
