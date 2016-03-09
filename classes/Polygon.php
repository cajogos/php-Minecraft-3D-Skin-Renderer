<?php

/**
 * Class Polygon
 */
class Polygon
{
    /**
     * @var Point[]
     */
    private $_dots;

    /**
     * @var int
     */
    private $_colour;

    /**
     * @var bool
     */
    private $_isProjected = false;

    /**
     * @var string
     */
    private $_face = 'w';

    /**
     * @var int
     */
    private $_faceDepth = 0;

    /**
     * Polygon constructor.
     * @param Point[] $dots
     * @param int $colour
     */
    function __construct($dots, $colour)
    {
        $this->_dots = $dots;
        $this->_colour = $colour;
        $coord_0 = $dots[0]->getOriginCoord();
        $coord_1 = $dots[1]->getOriginCoord();
        $coord_2 = $dots[2]->getOriginCoord();
        if ($coord_0['x'] == $coord_1['x'] && $coord_1['x'] == $coord_2['x'])
        {
            $this->_face = 'x';
            $this->_faceDepth = $coord_0['x'];
        }
        else if ($coord_0['y'] == $coord_1['y'] && $coord_1['y'] == $coord_2['y'])
        {
            $this->_face = 'y';
            $this->_faceDepth = $coord_0['y'];
        }
        else if ($coord_0['z'] == $coord_1['z'] && $coord_1['z'] == $coord_2['z'])
        {
            $this->_face = 'z';
            $this->_faceDepth = $coord_0['z'];
        }
    }

    /**
     * @return string
     */
    function getFace()
    {
        return $this->_face;
    }

    /**
     * @return int
     */
    function getFaceDepth()
    {
        if (!$this->_isProjected)
        {
            $this->project();
        }
        return $this->_faceDepth;
    }

    /**
     * @param int $ratio
     * @return string
     */
    function getSvgPolygon($ratio)
    {
        $points_2d = '';
        $r = ($this->_colour >> 16) & 0xFF;
        $g = ($this->_colour >> 8) & 0xFF;
        $b = $this->_colour & 0xFF;
        $vR = (127 - (($this->_colour & 0x7F000000) >> 24)) / 127;
        if ($vR == 0)
        {
            return '';
        }
        foreach ($this->_dots as $dot)
        {
            $coord = $dot->getDestCoord();
            $points_2d .= $coord['x'] * $ratio . ',' . $coord['y'] * $ratio . ' ';
        }
        $alpha = 1; // TODO: Implement this :)
        return '<polygon points="' . $points_2d . '" style="fill:rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')" />' . "\n";
    }

    /**
     * @param $image
     * @param int $minX
     * @param int $minY
     * @param int $ratio
     */
    function addPngPolygon(&$image, $minX, $minY, $ratio)
    {
        $points_2d = array();
        $nb_points = 0;
        $r = ($this->_colour >> 16) & 0xFF;
        $g = ($this->_colour >> 8) & 0xFF;
        $b = $this->_colour & 0xFF;
        $vR = (127 - (($this->_colour & 0x7F000000) >> 24)) / 127;
        if ($vR == 0)
        {
            return;
        }
        $same_plan_x = true;
        $same_plan_y = true;
        foreach ($this->_dots as $dot)
        {
            $coord = $dot->getDestCoord();
            if (!isset($coord_x))
            {
                $coord_x = $coord['x'];
            }
            if (!isset($coord_y))
            {
                $coord_y = $coord['y'];
            }
            if ($coord_x != $coord['x'])
            {
                $same_plan_x = false;
            }
            if ($coord_y != $coord['y'])
            {
                $same_plan_y = false;
            }
            $points_2d[] = ($coord['x'] - $minX) * $ratio;
            $points_2d[] = ($coord['y'] - $minY) * $ratio;
            $nb_points++;
        }
        if (!($same_plan_x || $same_plan_y))
        {
            $colour = imagecolorallocate($image, $r, $g, $b);
            imagefilledpolygon($image, $points_2d, $nb_points, $colour);
        }
    }

    /**
     * @return bool
     */
    function isProjected()
    {
        return $this->_isProjected;
    }

    function project()
    {
        foreach ($this->_dots as &$dot)
        {
            if (!$dot->isProjected())
            {
                $dot->project();
            }
        }
        $this->_isProjected = true;
    }

    function preProject($dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega)
    {
        foreach ($this->_dots as &$dot)
        {
            $dot->preProject($dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega);
        }
    }
}