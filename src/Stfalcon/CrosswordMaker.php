<?php

namespace Stfalcon;

class CrosswordMaker {

    public function generate($words)
    {
        // шукаємо довгі і короткі слова
        list($longestWords, $shortestWords) = $this->_findLongestAndShortestWords($words);

        $verticalWord = $longestWords[0];
        $horizontalWord = $longestWords[1];

        // шукаємо точки перетину довгих слів
        $crossingPositions = $this->_findLongestWordsCrossingPositions($horizontalWord, $verticalWord);
        // якщо немає перетинів, тоді немає кросворду
        if (count($crossingPositions) < 1) {
            return false;
        }

        // пробуємо згенерувати кросворд для різних варіантів перетину
        foreach($crossingPositions as $crossingPosition) {
            return $this->_generateCrossword($shortestWords, $horizontalWord, $verticalWord, $crossingPosition);
        }

        return false;
    }

    /**
     * Розбиваємо слова на два масиви (longestWords — "центральні" слова, shortestWords — решта слів)
     *
     * @param $words
     * @return array
     */
    private function _findLongestAndShortestWords($words)
    {
        // шукаємо максимальну довжину слова
        $maxWordLenght = strlen($words[0]);
        foreach($words as $word) {
            $maxWordLenght = strlen($word) > $maxWordLenght ? strlen($word) : $maxWordLenght;
        }

        // шукаємо найдовші слова, що підходять для центру кросворду
        $longestWords = array();
        $shortestWords = array();
        foreach($words as $word) {
            if (strlen($word) > $maxWordLenght - 2) {
                $longestWords[] = $word;
            } else {
                $shortestWords[] = $word;
            }
        }

        // може бути тільки два найдовших слова
        if (count($longestWords) > 2) {
            return false;
        }

        return array($longestWords, $shortestWords);
    }

    /**
     * Шукаємо точки перетину найдовших слів
     *
     * @param $horizontalWord
     * @param $verticalWord
     * @return array
     */
    private function _findLongestWordsCrossingPositions($horizontalWord, $verticalWord)
    {
        // відступаємо на два символи від початку і кінця слова
        $positions = array();
        for ($x = 2; $x < strlen($horizontalWord)-2; $x++) {
            $symbolFromHorizontalWord = $horizontalWord[$x];
            for ($y = 2; $y < strlen($verticalWord) - 2; $y++) {
                $symbolFromVerticalWord = $verticalWord[$y];
                if ($symbolFromVerticalWord == $symbolFromHorizontalWord) {
                    $positions[] = array('x' => $x, 'y' => $y);
                }
            }
        }

        return $positions;
    }

    private function _generateCrossword($shortestWords, $horizontalWord, $verticalWord, $crossingPosition)
    {
        // розмір матриці
        $maxX = strlen($horizontalWord) - 1;
        $maxY = strlen($verticalWord) - 1;

        $crossX = $crossingPosition['x'];
        $crossY = $crossingPosition['y'];

        list($topWords, $rightWords, $buttomWords, $leftWords) = $this->_findTopAndRightAndBottomAndLeftWords($shortestWords, $horizontalWord, $verticalWord, $crossX, $crossY, $maxX, $maxY);

        // генеруємо кроссворд
        foreach($topWords as $topWord) {
            foreach($leftWords as $leftWord) {
                foreach($rightWords as $rightWord) {
                    foreach($buttomWords as $buttomWord) {
                        // заповняємо матрицю точками
                        $crosswordData = array();
                        for($x = 0; $x <= $maxX; $x++) {
                            for($y = 0; $y <= $maxY; $y++) {
                                $crosswordData[$x][$y] = '.';
                            }
                        }

                        // верхнє слово по горизонталі
                        for($x = 0; $x < strlen($topWord); $x++) {
                            $crosswordData[$x][0] = $topWord[$x];
                        }

                        // довге слово по горизонталі
                        for($x = 0; $x < strlen($horizontalWord); $x++) {
                            $crosswordData[$x][$crossY] = $horizontalWord[$x];
                        }

                        // нижнє слово по горизонталі
                        for($x = 0; $x < strlen($buttomWord); $x++) {
                            $crosswordData[$x + $crossX][$maxY] = $buttomWord[$x];
                        }

                        // ліве слово по вертикалі
                        for($y = 0; $y < strlen($leftWord); $y++) {
                            $crosswordData[0][$y] = $leftWord[$y];
                        }

                        // довге слово по вертикалі
                        for($y = 0; $y < strlen($verticalWord); $y++) {
                            $crosswordData[$crossX][$y] = $verticalWord[$y];
                        }

                        // праве слово по вертикалі
                        for($y = 0; $y < strlen($rightWord); $y++) {
                            $crosswordData[$maxX][$y + $crossY] = $rightWord[$y];
                        }

                        // виводимо кросворд
                        $crosswordText = "";
                        for($y = 0; $y <= $maxY; $y++) {
                            for($x = 0; $x <= $maxX; $x++) {
                                $crosswordText .= $crosswordData[$x][$y];
                            }
                            $crosswordText .= "\n";
                        }

                        return substr($crosswordText, 0, -1);
                    }
                }
            }
        }
    }

    /**
     * Підбір слів на відповідні позиції
     *
     */
    private function _findTopAndRightAndBottomAndLeftWords($shortestWords, $horizontalWord, $verticalWord, $crossX, $crossY, $maxX, $maxY)
    {
        $topWords = array();
        $leftWords = array();
        $rightWords = array();
        $buttomWords = array();

        foreach($shortestWords as $word) {
            $wordLength = strlen($word);
            $wordFirstChar = $word[0];
            $wordLastChar = substr($word, -1);

            $topWordLength = $crossX + 1;
            $verticalWordFirstChar = $verticalWord[0];
            if ($wordLength == $topWordLength && $wordLastChar == $verticalWordFirstChar) {
                $topWords[] = $word;
            }

            $rightWordLength = $maxY - $crossY + 1;
            $horizontalWordLastChar = substr($horizontalWord, -1);
            if ($wordLength == $rightWordLength && $wordFirstChar == $horizontalWordLastChar) {
                $rightWords[] = $word;
            }

            $buttomWordLength = $maxX - $crossX + 1;
            $verticalWordLastChar = substr($verticalWord, -1);
            if ($wordLength == $buttomWordLength && $wordFirstChar == $verticalWordLastChar) {
                $buttomWords[] = $word;
            }

            $leftWordLength = $crossY + 1;
            $horizontalWordFirstChar = $horizontalWord[0];
            if ($wordLength == $leftWordLength && $wordLastChar == $horizontalWordFirstChar) {
                $leftWords[] = $word;
            }
        }

        return array($topWords, $rightWords, $buttomWords, $leftWords);
    }

}
