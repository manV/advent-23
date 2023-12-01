<?php

if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

function getCaliberationDigit(string $line): int {
  $first_digit = '';
  $last_digit = '';


  for ($i = 0; $i <= strlen($line) - 1; $i++) {
    if (strlen($first_digit) === 0 && is_numeric($line[$i])) {
      $first_digit = $line[$i];
      break;
    }
  }

  for ($j = strlen($line) - 1; $j >= 0; $j--) {
    if (strlen($last_digit) === 0 && is_numeric($line[$j])) {
      $last_digit = $line[$j];
      break;
    }
  }

  return (int)($first_digit . $last_digit);
}

$nums = array_map(fn($value) => getCaliberationDigit($value), explode("\n", $str));

echo array_reduce($nums, fn($carry, $item) => $carry + $item, 0) . PHP_EOL;

?>