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

  /** @param int[] $loaded_items */
  public function is_game_possible(array $loaded_items): bool {
    $max_counts = $this->get_max_counts();

    foreach($loaded_items as $loaded_color => $loaded_count) {
      if ($max_counts[$loaded_color] > $loaded_count) {
        return false;
      }
    }

    return true;
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

$loaded_items = [
  "red" => 12,
  "green" => 13,
  "blue" => 14,
];

$sum = 0;

foreach($game_strs as $game_str) {
  $game = new Game($game_str);
  if ($game->is_game_possible($loaded_items)) {
    $sum += $game->game_id;
  }
}

echo "Sum is: " . $sum . PHP_EOL;

?>