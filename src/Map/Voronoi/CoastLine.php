<?php

namespace Botrayal\Map\Voronoi;

/**
 * A coast line in Fortune's algorithm.
 *
 * @version 1.0
 * @author Dennis Degryse
 */
class CoastLine
{
    public $site;
    public $action;
    public $edge;
    public $isLeaf;
    private $parent;
    private $leftNeighbor;
    private $rightNeighbor;
    
    public function __construct($site) 
    {
        $this->site = $site;
        $this->isLeaf = $site != null;
    }
    
    public function setLeftNeighbor($coastLine) 
    {
        $this->leftNeighbor = $coastLine;
        $coastLine->parent = $this;
    }
    
    public function setRightNeighbor($coastLine) 
    {
        $this->rightNeighbor = $coastLine;
        $coastLine->parent = $this;
    }
    
    public function getLeftNeighbor() 
    {
        return $this->leftNeighbor;
    }
    
    public function getRightNeighbor() 
    {
        return $this->rightNeighbor;
    }
    
    public function getParent() 
    {
        return $this->parent;
    }
}
