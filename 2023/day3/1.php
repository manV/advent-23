<?php

if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

class Engine {
  /** @var string[][] $schematic_matrix */
  private array $schematic_matrix = array();

  /** @var int[] $parts_numbers */
  public array $part_numbers = array();

  function __construct(public string $schematic_str) {
    $schematic_line_strs = explode("\n", $this->schematic_str);
    foreach($schematic_line_strs as $schematic_line_str) {
      $this->schematic_matrix[] = str_split($schematic_line_str);
    }
    $this->parse_part_numbers();
  }

  private function parse_part_numbers() {
    foreach($this->schematic_matrix as $schematic_line_num => $schematic_line) {
      $part_number = "";
      $has_adj_symbol = false;
      foreach($schematic_line as $schematic_index => $schematic_char) {
        if (is_numeric($schematic_char)) {
          $part_number .= $schematic_char;
          $has_adj_symbol = $has_adj_symbol || $this->schematic_char_has_adj_symbol($schematic_char, $schematic_line_num, $schematic_index);
          if (!isset($schematic_line[$schematic_index + 1]) || !is_numeric($schematic_line[$schematic_index + 1])) {
            if ($has_adj_symbol) $this->part_numbers[] = (int)$part_number;
            $part_number = "";
            $has_adj_symbol = false;
          }
        }
      }
    }
  }

  private function schematic_char_has_adj_symbol(string $schematic_char, int $line_num, int $index): bool {
    return (isset($this->schematic_matrix[$line_num - 1]) && isset($this->schematic_matrix[$line_num - 1][$index - 1]) && char_is_symbol($this->schematic_matrix[$line_num - 1][$index - 1]))
      || (isset($this->schematic_matrix[$line_num - 1]) && isset($this->schematic_matrix[$line_num - 1][$index]) && char_is_symbol($this->schematic_matrix[$line_num - 1][$index]))
      || (isset($this->schematic_matrix[$line_num - 1]) && isset($this->schematic_matrix[$line_num - 1][$index + 1]) && char_is_symbol($this->schematic_matrix[$line_num - 1][$index + 1]))
      || (isset($this->schematic_matrix[$line_num]) && isset($this->schematic_matrix[$line_num][$index - 1]) && char_is_symbol($this->schematic_matrix[$line_num][$index - 1]))
      || (isset($this->schematic_matrix[$line_num]) && isset($this->schematic_matrix[$line_num][$index + 1]) && char_is_symbol($this->schematic_matrix[$line_num][$index + 1]))
      || (isset($this->schematic_matrix[$line_num + 1]) && isset($this->schematic_matrix[$line_num + 1][$index - 1]) && char_is_symbol($this->schematic_matrix[$line_num + 1][$index - 1]))
      || (isset($this->schematic_matrix[$line_num + 1]) && isset($this->schematic_matrix[$line_num + 1][$index]) && char_is_symbol($this->schematic_matrix[$line_num + 1][$index]))
      || (isset($this->schematic_matrix[$line_num + 1]) && isset($this->schematic_matrix[$line_num + 1][$index + 1]) && char_is_symbol($this->schematic_matrix[$line_num + 1][$index + 1]))
    ;
  }
}

function char_is_symbol(string $char) {
  return $char !== "." && !is_numeric($char);
}

$engine = new Engine($str);

echo array_reduce($engine->part_numbers, fn($carry, $current) => $carry + $current, 0) . PHP_EOL;

?>