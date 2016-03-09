<?php

/* ***** MINECRAFT 3D Skin Generator *****
 * The contents of this project were first developed by Pierre Gros on 17th April 2012.
 * It has once been modified by Carlos Ferreira (http://www.carlosferreira.me) on 31st May 2014.
 * Translations done by Carlos Ferreira.
 */
/*
 **** GET Parameters ****
 ** These parameters have been renamed to match their English translations **
 * user - Minecraft's username for the skin to be rendered.
 * vr - Vertical Rotation.
 * hr - Horizontal Rotation.
 *
 * hrh - Horizontal Rotation of the Head.
 *
 * vrll - Vertical Rotation of the Left Leg.
 * vrrl - Vertical Rotation of the Right Leg.
 * vrla - Vertical Rotation of the Left Arm.
 * vrra - Vertical Rotation of the Right Arm.
 *
 * displayHair - Either or not to display hairs. Set to "false" to NOT display hairs.
 * headOnly - Either or not to display the ONLY the head. Set to "true" to display ONLY the head (and the hair, based on displayHair).
 *
 * format - The format in which the image is to be rendered. PNG ("png") is used by default set to "svg" to use a vector version.
 * ratio - The size of the "png" image. The default and minimum value is 2.
 */

error_reporting(E_ALL);

require_once 'functions.php';
require_once 'classes/Point.php';
require_once 'classes/Polygon.php';
require_once 'classes/MinecraftSkinRenderer.php';

$times = array();

$times[] = array('Start', microtime_float());

$username = $_GET['user'];

$img_png = MinecraftSkinRenderer::getSkinImageByUsername($username);

$width = imagesx($img_png);
$height = imagesy($img_png);
if ($height == 64 && $width == 64)
{
    // TODO: Implement new 64x64 skins
}
if (!($width == $height * 2) || $height % 32 != 0)
{
    // Bad ratio created
    $img_png = imagecreatefrompng(MinecraftSkinRenderer::FALLBACK_IMAGE);
}



$hd_ratio = $height / 32; // Set HD ratio to 2 if the skin is 128x64

$times[] = array('Download-Image', microtime_float());

$vertical_rotation = $_GET['vr'];
$horizontal_rotation = $_GET['hr'];
$head_only = ($_GET['headOnly'] == 'true');
$display_hair = ($_GET['displayHair'] != 'false');

// Rotation variables in radians (3D Rendering)
$alpha = deg2rad($vertical_rotation); // Vertical rotation on the X axis.
$omega = deg2rad($horizontal_rotation); // Horizontal rotation on the Y axis.

// Cosine and Sine values
$cos_alpha = cos($alpha);
$sin_alpha = sin($alpha);
$cos_omega = cos($omega);
$sin_omega = sin($omega);

// Head, Helmet, Torso, Arms, Legs
$parts_angles = array();
$parts_angles['torso'] = array(
    'cos_alpha' => cos(0),
    'sin_alpha' => sin(0),
    'cos_omega' => cos(0),
    'sin_omega' => sin(0)
);
$alpha_head = 0;
$omega_head = deg2rad($_GET['hrh']);
$parts_angles['head'] = array(
    'cos_alpha' => cos($alpha_head),
    'sin_alpha' => sin($alpha_head),
    'cos_omega' => cos($omega_head),
    'sin_omega' => sin($omega_head)
);
$parts_angles['helmet'] = array(
    'cos_alpha' => cos($alpha_head),
    'sin_alpha' => sin($alpha_head),
    'cos_omega' => cos($omega_head),
    'sin_omega' => sin($omega_head)
);
$alpha_right_arm = deg2rad($_GET['vrra']);
$omega_right_arm = 0;
$parts_angles['rightArm'] = array(
    'cos_alpha' => cos($alpha_right_arm),
    'sin_alpha' => sin($alpha_right_arm),
    'cos_omega' => cos($omega_right_arm),
    'sin_omega' => sin($omega_right_arm)
);
$alpha_left_arm = deg2rad($_GET['vrla']);
$omega_left_arm = 0;
$parts_angles['leftArm'] = array(
    'cos_alpha' => cos($alpha_left_arm),
    'sin_alpha' => sin($alpha_left_arm),
    'cos_omega' => cos($omega_left_arm),
    'sin_omega' => sin($omega_left_arm)
);
$alpha_right_leg = deg2rad($_GET['vrrl']);
$omega_right_leg = 0;
$parts_angles['rightLeg'] = array(
    'cos_alpha' => cos($alpha_right_leg),
    'sin_alpha' => sin($alpha_right_leg),
    'cos_omega' => cos($omega_right_leg),
    'sin_omega' => sin($omega_right_leg)
);
$alpha_left_leg = deg2rad($_GET['vrll']);
$omega_left_leg = 0;
$parts_angles['leftLeg'] = array(
    'cos_alpha' => cos($alpha_left_leg),
    'sin_alpha' => sin($alpha_left_leg),
    'cos_omega' => cos($omega_left_leg),
    'sin_omega' => sin($omega_left_leg)
);

$minX = $maxX = $minY = $maxY = 0;

$times[] = array('Angle-Calculations', microtime_float());

$visible_faces_format = array(
    'front' => array(),
    'back' => array()
);

$visible_faces = array(
    'head' => $visible_faces_format,
    'torso' => $visible_faces_format,
    'rightArm' => $visible_faces_format,
    'leftArm' => $visible_faces_format,
    'rightLeg' => $visible_faces_format,
    'leftLeg' => $visible_faces_format
);

$all_faces = array(
    'back',
    'right',
    'top',
    'front',
    'left',
    'bottom'
);

// Loop each preProject and Project then calculate the visible faces for each - also display
foreach ($visible_faces as $k => &$v)
{
    unset($cube_max_depth_faces, $cube_points);

    $cube_points = array();
    $cube_points[] = array(new Point(array('x' => 0, 'y' => 0, 'z' => 0)), array('back', 'right', 'top')); // 0
    $cube_points[] = array(new Point(array('x' => 0, 'y' => 0, 'z' => 1)), array('front', 'right', 'top')); // 1
    $cube_points[] = array(new Point(array('x' => 0, 'y' => 1, 'z' => 0)), array('back', 'right', 'bottom')); // 2
    $cube_points[] = array(new Point(array('x' => 0, 'y' => 1, 'z' => 1)), array('front', 'right', 'bottom')); // 3
    $cube_points[] = array(new Point(array('x' => 1, 'y' => 0, 'z' => 0)), array('back', 'left', 'top')); // 4
    $cube_points[] = array(new Point(array('x' => 1, 'y' => 0, 'z' => 1)), array('front', 'left', 'top')); // 5
    $cube_points[] = array(new Point(array('x' => 1, 'y' => 1, 'z' => 0)), array('back', 'left', 'bottom')); // 6
    $cube_points[] = array(new Point(array('x' => 1, 'y' => 1, 'z' => 1)), array('front', 'left', 'bottom')); // 7

    foreach ($cube_points as $cube_point)
    {
        $cube_point[0]->preProject(0, 0, 0, $parts_angles[$k]['cos_alpha'], $parts_angles[$k]['sin_alpha'], $parts_angles[$k]['cos_omega'], $parts_angles[$k]['sin_omega']);
        $cube_point[0]->project();
        if (!isset($cube_max_depth_faces))
        {
            $cube_max_depth_faces = $cube_point;
        }
        else if ($cube_max_depth_faces[0]->getDepth() > $cube_point[0]->getDepth())
        {
            $cube_max_depth_faces = $cube_point;
        }
    }
    $v['back'] = $cube_max_depth_faces[1];
    $v['front'] = array_diff($all_faces, $v['back']);
}


$cube_points = array();
$cube_points[] = array(new Point(array('x' => 0, 'y' => 0, 'z' => 0)), array('back', 'right', 'top')); // 0
$cube_points[] = array(new Point(array('x' => 0, 'y' => 0, 'z' => 1)), array('front', 'right', 'top')); // 1
$cube_points[] = array(new Point(array('x' => 0, 'y' => 1, 'z' => 0)), array('back', 'right', 'bottom')); // 2
$cube_points[] = array(new Point(array('x' => 0, 'y' => 1, 'z' => 1)), array('front', 'right', 'bottom')); // 3
$cube_points[] = array(new Point(array('x' => 1, 'y' => 0, 'z' => 0)), array('back', 'left', 'top')); // 4
$cube_points[] = array(new Point(array('x' => 1, 'y' => 0, 'z' => 1)), array('front', 'left', 'top')); // 5
$cube_points[] = array(new Point(array('x' => 1, 'y' => 1, 'z' => 0)), array('back', 'left', 'bottom')); // 6
$cube_points[] = array(new Point(array('x' => 1, 'y' => 1, 'z' => 1)), array('front', 'left', 'bottom')); // 7

unset($cube_max_depth_faces);
foreach ($cube_points as $cube_point)
{
    $cube_point[0]->project();
    if (!isset($cube_max_depth_faces))
    {
        $cube_max_depth_faces = $cube_point;
    }
    elseif ($cube_max_depth_faces[0]->getDepth() > $cube_point[0]->getDepth())
    {
        $cube_max_depth_faces = $cube_point;
    }
}
$back_faces = $cube_max_depth_faces[1];
$front_faces = array_diff($all_faces, $back_faces);

$times[] = array('Determination-of-faces', microtime_float());

$depths_of_face = array();
$polygons = array();
$cube_faces_array = array(
    'front' => array(),
    'back' => array(),
    'top' => array(),
    'bottom' => array(),
    'right' => array(),
    'left' => array()
);
$polygons = array(
    'helmet' => $cube_faces_array,
    'head' => $cube_faces_array,
    'torso' => $cube_faces_array,
    'rightArm' => $cube_faces_array,
    'leftArm' => $cube_faces_array,
    'rightLeg' => $cube_faces_array,
    'leftLeg' => $cube_faces_array
);
// HEAD
for ($i = 0; $i < 9 * $hd_ratio; $i++)
{
    for ($j = 0; $j < 9 * $hd_ratio; $j++)
    {
        if (!isset($volume_points[$i][$j][-2 * $hd_ratio]))
        {
            $volume_points[$i][$j][-2 * $hd_ratio] = new Point(array('x' => $i, 'y' => $j, 'z' => -2 * $hd_ratio));
        }
        if (!isset($volume_points[$i][$j][6 * $hd_ratio]))
        {
            $volume_points[$i][$j][6 * $hd_ratio] = new Point(array('x' => $i, 'y' => $j, 'z' => 6 * $hd_ratio));
        }
    }
}
for ($j = 0; $j < 9 * $hd_ratio; $j++)
{
    for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++)
    {
        if (!isset($volume_points[0][$j][$k]))
        {
            $volume_points[0][$j][$k] = new Point(array('x' => 0, 'y' => $j, 'z' => $k));
        }
        if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
        {
            $volume_points[8 * $hd_ratio][$j][$k] = new Point(array('x' => 8 * $hd_ratio, 'y' => $j, 'z' => $k));
        }
    }
}
for ($i = 0; $i < 9 * $hd_ratio; $i++)
{
    for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++)
    {
        if (!isset($volume_points[$i][0][$k]))
        {
            $volume_points[$i][0][$k] = new Point(array('x' => $i, 'y' => 0, 'z' => $k));
        }
        if (!isset($volume_points[$i][8 * $hd_ratio][$k]))
        {
            $volume_points[$i][8 * $hd_ratio][$k] = new Point(array('x' => $i, 'y' => 8 * $hd_ratio, 'z' => $k));
        }
    }
}
for ($i = 0; $i < 8 * $hd_ratio; $i++)
{
    for ($j = 0; $j < 8 * $hd_ratio; $j++)
    {
        $polygons['head']['back'][] = new Polygon(array(
            $volume_points[$i][$j][-2 * $hd_ratio],
            $volume_points[$i + 1][$j][-2 * $hd_ratio],
            $volume_points[$i + 1][$j + 1][-2 * $hd_ratio],
            $volume_points[$i][$j + 1][-2 * $hd_ratio]
        ), imagecolorat($img_png, (32 * $hd_ratio - 1) - $i, 8 * $hd_ratio + $j));
        $polygons['head']['front'][] = new Polygon(array(
            $volume_points[$i][$j][6 * $hd_ratio],
            $volume_points[$i + 1][$j][6 * $hd_ratio],
            $volume_points[$i + 1][$j + 1][6 * $hd_ratio],
            $volume_points[$i][$j + 1][6 * $hd_ratio]
        ), imagecolorat($img_png, 8 * $hd_ratio + $i, 8 * $hd_ratio + $j));
    }
}
for ($j = 0; $j < 8 * $hd_ratio; $j++)
{
    for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++)
    {
        $polygons['head']['right'][] = new Polygon(array(
            $volume_points[0][$j][$k],
            $volume_points[0][$j][$k + 1],
            $volume_points[0][$j + 1][$k + 1],
            $volume_points[0][$j + 1][$k]
        ), imagecolorat($img_png, $k + 2 * $hd_ratio, 8 * $hd_ratio + $j));
        $polygons['head']['left'][] = new Polygon(array(
            $volume_points[8 * $hd_ratio][$j][$k],
            $volume_points[8 * $hd_ratio][$j][$k + 1],
            $volume_points[8 * $hd_ratio][$j + 1][$k + 1],
            $volume_points[8 * $hd_ratio][$j + 1][$k]
        ), imagecolorat($img_png, (24 * $hd_ratio - 1) - $k - 2 * $hd_ratio, 8 * $hd_ratio + $j));
    }
}
for ($i = 0; $i < 8 * $hd_ratio; $i++)
{
    for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++)
    {
        $polygons['head']['top'][] = new Polygon(array(
            $volume_points[$i][0][$k],
            $volume_points[$i + 1][0][$k],
            $volume_points[$i + 1][0][$k + 1],
            $volume_points[$i][0][$k + 1]
        ), imagecolorat($img_png, 8 * $hd_ratio + $i, $k + 2 * $hd_ratio));
        $polygons['head']['bottom'][] = new Polygon(array(
            $volume_points[$i][8 * $hd_ratio][$k],
            $volume_points[$i + 1][8 * $hd_ratio][$k],
            $volume_points[$i + 1][8 * $hd_ratio][$k + 1],
            $volume_points[$i][8 * $hd_ratio][$k + 1]
        ), imagecolorat($img_png, 16 * $hd_ratio + $i, (8 * $hd_ratio - 1) - ($k + 2 * $hd_ratio)));
    }
}
if ($display_hair)
{
    // HELMET/HAIR
    $volume_points = array();
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 9 * $hd_ratio; $j++)
        {
            if (!isset($volume_points[$i][$j][-2 * $hd_ratio]))
            {
                $volume_points[$i][$j][-2 * $hd_ratio] = new Point(array('x' => $i * 9 / 8 - 0.5 * $hd_ratio, 'y' => $j * 9 / 8 - 0.5 * $hd_ratio, 'z' => -2.5 * $hd_ratio));
            }
            if (!isset($volume_points[$i][$j][6 * $hd_ratio]))
            {
                $volume_points[$i][$j][6 * $hd_ratio] = new Point(array('x' => $i * 9 / 8 - 0.5 * $hd_ratio, 'y' => $j * 9 / 8 - 0.5 * $hd_ratio, 'z' => 6.5 * $hd_ratio));
            }
        }
    }
    for ($j = 0; $j < 9 * $hd_ratio; $j++)
    {
        for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[0][$j][$k]))
            {
                $volume_points[0][$j][$k] = new Point(array('x' => -0.5 * $hd_ratio, 'y' => $j * 9 / 8 - 0.5 * $hd_ratio, 'z' => $k * 9 / 8 - 0.5 * $hd_ratio));
            }
            if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
            {
                $volume_points[8 * $hd_ratio][$j][$k] = new Point(array('x' => 8.5 * $hd_ratio, 'y' => $j * 9 / 8 - 0.5 * $hd_ratio, 'z' => $k * 9 / 8 - 0.5 * $hd_ratio));
            }
        }
    }
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($k = -2 * $hd_ratio; $k < 7 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[$i][0][$k]))
            {
                $volume_points[$i][0][$k] = new Point(array('x' => $i * 9 / 8 - 0.5 * $hd_ratio, 'y' => -0.5 * $hd_ratio, 'z' => $k * 9 / 8 - 0.5 * $hd_ratio));
            }
            if (!isset($volume_points[$i][8 * $hd_ratio][$k]))
            {
                $volume_points[$i][8 * $hd_ratio][$k] = new Point(array('x' => $i * 9 / 8 - 0.5 * $hd_ratio, 'y' => 8.5 * $hd_ratio, 'z' => $k * 9 / 8 - 0.5 * $hd_ratio));
            }
        }
    }
    for ($i = 0; $i < 8 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 8 * $hd_ratio; $j++)
        {
            $polygons['helmet']['back'][] = new Polygon(array(
                $volume_points[$i][$j][-2 * $hd_ratio],
                $volume_points[$i + 1][$j][-2 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][-2 * $hd_ratio],
                $volume_points[$i][$j + 1][-2 * $hd_ratio]
            ), imagecolorat($img_png, 32 * $hd_ratio + (32 * $hd_ratio - 1) - $i, 8 * $hd_ratio + $j));
            $polygons['helmet']['front'][] = new Polygon(array(
                $volume_points[$i][$j][6 * $hd_ratio],
                $volume_points[$i + 1][$j][6 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][6 * $hd_ratio],
                $volume_points[$i][$j + 1][6 * $hd_ratio]
            ), imagecolorat($img_png, 32 * $hd_ratio + 8 * $hd_ratio + $i, 8 * $hd_ratio + $j));
        }
    }
    for ($j = 0; $j < 8 * $hd_ratio; $j++)
    {
        for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++)
        {
            $polygons['helmet']['right'][] = new Polygon(array(
                $volume_points[0][$j][$k],
                $volume_points[0][$j][$k + 1],
                $volume_points[0][$j + 1][$k + 1],
                $volume_points[0][$j + 1][$k]
            ), imagecolorat($img_png, 32 * $hd_ratio + $k + 2 * $hd_ratio, 8 * $hd_ratio + $j));
            $polygons['helmet']['left'][] = new Polygon(array(
                $volume_points[8 * $hd_ratio][$j][$k],
                $volume_points[8 * $hd_ratio][$j][$k + 1],
                $volume_points[8 * $hd_ratio][$j + 1][$k + 1],
                $volume_points[8 * $hd_ratio][$j + 1][$k]
            ), imagecolorat($img_png, 32 * $hd_ratio + (24 * $hd_ratio - 1) - $k - 2 * $hd_ratio, 8 * $hd_ratio + $j));
        }
    }
    for ($i = 0; $i < 8 * $hd_ratio; $i++)
    {
        for ($k = -2 * $hd_ratio; $k < 6 * $hd_ratio; $k++)
        {
            $polygons['helmet']['top'][] = new Polygon(array(
                $volume_points[$i][0][$k],
                $volume_points[$i + 1][0][$k],
                $volume_points[$i + 1][0][$k + 1],
                $volume_points[$i][0][$k + 1]
            ), imagecolorat($img_png, 32 * $hd_ratio + 8 * $hd_ratio + $i, $k + 2 * $hd_ratio));
            $polygons['helmet']['bottom'][] = new Polygon(array(
                $volume_points[$i][8 * $hd_ratio][$k],
                $volume_points[$i + 1][8 * $hd_ratio][$k],
                $volume_points[$i + 1][8 * $hd_ratio][$k + 1],
                $volume_points[$i][8 * $hd_ratio][$k + 1]
            ), imagecolorat($img_png, 32 * $hd_ratio + 16 * $hd_ratio + $i, (8 * $hd_ratio - 1) - ($k + 2 * $hd_ratio)));
        }
    }
}
if (!$head_only)
{
    // TORSO
    $volume_points = array();
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 13 * $hd_ratio; $j++)
        {
            if (!isset($volume_points[$i][$j][0]))
            {
                $volume_points[$i][$j][0] = new Point(array('x' => $i, 'y' => $j + 8 * $hd_ratio, 'z' => 0));
            }
            if (!isset($volume_points[$i][$j][4 * $hd_ratio]))
            {
                $volume_points[$i][$j][4 * $hd_ratio] = new Point(array('x' => $i, 'y' => $j + 8 * $hd_ratio, 'z' => 4 * $hd_ratio));
            }
        }
    }
    for ($j = 0; $j < 13 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[0][$j][$k]))
            {
                $volume_points[0][$j][$k] = new Point(array('x' => 0, 'y' => $j + 8 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
            {
                $volume_points[8 * $hd_ratio][$j][$k] = new Point(array('x' => 8 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[$i][0][$k]))
            {
                $volume_points[$i][0][$k] = new Point(array('x' => $i, 'y' => 0 + 8 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[$i][12 * $hd_ratio][$k]))
            {
                $volume_points[$i][12 * $hd_ratio][$k] = new Point(array('x' => $i, 'y' => 12 * $hd_ratio + 8 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 8 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 12 * $hd_ratio; $j++)
        {
            $polygons['torso']['back'][] = new Polygon(array(
                $volume_points[$i][$j][0],
                $volume_points[$i + 1][$j][0],
                $volume_points[$i + 1][$j + 1][0],
                $volume_points[$i][$j + 1][0]
            ), imagecolorat($img_png, (40 * $hd_ratio - 1) - $i, 20 * $hd_ratio + $j));
            $polygons['torso']['front'][] = new Polygon(array(
                $volume_points[$i][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                $volume_points[$i][$j + 1][4 * $hd_ratio]
            ), imagecolorat($img_png, 20 * $hd_ratio + $i, 20 * $hd_ratio + $j));
        }
    }
    for ($j = 0; $j < 12 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['torso']['right'][] = new Polygon(array(
                $volume_points[0][$j][$k],
                $volume_points[0][$j][$k + 1],
                $volume_points[0][$j + 1][$k + 1],
                $volume_points[0][$j + 1][$k]
            ), imagecolorat($img_png, 16 * $hd_ratio + $k, 20 * $hd_ratio + $j));
            $polygons['torso']['left'][] = new Polygon(array(
                $volume_points[8 * $hd_ratio][$j][$k],
                $volume_points[8 * $hd_ratio][$j][$k + 1],
                $volume_points[8 * $hd_ratio][$j + 1][$k + 1],
                $volume_points[8 * $hd_ratio][$j + 1][$k]
            ), imagecolorat($img_png, (32 * $hd_ratio - 1) - $k, 20 * $hd_ratio + $j));
        }
    }
    for ($i = 0; $i < 8 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['torso']['top'][] = new Polygon(array(
                $volume_points[$i][0][$k],
                $volume_points[$i + 1][0][$k],
                $volume_points[$i + 1][0][$k + 1],
                $volume_points[$i][0][$k + 1]
            ), imagecolorat($img_png, 20 * $hd_ratio + $i, 16 * $hd_ratio + $k));
            $polygons['torso']['bottom'][] = new Polygon(array(
                $volume_points[$i][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                $volume_points[$i][12 * $hd_ratio][$k + 1]
            ), imagecolorat($img_png, 28 * $hd_ratio + $i, (20 * $hd_ratio - 1) - $k));
        }
    }
    // RIGHT ARM
    $volume_points = array();
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 13 * $hd_ratio; $j++)
        {
            if (!isset($volume_points[$i][$j][0]))
            {
                $volume_points[$i][$j][0] = new Point(array('x' => $i - 4 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => 0));
            }
            if (!isset($volume_points[$i][$j][4 * $hd_ratio]))
            {
                $volume_points[$i][$j][4 * $hd_ratio] = new Point(array('x' => $i - 4 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => 4 * $hd_ratio));
            }
        }
    }
    for ($j = 0; $j < 13 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[0][$j][$k]))
            {
                $volume_points[0][$j][$k] = new Point(array('x' => 0 - 4 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
            {
                $volume_points[4 * $hd_ratio][$j][$k] = new Point(array('x' => 4 * $hd_ratio - 4 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[$i][0][$k]))
            {
                $volume_points[$i][0][$k] = new Point(array('x' => $i - 4 * $hd_ratio, 'y' => 0 + 8 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[$i][12 * $hd_ratio][$k]))
            {
                $volume_points[$i][12 * $hd_ratio][$k] = new Point(array('x' => $i - 4 * $hd_ratio, 'y' => 12 * $hd_ratio + 8 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 12 * $hd_ratio; $j++)
        {
            $polygons['rightArm']['back'][] = new Polygon(array(
                $volume_points[$i][$j][0],
                $volume_points[$i + 1][$j][0],
                $volume_points[$i + 1][$j + 1][0],
                $volume_points[$i][$j + 1][0]
            ), imagecolorat($img_png, (56 * $hd_ratio - 1) - $i, 20 * $hd_ratio + $j));
            $polygons['rightArm']['front'][] = new Polygon(array(
                $volume_points[$i][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                $volume_points[$i][$j + 1][4 * $hd_ratio]
            ), imagecolorat($img_png, 44 * $hd_ratio + $i, 20 * $hd_ratio + $j));
        }
    }
    for ($j = 0; $j < 12 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['rightArm']['right'][] = new Polygon(array(
                $volume_points[0][$j][$k],
                $volume_points[0][$j][$k + 1],
                $volume_points[0][$j + 1][$k + 1],
                $volume_points[0][$j + 1][$k]
            ), imagecolorat($img_png, 40 * $hd_ratio + $k, 20 * $hd_ratio + $j));
            $polygons['rightArm']['left'][] = new Polygon(array(
                $volume_points[4 * $hd_ratio][$j][$k],
                $volume_points[4 * $hd_ratio][$j][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k]
            ), imagecolorat($img_png, (52 * $hd_ratio - 1) - $k, 20 * $hd_ratio + $j));
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['rightArm']['top'][] = new Polygon(array(
                $volume_points[$i][0][$k],
                $volume_points[$i + 1][0][$k],
                $volume_points[$i + 1][0][$k + 1],
                $volume_points[$i][0][$k + 1]
            ), imagecolorat($img_png, 44 * $hd_ratio + $i, 16 * $hd_ratio + $k));
            $polygons['rightArm']['bottom'][] = new Polygon(array(
                $volume_points[$i][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                $volume_points[$i][12 * $hd_ratio][$k + 1]
            ), imagecolorat($img_png, 48 * $hd_ratio + $i, (20 * $hd_ratio - 1) - $k));
        }
    }
    // LEFT ARM
    $volume_points = array();
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 13 * $hd_ratio; $j++)
        {
            if (!isset($volume_points[$i][$j][0]))
            {
                $volume_points[$i][$j][0] = new Point(array('x' => $i + 8 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => 0));
            }
            if (!isset($volume_points[$i][$j][4 * $hd_ratio]))
            {
                $volume_points[$i][$j][4 * $hd_ratio] = new Point(array('x' => $i + 8 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => 4 * $hd_ratio));
            }
        }
    }
    for ($j = 0; $j < 13 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[0][$j][$k]))
            {
                $volume_points[0][$j][$k] = new Point(array('x' => 0 + 8 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
            {
                $volume_points[4 * $hd_ratio][$j][$k] = new Point(array('x' => 4 * $hd_ratio + 8 * $hd_ratio, 'y' => $j + 8 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[$i][0][$k]))
            {
                $volume_points[$i][0][$k] = new Point(array('x' => $i + 8 * $hd_ratio, 'y' => 0 + 8 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[$i][12 * $hd_ratio][$k]))
            {
                $volume_points[$i][12 * $hd_ratio][$k] = new Point(array('x' => $i + 8 * $hd_ratio, 'y' => 12 * $hd_ratio + 8 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 12 * $hd_ratio; $j++)
        {
            $polygons['leftArm']['back'][] = new Polygon(array(
                $volume_points[$i][$j][0],
                $volume_points[$i + 1][$j][0],
                $volume_points[$i + 1][$j + 1][0],
                $volume_points[$i][$j + 1][0]
            ), imagecolorat($img_png, (56 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j));
            $polygons['leftArm']['front'][] = new Polygon(array(
                $volume_points[$i][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                $volume_points[$i][$j + 1][4 * $hd_ratio]
            ), imagecolorat($img_png, 44 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j));
        }
    }
    for ($j = 0; $j < 12 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['leftArm']['right'][] = new Polygon(array(
                $volume_points[0][$j][$k],
                $volume_points[0][$j][$k + 1],
                $volume_points[0][$j + 1][$k + 1],
                $volume_points[0][$j + 1][$k]
            ), imagecolorat($img_png, 40 * $hd_ratio + ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j));
            $polygons['leftArm']['left'][] = new Polygon(array(
                $volume_points[4 * $hd_ratio][$j][$k],
                $volume_points[4 * $hd_ratio][$j][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k]
            ), imagecolorat($img_png, (52 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j));
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['leftArm']['top'][] = new Polygon(array(
                $volume_points[$i][0][$k],
                $volume_points[$i + 1][0][$k],
                $volume_points[$i + 1][0][$k + 1],
                $volume_points[$i][0][$k + 1]
            ), imagecolorat($img_png, 44 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 16 * $hd_ratio + $k));
            $polygons['leftArm']['bottom'][] = new Polygon(array(
                $volume_points[$i][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                $volume_points[$i][12 * $hd_ratio][$k + 1]
            ), imagecolorat($img_png, 48 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), (20 * $hd_ratio - 1) - $k));
        }
    }
    // RIGHT LEG
    $volume_points = array();
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 13 * $hd_ratio; $j++)
        {
            if (!isset($volume_points[$i][$j][0]))
            {
                $volume_points[$i][$j][0] = new Point(array('x' => $i, 'y' => $j + 20 * $hd_ratio, 'z' => 0));
            }
            if (!isset($volume_points[$i][$j][4 * $hd_ratio]))
            {
                $volume_points[$i][$j][4 * $hd_ratio] = new Point(array('x' => $i, 'y' => $j + 20 * $hd_ratio, 'z' => 4 * $hd_ratio));
            }
        }
    }
    for ($j = 0; $j < 13 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[0][$j][$k]))
            {
                $volume_points[0][$j][$k] = new Point(array('x' => 0, 'y' => $j + 20 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
            {
                $volume_points[4 * $hd_ratio][$j][$k] = new Point(array('x' => 4 * $hd_ratio, 'y' => $j + 20 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[$i][0][$k]))
            {
                $volume_points[$i][0][$k] = new Point(array('x' => $i, 'y' => 0 + 20 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[$i][12 * $hd_ratio][$k]))
            {
                $volume_points[$i][12 * $hd_ratio][$k] = new Point(array('x' => $i, 'y' => 12 * $hd_ratio + 20 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 12 * $hd_ratio; $j++)
        {
            $polygons['rightLeg']['back'][] = new Polygon(array(
                $volume_points[$i][$j][0],
                $volume_points[$i + 1][$j][0],
                $volume_points[$i + 1][$j + 1][0],
                $volume_points[$i][$j + 1][0]
            ), imagecolorat($img_png, (16 * $hd_ratio - 1) - $i, 20 * $hd_ratio + $j));
            $polygons['rightLeg']['front'][] = new Polygon(array(
                $volume_points[$i][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                $volume_points[$i][$j + 1][4 * $hd_ratio]
            ), imagecolorat($img_png, 4 * $hd_ratio + $i, 20 * $hd_ratio + $j));
        }
    }
    for ($j = 0; $j < 12 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['rightLeg']['right'][] = new Polygon(array(
                $volume_points[0][$j][$k],
                $volume_points[0][$j][$k + 1],
                $volume_points[0][$j + 1][$k + 1],
                $volume_points[0][$j + 1][$k]
            ), imagecolorat($img_png, 0 + $k, 20 * $hd_ratio + $j));
            $polygons['rightLeg']['left'][] = new Polygon(array(
                $volume_points[4 * $hd_ratio][$j][$k],
                $volume_points[4 * $hd_ratio][$j][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k]
            ), imagecolorat($img_png, (12 * $hd_ratio - 1) - $k, 20 * $hd_ratio + $j));
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['rightLeg']['top'][] = new Polygon(array(
                $volume_points[$i][0][$k],
                $volume_points[$i + 1][0][$k],
                $volume_points[$i + 1][0][$k + 1],
                $volume_points[$i][0][$k + 1]
            ), imagecolorat($img_png, 4 * $hd_ratio + $i, 16 * $hd_ratio + $k));
            $polygons['rightLeg']['bottom'][] = new Polygon(array(
                $volume_points[$i][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                $volume_points[$i][12 * $hd_ratio][$k + 1]
            ), imagecolorat($img_png, 8 * $hd_ratio + $i, (20 * $hd_ratio - 1) - $k));
        }
    }
    // LEFT LEG
    $volume_points = array();
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 13 * $hd_ratio; $j++)
        {
            if (!isset($volume_points[$i][$j][0]))
            {
                $volume_points[$i][$j][0] = new Point(array('x' => $i + 4 * $hd_ratio, 'y' => $j + 20 * $hd_ratio, 'z' => 0));
            }
            if (!isset($volume_points[$i][$j][4 * $hd_ratio]))
            {
                $volume_points[$i][$j][4 * $hd_ratio] = new Point(array('x' => $i + 4 * $hd_ratio, 'y' => $j + 20 * $hd_ratio, 'z' => 4 * $hd_ratio));
            }
        }
    }
    for ($j = 0; $j < 13 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[0][$j][$k]))
            {
                $volume_points[0][$j][$k] = new Point(array('x' => 0 + 4 * $hd_ratio, 'y' => $j + 20 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[8 * $hd_ratio][$j][$k]))
            {
                $volume_points[4 * $hd_ratio][$j][$k] = new Point(array('x' => 4 * $hd_ratio + 4 * $hd_ratio, 'y' => $j + 20 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 9 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 5 * $hd_ratio; $k++)
        {
            if (!isset($volume_points[$i][0][$k]))
            {
                $volume_points[$i][0][$k] = new Point(array('x' => $i + 4 * $hd_ratio, 'y' => 0 + 20 * $hd_ratio, 'z' => $k));
            }
            if (!isset($volume_points[$i][12 * $hd_ratio][$k]))
            {
                $volume_points[$i][12 * $hd_ratio][$k] = new Point(array('x' => $i + 4 * $hd_ratio, 'y' => 12 * $hd_ratio + 20 * $hd_ratio, 'z' => $k));
            }
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($j = 0; $j < 12 * $hd_ratio; $j++)
        {
            $polygons['leftLeg']['back'][] = new Polygon(array(
                $volume_points[$i][$j][0],
                $volume_points[$i + 1][$j][0],
                $volume_points[$i + 1][$j + 1][0],
                $volume_points[$i][$j + 1][0]
            ), imagecolorat($img_png, (16 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j));
            $polygons['leftLeg']['front'][] = new Polygon(array(
                $volume_points[$i][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j][4 * $hd_ratio],
                $volume_points[$i + 1][$j + 1][4 * $hd_ratio],
                $volume_points[$i][$j + 1][4 * $hd_ratio]
            ), imagecolorat($img_png, 4 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 20 * $hd_ratio + $j));
        }
    }
    for ($j = 0; $j < 12 * $hd_ratio; $j++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['leftLeg']['right'][] = new Polygon(array(
                $volume_points[0][$j][$k],
                $volume_points[0][$j][$k + 1],
                $volume_points[0][$j + 1][$k + 1],
                $volume_points[0][$j + 1][$k]
            ), imagecolorat($img_png, 0 + ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j));
            $polygons['leftLeg']['left'][] = new Polygon(array(
                $volume_points[4 * $hd_ratio][$j][$k],
                $volume_points[4 * $hd_ratio][$j][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k + 1],
                $volume_points[4 * $hd_ratio][$j + 1][$k]
            ), imagecolorat($img_png, (12 * $hd_ratio - 1) - ((4 * $hd_ratio - 1) - $k), 20 * $hd_ratio + $j));
        }
    }
    for ($i = 0; $i < 4 * $hd_ratio; $i++)
    {
        for ($k = 0; $k < 4 * $hd_ratio; $k++)
        {
            $polygons['leftLeg']['top'][] = new Polygon(array(
                $volume_points[$i][0][$k],
                $volume_points[$i + 1][0][$k],
                $volume_points[$i + 1][0][$k + 1],
                $volume_points[$i][0][$k + 1]
            ), imagecolorat($img_png, 4 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), 16 * $hd_ratio + $k));
            $polygons['leftLeg']['bottom'][] = new Polygon(array(
                $volume_points[$i][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k],
                $volume_points[$i + 1][12 * $hd_ratio][$k + 1],
                $volume_points[$i][12 * $hd_ratio][$k + 1]
            ), imagecolorat($img_png, 8 * $hd_ratio + ((4 * $hd_ratio - 1) - $i), (20 * $hd_ratio - 1) - $k));
        }
    }
}

$times[] = array('Polygon-generation', microtime_float());

foreach ($polygons['head'] as $face)
{
    foreach ($face as $poly)
    {
        $poly->preProject(4, 8, 2, $parts_angles['head']['cos_alpha'], $parts_angles['head']['sin_alpha'], $parts_angles['head']['cos_omega'], $parts_angles['head']['sin_omega']);
    }
}
if ($display_hair)
{
    foreach ($polygons['helmet'] as $face)
    {
        foreach ($face as $poly)
        {
            $poly->preProject(4, 8, 2, $parts_angles['head']['cos_alpha'], $parts_angles['head']['sin_alpha'], $parts_angles['head']['cos_omega'], $parts_angles['head']['sin_omega']);
        }
    }
}
if (!$head_only)
{
    foreach ($polygons['rightArm'] as $face)
    {
        foreach ($face as $poly)
        {
            $poly->preProject(-2, 8, 2, $parts_angles['rightArm']['cos_alpha'], $parts_angles['rightArm']['sin_alpha'], $parts_angles['rightArm']['cos_omega'], $parts_angles['rightArm']['sin_omega']);
        }
    }
    foreach ($polygons['leftArm'] as $face)
    {
        foreach ($face as $poly)
        {
            $poly->preProject(10, 8, 2, $parts_angles['leftArm']['cos_alpha'], $parts_angles['leftArm']['sin_alpha'], $parts_angles['leftArm']['cos_omega'], $parts_angles['leftArm']['sin_omega']);
        }
    }
    foreach ($polygons['rightLeg'] as $face)
    {
        foreach ($face as $poly)
        {
            $poly->preProject(2, 20, ($parts_angles['rightLeg']['sin_alpha'] < 0 ? 0 : 4), $parts_angles['rightLeg']['cos_alpha'], $parts_angles['rightLeg']['sin_alpha'], $parts_angles['rightLeg']['cos_omega'], $parts_angles['rightLeg']['sin_omega']);
        }
    }
    foreach ($polygons['leftLeg'] as $face)
    {
        foreach ($face as $poly)
        {
            $poly->preProject(6, 20, ($parts_angles['leftLeg']['sin_alpha'] < 0 ? 0 : 4), $parts_angles['leftLeg']['cos_alpha'], $parts_angles['leftLeg']['sin_alpha'], $parts_angles['leftLeg']['cos_omega'], $parts_angles['leftLeg']['sin_omega']);
        }
    }
}

$times[] = array('Members-rotation', microtime_float());

foreach ($polygons as $piece)
{
    foreach ($piece as $face)
    {
        foreach ($face as $poly)
        {
            if (!$poly->isProjected())
            {
                $poly->project();
            }
        }
    }
}

$times[] = array('Projection-plan', microtime_float());

$width = $maxX - $minX;
$height = $maxY - $minY;

// Handle the ratio
$min_ratio = 2;
$ratio = intval($_GET['ratio']);
$ratio = ($ratio < $min_ratio) ? $min_ratio : $ratio;

if (MinecraftSkinRenderer::SECONDS_TO_CACHE > 0)
{
    $ts = gmdate("D, d M Y H:i:s", time() + MinecraftSkinRenderer::SECONDS_TO_CACHE) . ' GMT';
    header('Expires: ' . $ts);
    header('Pragma: cache');
    header('Cache-Control: max-age=' . MinecraftSkinRenderer::SECONDS_TO_CACHE);
}

$image = null;
if ($_GET['format'] == 'svg')
{
    header('Content-Type: image/svg+xml');
    echo '<?xml version="1.0" standalone="no"?>
		<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
		"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

		<svg width="100%" height="100%" version="1.1"
		xmlns="http://www.w3.org/2000/svg" viewBox="' . $minX . ' ' . $minY . ' ' . $width . ' ' . $height . '">';
}
else
{
    header('Content-type: image/png');
    $image = imagecreatetruecolor($ratio * $width + 1, $ratio * $height + 1);
    imagesavealpha($image, true);
    $trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $trans_colour);
}

$display_order = array();
if (in_array('top', $front_faces))
{
    if (in_array('right', $front_faces))
    {
        $display_order[] = array('leftLeg' => $back_faces);
        $display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
        $display_order[] = array('rightLeg' => $back_faces);
        $display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
        $display_order[] = array('leftArm' => $back_faces);
        $display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
        $display_order[] = array('torso' => $back_faces);
        $display_order[] = array('torso' => $visible_faces['torso']['front']);
        $display_order[] = array('rightArm' => $back_faces);
        $display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
    }
    else
    {
        $display_order[] = array('rightLeg' => $back_faces);
        $display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
        $display_order[] = array('leftLeg' => $back_faces);
        $display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
        $display_order[] = array('rightArm' => $back_faces);
        $display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
        $display_order[] = array('torso' => $back_faces);
        $display_order[] = array('torso' => $visible_faces['torso']['front']);
        $display_order[] = array('leftArm' => $back_faces);
        $display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
    }
    $display_order[] = array('helmet' => $back_faces);
    $display_order[] = array('head' => $back_faces);
    $display_order[] = array('head' => $visible_faces['head']['front']);
    $display_order[] = array('helmet' => $visible_faces['head']['front']);
}
else
{
    $display_order[] = array('helmet' => $back_faces);
    $display_order[] = array('head' => $back_faces);
    $display_order[] = array('head' => $visible_faces['head']['front']);
    $display_order[] = array('helmet' => $visible_faces['head']['front']);
    if (in_array('right', $front_faces))
    {
        $display_order[] = array('leftArm' => $back_faces);
        $display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
        $display_order[] = array('torso' => $back_faces);
        $display_order[] = array('torso' => $visible_faces['torso']['front']);
        $display_order[] = array('rightArm' => $back_faces);
        $display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
        $display_order[] = array('leftLeg' => $back_faces);
        $display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
        $display_order[] = array('rightLeg' => $back_faces);
        $display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
    }
    else
    {
        $display_order[] = array('rightArm' => $back_faces);
        $display_order[] = array('rightArm' => $visible_faces['rightArm']['front']);
        $display_order[] = array('torso' => $back_faces);
        $display_order[] = array('torso' => $visible_faces['torso']['front']);
        $display_order[] = array('leftArm' => $back_faces);
        $display_order[] = array('leftArm' => $visible_faces['leftArm']['front']);
        $display_order[] = array('rightLeg' => $back_faces);
        $display_order[] = array('rightLeg' => $visible_faces['rightLeg']['front']);
        $display_order[] = array('leftLeg' => $back_faces);
        $display_order[] = array('leftLeg' => $visible_faces['leftLeg']['front']);
    }
}

$times[] = array('Calculated-display-faces', microtime_float());

foreach ($display_order as $pieces)
{
    foreach ($pieces as $piece => $faces)
    {
        foreach ($faces as $face)
        {
            foreach ($polygons[$piece][$face] as $poly)
            {
                if ($_GET['format'] == 'svg')
                {
                    echo $poly->getSvgPolygon(1);
                }
                else
                {
                    $poly->addPngPolygon($image, $minX, $minY, $ratio);
                }
            }
        }
    }
}

$times[] = array('Display-image', microtime_float());

if ($_GET['format'] == 'svg')
{
    echo '</svg>' . "\n";
    for ($i = 1; $i < count($times); $i++)
    {
        echo '<!-- ' . ($times[$i][1] - $times[$i - 1][1]) * 1000 . 'ms : ' . $times[$i][0] . ' -->' . "\n";
    }
    echo '<!-- TOTAL : ' . ($times[count($times) - 1][1] - $times[0][1]) * 1000 . 'ms -->' . "\n";
}
else
{
    imagepng($image);
    imagedestroy($image);
    for ($i = 1; $i < count($times); $i++)
    {
        header('generation-time-' . $i . '-' . $times[$i][0] . ': ' . ($times[$i][1] - $times[$i - 1][1]) * 1000 . 'ms');
    }
    header('generation-time-' . count($times) . '-TOTAL: ' . ($times[count($times) - 1][1] - $times[0][1]) * 1000 . 'ms');
}