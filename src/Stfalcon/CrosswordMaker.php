<?php

namespace Stfalcon;

class CrosswordMaker {

    /**
     * Основний метод генерації кросворду
     *
     * @param $words
     * @return bool|string
     */
    public function generate($words)
    {
        // сортуємо масив в порядку спадання довжини слів (щоб виконати умову лексикографічно меншого варіанту)
        usort($words, function($a, $b) {
            return (strlen($a) == strlen($b)) ? 0 : (strlen($a) < strlen($b)) ? 1 : -1;
        });

        // генеруємо всі можливі комбінації слів
        $combinations = self::_generateAllCombinations($words);

        // послідовно перебираємо варіанти і для кожного пробуємо згенерувати валідний кросворд
        foreach($combinations as $combination) {
            if ($crossword = $this->_tryGenerateCrossword($combination)) {
                return $crossword;
            }
        }

        return false;
    }

    /**
     * Рекурсивний метод генерації усіх можливих комбінацій з вхідного набору слів
     * (кількість комбінацій = факторіал кількості слів)
     *
     * @param $leftWords
     * @param array $rightWords
     * @param array $combinations
     * @return array
     */
    static private function _generateAllCombinations($leftWords, $rightWords = array(), &$combinations = array())
    {
        if (empty($leftWords)) {
            // якщо "зліва" слова закінчились, значить є нова комбінація
            $combinations[] = $rightWords;
        } else {
            for ($i = count($leftWords) - 1; $i >= 0; --$i) {
                $newLeftWords = $leftWords;
                $newRightWords = $rightWords;

                // переносимо одне з лівих слів в початок масиву правих слів
                array_unshift($newRightWords, $newLeftWords[$i]);
                // і прибираємо його з масиву лівих слів
                array_splice($newLeftWords, $i, 1);

                self::_generateAllCombinations($newLeftWords, $newRightWords, $combinations);
            }
        }

        return $combinations;
    }

    /**
     * Пробуємо генерувати кросворд у вигляді прямокутної вісімки
     *
     * @param $words
     * @return bool|string
     */
    private function _tryGenerateCrossword($words) {
        // напрямки руху по матриці (зміщення для кожного слов і напрямок тексту в словіа)
        $directions = array(
            array('x' => +1, 'y' => 0), // починаємо від [0,0] і йдемо вниз (ліве слово по вертикалі)
            array('x' => 0, 'y' => +1), // зліва направо (центральне слово по горизонталі)
            array('x' => +1, 'y' => 0), // зверху вниз (праве слово по вертикалі)
            array('x' => 0, 'y' => -1), // зправа наліво (нижнє слово по горизонталі)
            array('x' => -1, 'y' => 0), // знизу вверх (центральне слово по вертикалі)
            array('x' => 0, 'y' => -1), // зправа наліво (верхнє слово по горизонталі) і повертаємось в [0,0]
        );

        $matrix = array();
        $x=0;
        $y=0;
        $maxX = 0;
        $maxY = 0;

        for($wordId = 0; $wordId < count($words); $wordId++) {
            $word = $words[$wordId];
            for($i = 0; $i < strlen($word); $i++) {
                // якщо маршрут закінчився не там де почався (не в точці з координатами [0,0])
                if ($wordId + 1 == count($words) && $i + 1 == strlen($word) && ($x != 0 || $y != 0)) {
                    return false;
                }

                // для напрямків "зправа наліво" чи "знизу вверх" пишемо слова в зворотньому напрямку
                $reverseDirection = $directions[$wordId]['x'] < 0 || $directions[$wordId]['y'] < 0;
                // визначаємо номер літери, яку будемо прописувати в клітину (враховуємо напрямок тексту)
                $letterId = $reverseDirection ? strlen($word) - ($i+1) : $i;

                // якщо в цій клітинці вже є літера, то вони мають співпадати
                if (isset($matrix[$x][$y]) && $matrix[$x][$y] != $word[$letterId]) {
                    return false;
                }

                // проставляємо нову літеру в матрицю
                $matrix[$x][$y] = $word[$letterId];

                // рахуємо координати наступної літери
                if ($i < strlen($word) - 1) {
                    $x += $directions[$wordId]['x'];
                    $y += $directions[$wordId]['y'];

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
