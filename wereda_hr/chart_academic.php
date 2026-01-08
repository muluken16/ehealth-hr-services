<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

$result = $conn->query("SELECT education_level, COUNT(*) as count FROM employees WHERE woreda LIKE '%Woreda 1%' GROUP BY education_level");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['education_level']] = $row['count'];
}

$conn->close();

// Create image
$width = 600;
$height = 400;
$image = imagecreatetruecolor($width, $height);

// Colors
$white = imagecolorallocate($image, 255, 255, 255);
$red = imagecolorallocate($image, 255, 0, 0);
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
foreach ($data as $education => $count) {
    $bar_height = $max_count > 0 ? ($count / $max_count) * $max_bar_height : 0;
    $y_top = $y_bottom - $bar_height;

    imagefilledrectangle($image, $x, $y_top, $x + $bar_width, $y_bottom, $red);

    // Number on top of bar
    imagestring($image, 5, $x + 5, $y_top - 20, $count, $black);

    // Education level name (shortened)
    $edu_short = substr($education, 0, 10);
    imagestring($image, 5, $x, $y_bottom + 5, $edu_short, $black);

    $x += $bar_width + 20;
}

// Title
imagestring($image, 5, 200, 10, 'Academic Level Distribution', $black);

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