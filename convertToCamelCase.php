<?php

function convertToCamelCase(string $string):string {
    // Удаляем лишние пробелы
    $string = trim($string);

    // Разбиваем строку на слова
    $words = explode(' ', $string);

    // Преобразуем каждое слово к формату UpperCamelCase
    $words = array_map('ucfirst', $words);

    // Объединяем слова в одну строку
    return implode('', $words);
}