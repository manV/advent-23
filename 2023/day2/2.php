<?php

if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

$game_strs = explode("\n", $str);

class Game {
  public readonly int $game_id;
  
  /** @var Revelation[][] $revelations */
  private array $revelations_sets = array();

  function __construct(string $game_str) {
    [$game_label_str, $revelation_sets_str] = explode(": ", $game_str);
    [,$game_id_str] = explode(" ", $game_label_str);
    // echo $game_label_str . PHP_EOL;
    $this->game_id = (int)$game_id_str;

    foreach(explode("; ", $revelation_sets_str) as $revelation_set_str) {
      $revelation_set = array();
      $revelation_strs = explode(", ", $revelation_set_str);
      foreach($revelation_strs as $revelation_str) {
        array_push($revelation_set, new Revelation($revelation_str));
      }
      array_push($this->revelations_sets, $revelation_set);
    }
  }

  public function get_power(): int {
    $max_counts = $this->get_max_counts();

    return $max_counts["red"] * $max_counts["green"] * $max_counts["blue"];
  }

  /** @return int[] $loaded_items */
  private function get_max_counts() : array  {
    $max_counts =  [
      "red" => 0,
      "green" => 0,
      "blue" => 0,
    ];

    foreach($this->revelations_sets as $revelation_set) {
      foreach($revelation_set as $revelation) {
        $max_counts[$revelation->color] = max($max_counts[$revelation->color], $revelation->count);
      }
    }

    return $max_counts;
  }
}

class Revelation {
  public readonly int $count;
  public readonly string $color;
  function __construct(string $revelation_str) {
    [$count, $color] = explode(" ", $revelation_str);
    $this->count = (int)$count;
    $this->color = $color;
  }
}


$sum = 0;

foreach($game_strs as $game_str) {
  $game = new Game($game_str);
  $sum += $game->get_power();
}

echo "Sum is: " . $sum . PHP_EOL;

?>