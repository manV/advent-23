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
    usort($this->ranges, function ($a, $b) {
      return $a->source - $b->source;
    });
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
    $this->range_maps[$this->map_key($new_map->from, $new_map->to)] = $new_map;
  }

  private function map_key(string $from, string $to) {
    return "{$from}-{$to}";
  }

  private function find_range_map(string $from, string $to): RangeMap {
    return $this->range_maps[$this->map_key($from, $to)];
  }

  public function resolve_location_to_seed_value(int $location_val): int {
    $humidity_val = $this->find_range_map("humidity", "location")->resolve("location", "humidity", $location_val);
    $temperature_val = $this->find_range_map("temperature", "humidity")->resolve("humidity", "temperature", $humidity_val);
    $light_val = $this->find_range_map("light", "temperature")->resolve("temperature", "light", $temperature_val);
    $water_val = $this->find_range_map("water", "light")->resolve("light", "water", $light_val);
    $fertilizer_val = $this->find_range_map("fertilizer", "water")->resolve("water", "fertilizer", $water_val);
    $soil_val = $this->find_range_map("soil", "fertilizer")->resolve("fertilizer", "soil", $fertilizer_val);
    $seed_val = $this->find_range_map("seed", "soil")->resolve("soil", "seed", $soil_val);    
    return $seed_val;
  }

  public function find_min_seed_value(array $seeds): int {
    $humidity_to_location_map_ranges = $this->find_range_map("humidity", "location")->ranges;

    for ($i = 0; $i <= PHP_INT_MAX ; $i++) {
      $seed_val = $this->resolve_location_to_seed_value($i);
      if (is_number_in_ranges($seed_val, $seeds)) return $i;
    }
  }
}

function is_number_in_ranges(int $num, array $ranges) {
  foreach($ranges as $range) {
    if ($range[0] <= $num && $num <= $range[1]) return true;
  }
  return false;
}

$arr = explode("\n\n", $str);
[, $seeds_str] = explode(": " ,$arr[0]);
$map_strs = array_slice($arr, 1);


$seeds = array_chunk(array_map(function ($val) {
  return (int)$val;
}, explode(" ", $seeds_str)), 2);

$seeds = array_map(function($seed) {
  return [$seed[0], $seed[0] + $seed[1] - 1];
}, $seeds);

$range_map_resolver = new RangeMapResolver();
foreach($map_strs as $map_str) {
  $range_map_resolver->add_map(new RangeMap($map_str));
}

// takes about 10 mins!!!!!
echo $range_map_resolver->find_min_seed_value($seeds) . PHP_EOL;

?>