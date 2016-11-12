<?php
namespace Skinny\Utility;

class Text
{
    /**
     * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
     * $leftBound and $rightBound.
     *
     * @param string $data The data to tokenize.
     * @param string $separator The token to split the data on.
     * @param string $leftBound The left boundary to ignore separators in.
     * @param string $rightBound The right boundary to ignore separators in.
     *
     * @return array|string Array of tokens in $data or original input if empty.
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
        $length = mb_strlen($data);
        $open = false;

        while ($offset <= $length) {
            $tmpOffset = -1;
            $offsets = [
                mb_strpos($data, $separator, $offset),
                mb_strpos($data, $leftBound, $offset),
                mb_strpos($data, $rightBound, $offset)
            ];
            for ($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }
            if ($tmpOffset !== -1) {
                $buffer .= mb_substr($data, $offset, ($tmpOffset - $offset));
                $char = mb_substr($data, $tmpOffset, 1);
                if (!$depth && $char === $separator) {
                    $results[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $char;
                }
                if ($leftBound !== $rightBound) {
                    if ($char === $leftBound) {
                        $depth++;
                    }
                    if ($char === $rightBound) {
                        $depth--;
                    }
                } else {
                    if ($char === $leftBound) {
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
                $results[] = $buffer . mb_substr($data, $offset);
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
