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
  public int $card_number;
  public int $copies = 1;

  public function __construct(string $card_str)
  {
    [$card_id_str, $card_numbers_str] = explode(": ", $card_str);
    $card_id_arr = array_filter(explode(" ", $card_id_str), function($num_str) {
      return strlen($num_str) > 0;
    });

    $this->card_number = (int)end($card_id_arr);

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

class CardManager {
  // associative array of card number to cards
  public array $cards = array();

  public function add_card(Card $card) {
    $this->cards[$card->card_number] = $card;
  }

  public function process_cards() {
    foreach($this->cards as $card_number => $card) {
      $num_common = $card->num_common;
      if ($num_common <= 0) continue;
      foreach(range($card_number + 1, $card_number + $num_common) as $num) {
        $this->cards[$num]->copies += $card->copies;
      }
    }
  }

  public function get_total_copies(): int {
    return array_reduce($this->cards, function($carry, $card) {
      return $carry + $card->copies;
    }, 0);
  }
}

$card_manager = new CardManager();

foreach(explode("\n", $str) as $line) {
  $card = new Card($line);
  $card_manager->add_card($card);
}

$card_manager->process_cards();

echo $card_manager->get_total_copies() . PHP_EOL;

?>