<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

$result = $conn->query("SELECT gender, COUNT(*) as count FROM employees WHERE woreda LIKE '%Woreda 1%' GROUP BY gender");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['gender']] = $row['count'];
}

$conn->close();

// Create image
$width = 600;
$height = 400;
$image = imagecreatetruecolor($width, $height);

// Colors
$white = imagecolorallocate($image, 255, 255, 255);
$red = imagecolorallocate($image, 255, 0, 0);
$blue = imagecolorallocate($image, 0, 0, 255);
$yellow = imagecolorallocate($image, 255, 255, 0);
$black = imagecolorallocate($image, 0, 0, 0);

imagefill($image, 0, 0, $white);

if (empty($data)) {
    imagestring($image, 5, 250, 180, 'No data available', $black);
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}

// Calculate total
$total = array_sum($data);

// Pie chart settings
$center_x = $width / 2;
$center_y = $height / 2;
$radius = 100;
$colors = [$red, $blue, $yellow];
$color_index = 0;

$start_angle = 0;
foreach ($data as $gender => $count) {
    $percentage = $count / $total;
    $end_angle = $start_angle + ($percentage * 360);

    imagefilledarc($image, $center_x, $center_y, $radius * 2, $radius * 2, $start_angle, $end_angle, $colors[$color_index % count($colors)], IMG_ARC_PIE);

    // Label
    $label_angle = $start_angle + ($percentage * 180);
    $label_x = $center_x + cos(deg2rad($label_angle)) * ($radius + 20);
    $label_y = $center_y + sin(deg2rad($label_angle)) * ($radius + 20);
    imagestring($image, 5, $label_x - 20, $label_y - 5, "$gender: $count", $black);

    $start_angle = $end_angle;
    $color_index++;
}

// Title
imagestring($image, 5, 220, 10, 'Employee Gender Distribution', $black);

// Output
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>