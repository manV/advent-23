<?php
if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

class Engine {
  /** @var string[][] $schematic_matrix */
  private array $schematic_matrix = array();

  /** @var array<string, int[]> $parts_numbers */
  public array $gears = array();

  function __construct(public string $schematic_str) {
    $schematic_line_strs = explode("\n", $this->schematic_str);
    foreach($schematic_line_strs as $schematic_line_str) {
      $this->schematic_matrix[] = str_split($schematic_line_str);
    }
    $this->parse_gears();
  }

  private function parse_gears() {
    foreach($this->schematic_matrix as $schematic_line_num => $schematic_line) {
      $part_number = "";
      $adj_gears = array();
      foreach($schematic_line as $schematic_index => $schematic_char) {
        if (is_numeric($schematic_char)) {
          $part_number .= $schematic_char;
          foreach($this->schematic_char_get_adj_gears($schematic_line_num, $schematic_index) as $adj_gear) {
            if (!in_array($adj_gear, $adj_gears)) $adj_gears[] = $adj_gear;
          }
          
          if (!isset($schematic_line[$schematic_index + 1]) || !is_numeric($schematic_line[$schematic_index + 1])) {
            foreach($adj_gears as $adj_gear) {
              if (!isset($this->gears[$adj_gear])) $this->gears[$adj_gear] = array();
              $this->gears[$adj_gear][] = (int)$part_number;
            }
            $part_number = "";
            $adj_gears = array();
          }
        }
      }
    }
  }

  private function get_gear_hash(int $line, int $index): string {
    return $line . "-" . $index;
  }


  private function schematic_char_get_adj_gears(int $line_num, int $index): array {
    $gears = array();
    if (isset($this->schematic_matrix[$line_num - 1]) && isset($this->schematic_matrix[$line_num - 1][$index - 1]) && char_is_gear($this->schematic_matrix[$line_num - 1][$index - 1])) {
      $gears[] = $this->get_gear_hash($line_num - 1, $index - 1);
    }
    if (isset($this->schematic_matrix[$line_num - 1]) && isset($this->schematic_matrix[$line_num - 1][$index]) && char_is_gear($this->schematic_matrix[$line_num - 1][$index])) {
      $gears[] = $this->get_gear_hash($line_num - 1, $index);
    }
    if (isset($this->schematic_matrix[$line_num - 1]) && isset($this->schematic_matrix[$line_num - 1][$index + 1]) && char_is_gear($this->schematic_matrix[$line_num - 1][$index + 1])) {
      $gears[] = $this->get_gear_hash($line_num - 1, $index + 1);
    }
    if (isset($this->schematic_matrix[$line_num]) && isset($this->schematic_matrix[$line_num][$index - 1]) && char_is_gear($this->schematic_matrix[$line_num][$index - 1])) {
      $gears[] = $this->get_gear_hash($line_num, $index - 1);
    }
    if (isset($this->schematic_matrix[$line_num]) && isset($this->schematic_matrix[$line_num][$index + 1]) && char_is_gear($this->schematic_matrix[$line_num][$index + 1])) {
      $gears[] = $this->get_gear_hash($line_num, $index + 1);
    }
    if (isset($this->schematic_matrix[$line_num + 1]) && isset($this->schematic_matrix[$line_num + 1][$index - 1]) && char_is_gear($this->schematic_matrix[$line_num + 1][$index - 1])) {
      $gears[] = $this->get_gear_hash($line_num + 1, $index - 1);
    }
    if (isset($this->schematic_matrix[$line_num + 1]) && isset($this->schematic_matrix[$line_num + 1][$index]) && char_is_gear($this->schematic_matrix[$line_num + 1][$index])) {
      $gears[] = $this->get_gear_hash($line_num + 1, $index);
    }
    if (isset($this->schematic_matrix[$line_num + 1]) && isset($this->schematic_matrix[$line_num + 1][$index + 1]) && char_is_gear($this->schematic_matrix[$line_num + 1][$index + 1])) {
      $gears[] = $this->get_gear_hash($line_num + 1, $index + 1);
    }

    return $gears;
  }

}

function char_is_gear(string $char) {
  return $char === "*";
}

$engine = new Engine($str);

$sum = 0;

foreach($engine->gears as $gear) {
  if (count($gear) === 2) {
    $sum += ($gear[0] * $gear[1]);
  }
}

echo $sum . PHP_EOL;

?>