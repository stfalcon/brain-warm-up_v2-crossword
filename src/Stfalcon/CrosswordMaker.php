<?php

namespace Stfalcon;

class CrosswordMaker {

    public function generate($words)
    {
//        var_dump(self::_bruteForce($words));
//        exit;
        return self::_bruteForce($words);
    }

    static private function _bruteForce($words, $pos = 0) {
        if (count($words) == $pos-1) {
            return false;
        }

        // пробуємо згенерувати кросворд для поточної комбінації
        $crossword = self::_tryGenerate($words);
        if ($crossword) {
            return $crossword;
        }

        for($i = $pos; $i < count($words); $i++) {
            $data = $words;
            if (isset($words[$i+1])) {
                $data[$pos] = $words[$i+1];
                $data[$i+1] = $words[$pos];
            }

            return self::_bruteForce($data, $pos+1);
        }
    }

    static private function _tryGenerate($words) {
//        echo implode($words,' ') . "\n";

        $direction = array(
            array(+1, 0),
            array(0, +1),
            array(+1, 0),
            array(0, -1),
            array(-1, 0),
            array(0, -1),
        );

        $matrix = array();
        $x=0;
        $y=0;
        $maxX = 0;
        $maxY = 0;
        for($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            for($n = 0; $n < strlen($word); $n++) {
                $num = ($direction[$i][0] < 0 || $direction[$i][1] < 0) ? strlen($word) - ($n+1) : $n;

                if (isset($matrix[$x][$y]) && $matrix[$x][$y] != $word[$num]) {
                    return false;
                }

                $matrix[$x][$y] = $word[$num];
                if ($n < strlen($word) - 1) {
                    $x += $direction[$i][0];
                    $maxX = $x > $maxX ? $x : $maxX;

                    $y += $direction[$i][1];
                    $maxY = $y > $maxY ? $y: $maxY;
                }
            }
        }

        ob_start();
        for ($x = 0; $x <= $maxX; $x++) {
            for ($y = 0; $y <= $maxY; $y++) {
                if (isset($matrix[$x][$y])) {
                    echo $matrix[$x][$y];
                } else {
                    echo '.';
                }
            }
            echo "\n";
        }

        $crossword = ob_get_contents();
        ob_end_clean();

        return substr($crossword, 0, -1);
    }

}
