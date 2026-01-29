<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

$result = $conn->query("SELECT kebele, COUNT(*) as count FROM employees GROUP BY kebele");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['kebele']] = $row['count'];
}

$conn->close();

// Create image
$width = 600;
$height = 400;
$image = imagecreatetruecolor($width, $height);

// Colors
$white = imagecolorallocate($image, 255, 255, 255);
$green = imagecolorallocate($image, 0, 255, 0);
$black = imagecolorallocate($image, 0, 0, 0);

imagefill($image, 0, 0, $white);

if (empty($data)) {
    imagestring($image, 5, 250, 180, 'No data available', $black);
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}

// Find max count
$max_count = max($data);

// Bar settings
$bar_width = 40;
$x_start = 50;
$y_bottom = $height - 50;
$max_bar_height = 300;

$x = $x_start;
foreach ($data as $kebele => $count) {
    $bar_height = $max_count > 0 ? ($count / $max_count) * $max_bar_height : 0;
    $y_top = $y_bottom - $bar_height;
    
    imagefilledrectangle($image, $x, $y_top, $x + $bar_width, $y_bottom, $green);
    
    // Number on top of bar
    imagestring($image, 5, $x + 5, $y_top - 20, $count, $black);
    
    // Kebele name
    $kebele_short = substr($kebele, 0, 10);
    imagestring($image, 5, $x, $y_bottom + 5, $kebele_short, $black);
    
    $x += $bar_width + 20;
}

// Title
imagestring($image, 5, 220, 10, 'Employees by Kebele', $black);

// Y-axis labels
for ($i = 0; $i <= $max_count; $i += max(1, floor($max_count / 5))) {
    $y = $y_bottom - ($i / $max_count) * $max_bar_height;
    imagestring($image, 5, 10, $y - 5, $i, $black);
}

// Output
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>