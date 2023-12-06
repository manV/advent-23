<?php

if (count($argv) <= 1) {
  throw new Exception("file name is required");
}
$file_name = $argv[1];

$str = file_get_contents($file_name);

class Range {
  public int $source;
  public int $dest;
  public int $len;
  public function __construct(string $range_str) {
    [$dest_str, $source_str, $len_str] = explode(" ", $range_str);
    $this->dest = (int)$dest_str;
    $this->source = (int)$source_str;
    $this->len = (int)$len_str;
  }

  public function is_source_in_range(int $source_value): bool {
    return ($this->source <= $source_value) && ($source_value <= ($this->source + $this->len - 1));
  }
  public function is_dest_in_range(int $dest_value): bool {
    return ($this->dest <= $dest_value) && ($dest_value <= ($this->dest + $this->len - 1));
  }

  public function get_dest_for(int $source_value):int {
    $diff = $source_value - $this->source;
    return $this->dest + $diff;
  }
  public function get_source_for(int $dest_value):int {
    $diff = $dest_value - $this->dest;
    return $this->source + $diff;
  }
}

class RangeMap {
  public string $from;
  public string $to;
  public array $ranges = array();
  
  public function __construct(string $map_str) {
    $lines = explode("\n", $map_str);
    $title_str = $lines[0];
    [$from_to_str] = explode(" ", $title_str);
    [$this->from, $this->to] = explode("-to-", $from_to_str);
    $range_strs = array_slice($lines, 1);
    foreach($range_strs as $range_str) {
      $this->ranges[] = new Range($range_str);
    }
  }

  public function resolve(string $from, string $to, int $value) {
    if ($this->from === $from && $this->to === $to) {
      foreach($this->ranges as $range) {
        if ($range->is_source_in_range($value)) {
          return $range->get_dest_for($value);
        }
      }
      return $value;
    } else if ($this->to === $from && $this->from === $to) {
      foreach($this->ranges as $range) {
        if ($range->is_dest_in_range($value)) {
          return $range->get_source_for($value);
        }
      }
      return $value;
    }

    throw new Exception("invalid lookup.");
  }
}

class RangeMapResolver {
  public $range_maps = array();

  public function add_map(RangeMap $new_map) {
    $this->range_maps[] = $new_map;
  }

  private function find_range_map(string $from, string $to): RangeMap {
    foreach($this->range_maps as $range_map) {
      if (($range_map->from === $from && $range_map->to === $to) || ($range_map->from === $to && $range_map->to === $from)) {
        return $range_map;
      }
    }
    throw new Exception("No such range map");
  }

  public function resolve_seed_to_location_value(int $seed_val): int {
    $soil_val = $this->find_range_map("seed", "soil")->resolve("seed", "soil", $seed_val);
    $fertilizer_val = $this->find_range_map("soil", "fertilizer")->resolve("soil", "fertilizer", $soil_val);
    $water_val = $this->find_range_map("fertilizer", "water")->resolve("fertilizer", "water", $fertilizer_val);
    $light_val = $this->find_range_map("water", "light")->resolve("water", "light", $water_val);
    $temperature_val = $this->find_range_map("light", "temperature")->resolve("light", "temperature", $light_val);
    $humidity_val = $this->find_range_map("temperature", "humidity")->resolve("temperature", "humidity", $temperature_val);
    $location_val = $this->find_range_map("humidity", "location")->resolve("humidity", "location", $humidity_val);
    return $location_val;
  }
}

$arr = explode("\n\n", $str);
[, $seeds_str] = explode(": " ,$arr[0]);
$map_strs = array_slice($arr, 1);


$seeds = array_map(function ($val) {
  return (int)$val;
}, explode(" ", $seeds_str));

$range_map_resolver = new RangeMapResolver();

foreach($map_strs as $map_str) {
  $range_map_resolver->add_map(new RangeMap($map_str));
}

echo min(array_map(function ($seed) use($range_map_resolver) {
  return $range_map_resolver->resolve_seed_to_location_value($seed);
}, $seeds)) . PHP_EOL;

?>