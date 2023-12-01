<?php

if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

$num_word_strs = ["one", "two", "three", "four", "five", "six", "seven", "eight", "nine"];
$num_strs = ["1", "2", "3", "4", "5", "6", "7", "8", "9"];

function getCaliberationDigit(string $line): int {
  global $num_word_strs;
  global $num_strs;
  $first_digit = '';
  $last_digit = '';

  for ($i = 0; $i <= strlen($line) - 1; $i++) {
    if (strlen($first_digit) !== 0) break;
    if (is_numeric($line[$i])) {
      $first_digit = $line[$i];
    } else {
      foreach ($num_word_strs as $index => $word) {
        if (str_starts_with(substr($line, $i), $word)) {
          $first_digit = $num_strs[$index];
          break;
        }
      }
    }
  }

  for ($j = strlen($line) - 1; $j >= 0; $j--) {
    if (strlen($last_digit) !== 0) break;
    if (is_numeric($line[$j])) {
      $last_digit = $line[$j];
    } else {
      foreach ($num_word_strs as $index => $word) {
        if (str_ends_with(substr($line, 0, $j + 1), $word)) {
          $last_digit = $num_strs[$index];
          break;
        }
      }
    }
  }

  return (int)($first_digit . $last_digit);
}

$nums = array_map(fn($value) => getCaliberationDigit($value), explode("\n", $str));

echo array_reduce($nums, fn($carry, $item) => $carry + $item, 0) . PHP_EOL;

?>