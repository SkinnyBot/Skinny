<?php
namespace Skinny\Utility;

use InvalidArgumentException;

class Text
{
    /**
     * Get a valuie between 2 string in a string.
     *
     * @param string $string The string to search for.
     * @param string $left   Delimiter left.
     * @param string $right  Delimiter right.
     *
     * @return string
     */
    public static function getBetween($string, $left, $right)
    {
        $start = stripos($string, $left) + strlen($left);
        $length = stripos($string, $right, $start);

        return substr($string, $start, $length - $start);
    }

    /**
     * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
     * $leftBound and $rightBound.
     *
     * @param string $data The data to tokenize.
     * @param string $separator The token to split the data on.
     * @param string $leftBound The left boundary to ignore separators in.
     * @param string $rightBound The right boundary to ignore separators in.
     *
     * @return mixed Array of tokens in $data or original input if empty.
     */
    public static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')')
    {
        if (empty($data)) {
            return [];
        }

        $depth = 0;
        $offset = 0;
        $buffer = '';
        $results = [];
        $length = strlen($data);
        $open = false;

        while ($offset <= $length) {
            $tmpOffset = -1;
            $offsets = [
            strpos($data, $separator, $offset),
            strpos($data, $leftBound, $offset),
            strpos($data, $rightBound, $offset)
            ];
            for ($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }
            if ($tmpOffset !== -1) {
                $buffer .= substr($data, $offset, ($tmpOffset - $offset));
                if (!$depth && $data{$tmpOffset} === $separator) {
                    $results[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $data{$tmpOffset};
                }
                if ($leftBound !== $rightBound) {
                    if ($data{$tmpOffset} === $leftBound) {
                        $depth++;
                    }
                    if ($data{$tmpOffset} === $rightBound) {
                        $depth--;
                    }
                } else {
                    if ($data{$tmpOffset} === $leftBound) {
                        if (!$open) {
                            $depth++;
                            $open = true;
                        } else {
                            $depth--;
                        }
                    }
                }
                $offset = ++$tmpOffset;
            } else {
                $results[] = $buffer . substr($data, $offset);
                $offset = $length + 1;
            }
        }
        if (empty($results) && !empty($buffer)) {
            $results[] = $buffer;
        }

        if (!empty($results)) {
            return array_map('trim', $results);
        }

        return [];
    }
}
