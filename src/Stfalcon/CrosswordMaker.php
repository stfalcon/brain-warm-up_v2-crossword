<?php

namespace Stfalcon;

class CrosswordMaker {

    static private $_combinations = array();

    public function generate($words)
    {
        self::$_combinations = array();

        usort($words, function($a, $b) {
            if (strlen($a) == strlen($b)) {
                return 0;
            }
            return (strlen($a) < strlen($b)) ? 1 : -1;
        });

        self::_bruteForce($words);

        foreach(self::$_combinations as $combination) {
            $crossword = self::_tryGenerate($combination);
            if ($crossword) {
                return $crossword;
            }
        }

        return false;
    }

    static private function _bruteForce($data, $pos = 0) {
        if (count($data) == $pos-1) {
            return false;
        }

        array_push(self::$_combinations, $data);

        for($i = $pos; $i < count($data); $i++) {
            $dataCopy = $data;
            if (isset($data[$i+1])) {
                $dataCopy[$pos] = $data[$i+1];
                $dataCopy[$i+1] = $data[$pos];
            }

            self::_bruteForce($dataCopy, $pos+1);
        }
    }

    static private function _tryGenerate($words) {
        // напрямок руху по матриці (зміщення для кожного слова)
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

        for($wordId = 0; $wordId < count($words); $wordId++) {
            $word = $words[$wordId];
            for($letterId = 0; $letterId < strlen($word); $letterId++) {
                // визначаємо номер літери, яку будемо прописувати в клітину (враховуємо зворотні напрямки)
                $n = ($direction[$wordId][0] < 0 || $direction[$wordId][1] < 0) ? strlen($word) - ($letterId+1) : $letterId;

                // якщо маршрут закінчився не там де почався (не в точці з координатами [0][0])
                if ($wordId + 1 == count($words) && $letterId + 1 == strlen($word) && ($x != 0 || $y != 0)) {
                    return false;
                }

                // якщо в цій клітинці вже є літера, то вони мають співпадати
                if (isset($matrix[$x][$y]) && $matrix[$x][$y] != $word[$n]) {
                    return false;
                }

                // проставляємо нову літеру в матрицю
                $matrix[$x][$y] = $word[$n];

                // рахуємо координати наступної літери
                if ($letterId < strlen($word) - 1) {
                    $x += $direction[$wordId][0];
                    $y += $direction[$wordId][1];

                    // фіксуємо розміри матриці
                    $maxX = $x > $maxX ? $x : $maxX;
                    $maxY = $y > $maxY ? $y: $maxY;
                }
            }
        }

        // рендеримо кросворд
        ob_start();
        for ($x = 0; $x <= $maxX; $x++) {
            for ($y = 0; $y <= $maxY; $y++) {
                    echo isset($matrix[$x][$y]) ? $matrix[$x][$y] : '.';
            }
            echo "\n";
        }

        $crossword = ob_get_contents();
        ob_end_clean();

        // забираємо останній "\n"
        return substr($crossword, 0, -1);
    }

}
