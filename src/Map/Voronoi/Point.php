<?php

namespace Botrayal\Map\Voronoi;

/**
 * A point in the voronoi diagram.
 *
 * @version 1.0
 * @author Dennis Degryse
 */
class Point
{
    public $x;
    public $y;

    public function __construct($x, $y) 
    {
        $this->x = $x;
        $this->y = $y;
    }
}
