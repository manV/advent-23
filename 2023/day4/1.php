<?php

if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

class Card
{
  public array $my_numbers = array();
  public array $winning_numbers = array();
  public int $num_common = 0;

  public function __construct(string $card_str)
  {
    [$card_id_str, $card_numbers_str] = explode(": ", $card_str);

    [$winning_numbers_str, $my_numbers_str] = explode(" | ", $card_numbers_str);
    $this->winning_numbers = array_filter(explode(" ", $winning_numbers_str), function($num_str) {
      return strlen($num_str) > 0;
    });
    $this->my_numbers = array_filter(explode(" ", $my_numbers_str), function($num_str) {
      return strlen($num_str) > 0;
    });

    $this->num_common = count(array_intersect($this->winning_numbers, $this->my_numbers));
  }
}

$sum = array_reduce(explode("\n", $str), function ($carry, $line) {
  $card = new Card($line);
  return $carry += ($card->num_common > 0 ? pow(2, $card->num_common - 1) : 0);
}, 0);

echo $sum . PHP_EOL

?>