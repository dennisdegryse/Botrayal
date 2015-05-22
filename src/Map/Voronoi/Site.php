<?php

namespace Botrayal\Map\Voronoi;

/**
 * A site in the voronoi diagram
 *
 * @version 1.0
 * @author Dennis Degryse
 */
class Site
{
    public $point;
    public $edges;
    public $coastLine;
    
    public function __construct($point) 
    {
        $this->point = $point;
        $this->edges = [];
        $this->coastLine = null;
    }
}
