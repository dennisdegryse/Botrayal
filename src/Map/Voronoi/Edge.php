<?php

namespace Botrayal\Map\Voronoi;

/**
 * An edge in the voronoi diagram.
 *
 * @version 1.0
 * @author Dennis Degryse
 */
class Edge
{
    public $leftSite;
    public $rightSite;
    public $startVertex;
    public $endVertex;
    public $slope;
    public $yLeft;
    public $direction;
    public $neighbor;
    
    public function __construct($leftSite, $rightSite, $startVertex) 
    {
        $this->leftSite = $leftSite;
        $this->rightSite = $rightSite;
        $this->startVertex = $startVertex;
        $this->endVertex = null;
        $this->slope = ($rightSite->x - $leftSite->x) / ($leftSite->y - $rightSite->y);
        $this->yLeft = $startVertex->y - $this->slope * $startVertex->x;
        $this->direction = new Point($rightSite->y - $leftSite->y, $leftSite->x - $rightSite->x);
        $this->neighbor = null;
    }
}
