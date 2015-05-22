<?php

namespace Botrayal\Map\Voronoi;

/**
 * A helper class for Fortune's agorithm.
 *
 * @version 1.0
 * @author Dennis
 */
class Fortune
{
    private $width;
    private $height;
    private $actions;
    private $edges;
    private $yMax;
    private $root;
    private $ly;
    private $firstPoint;
    
    public function __construct($width, $height, $points) 
    {
        $this->width = $width;
        $this->height = $height;
        $this->actions = new \SplPriorityQueue();
        $this->edges = [];
        $this->yMax = PHP_INT_MAX;
        $this->root = null;
        $this->firstPoint = null;
        
        foreach ($points as $point) {
            $this->actions->insert(new Action($point, Action::INITIALIZE_COASTLINE), $this->getPriority($point->y));
        }
    }
    
    public function isFinished() {
        return $this->actions->count() === 0;
    }
    
    public function step() {
        $action = $this->actions->extract();
        
        $this->ly = $action->point->y;
        
        switch ($action->type) {
            case Action::INITIALIZE_COASTLINE:
                $this->initializeCoastLine($action->point);
                break;
                
            case Action::FINALIZE_COASTLINE:
                $this->finalizeCoastLine($action->point, $action->coastLine);
                break;
        }
        
        $this->yMax = $action->point->y;
        
        while ($this->actions->count() > 0 && $this->actions->current()->type === Action::REMOVED) {
            $this->actions->extract();
        }
    }
    
    private function initializeCoastLine($point) 
    {
        if ($this->root === null) {
            $this->root = new CoastLine($point);
            $this->firstPoint = $point;
        } elseif ($this->root->isLeaf() && $this->root->site->y - $point->y < 1) {
            $this->root->isLeaf = false;
            $this->root->setLeftNeighbor($this->firstPoint);
            $this->root->setRightNeighbor($point);
            
            $startVertex = new Point(($point->x + $this->firstPoint->x) / 2, $this->height);
            
            if ($point->x > $this->firstPoint->x) {
                $this->root->edge = new Edge($this->firstPoint, $point, $startVertex);
            } else {
                $this->root->edge = new Edge($point, $this->firstPoint, $startVertex);
            }
            
            $this->edges[] = $this->root->edge;
            $coastLine = $this->getCoastLineX($point->x);
            
            if ($coastLine->action !== null) {
                $coastLine->action->type = Action::REMOVED;
                $coastLine->action = null;
            }
            
            $startVertex = new Point($point->x, $this->getY($coastLine->site, $point->x));
            $leftEdge = new Edge($coastLine->site, $point, $startVertex);
            $rightEdge = new Edge($coastLine->site, $startVertex, $point);
            
            $leftEdge->neighbor = $rightEdge;
            $this->edges[] = $leftEdge;
            
            $coastLine->edge = $rightEdge;
            $coastLine->isLeaf = false;
            
            $coastLine0 = new CoastLine($coastLine->site);
            $coastLine1 = new CoastLine($coastLine->site);
            
            $coastLine->setRightNeighbor($coastLine1);
            $coastLine->setLeftNeighbor(new CoastLine(null));
            $coastLine->getLeftNeighbor()->edge = $leftEdge;
			$coastLine->getLeftNeighbor()->setLeftNeighbor($coastLine0);
            $coastLine->getLeftNeighbor()->setRightNeighbor(new CoastLine($point));
            
            $this->checkCircle($coastLine0);
            $this->checkCircle($coastLine1);
        }
    }
    
    private function finalizeCoastLine($point, $coastLine)
    {
        $leftParent = $this->getLeftParent($coastLine);
        $rightParent = $this->getRightParent($coastLine);
        
        $leftSibling = $this->getLeftChild($leftParent);
        $rightSibling = $this->getRightChild($rightParent);
        
        if ($leftSibling->action !== null) {
            $leftSibling->action->type = Action::REMOVED;
        }
        
        if ($rightSibling->action !== null) {
            $rightSibling->action->type = Action::REMOVED;
        }
        
        $endPoint = new Point($point->x, $this->getY($coastLine->site, $point->x));
        $this->yMax = $point->y;
        
        $leftParent->edge->end = $endPoint;
        $rightParent->edge->end = $endPoint;
        
        $highestCoastLine = null;
        $currentCoastLine = $coastLine;
        
        while ($currentCoastLine !== $this->root) {
            $currentCoastLine = $currentCoastLine->parent;
            
            if ($currentCoastLine === $leftParent || $currentCoastLine === $rightParent) {
                $highestCoastLine = $currentCoastLine;
            }
        }
        
        $highestCoastLine->edge = new Edge($leftSibling->site, $rightSibling->site, $endPoint);
        $this->edges[] = $highestCoastLine->edge;
        
        $parent = $coastLine->parent->parent;
        
        if ($coastLine->parent->getLeftNeighbor() === $coastLine) {
            if ($parent->getLeftNeighbor() === $coastLine->parent) {
                $parent->setLeftNeighbor($coastLine->parent->getRightNeighbor());
            } else {
                $parent->setRightNeighbor($coastLine->parent->getRightNeighbor());
            }
        } else {
            if ($parent->getLeftNeighbor() === $coastLine->parent) {
                $parent->setLeftNeighbor($coastLine->parent->getLeftNeighbor());
            } else {
                $parent->setRightNeighbor($coastLine->parent->getLeftNeighbor());
            }
        }
			
        $this->checkCircle($leftSibling);
        $this->checkCircle($rightSibling);
    }
    
    private function getLeftParent($coastLine) 
    {
        $parent = $coastLine->parent;
        $current = $coastLine;
        
        while ($parent->getLeftNeighbor() === $current) {
            if ($parent->parent === null) {
                return null;
            }
            
            $current = $parent;
            $parent = $parent->parent;
        }
        
        return $parent;
    }
    
	private function getRightParent($coastLine)
	{
        $parent = $coastLine->parent;
        $current = $coastLine;
        
        while ($parent->getRightNeighbor() === $current) {
            if ($parent->parent === null) {
                return null;
            }
            
            $current = $parent;
            $parent = $parent->parent;
        }
        
        return $parent;
	}
    
	private function getLeftChild($coastLine)
	{
        if ($coastLine === null) {
            return null;
        }
        
        $currentCoastLine = $coastLine->getLeftNeighbor();
        
        while (!$currentCoastLine->isLeaf) {
            $currentCoastLine = $currentCoastLine->getRightNeighbor();
        }
        
        return $currentCoastLine;
	}
    
	private function getRightChild($coastLine)
	{
        if ($coastLine === null) {
            return null;
        }
        
        $currentCoastLine = $coastLine->getRightNeighbor();
        
        while (!$currentCoastLine->isLeaf) {
            $currentCoastLine = $currentCoastLine->getRightNeighbor();
        }
        
        return $currentCoastLine;
	}
    
    private function getCoastLineX($x)
    {
        $currentCoastLine = $this->root;
        $currentX = 0;
			
        while (!$currentCoastLine->isLeaf) {
            $currentX = $this->getXOfEdge($currentCoastLine, $this->ly);
            
            if ($currentX > $x) {
                $currentCoastLine = $currentCoastLine->getLeftNeighbor();
            } else {
                $currentCoastLine = $currentCoastLine->getRightNeighbor();
            }
        }
        
        return $currentCoastLine;
    }
		
    private function getY($point, $x)
    {
        $dp = 2 * ($point->y - $this->ly);
        $b1 = -2 * $point->x / $dp;
        $c1 = $this->ly + $dp / 4 + $point->x * $point->x / $dp;
        
        return $x * $x / $dp + $b1 * $x + $c1;
    }

    private function checkCircle($coastLine)
    {
        $leftParent = $this->getLeftParent($coastLine);
        $rightParent = $this->getRightParent($coastLine);
        
        $leftSibling = $this->getLeftChild($leftParent);
        $rightSibling = $this->getRightChild($rightParent);
        
        if ($leftSibling === null || $rightSibling === null || $leftSibling->site === $rightSibling->site) {
            return;
        }
        
        $intersection = $this->getEdgeIntersection($leftParent->edge, $rightParent->edge);
        
        if ($intersection === null) {
            return;
        }
        
        $distance = $this->getDistance($coastLine->site, $intersection);
        
        if ($intersection->y - $distance >= $this->ly) {
            return;
        }
        
        $actionPoint = new Point($intersection->x, $intersection->y - $distance);
        $action = new Action($actionPoint, Action::FINALIZE_COASTLINE);
        
        $coastLine->action = $action;
        $action->coastLine = $coastLine;
        
        $this->queue->insert($action, $this->getPriority($actionPoint->y));
    }
    
    private function getPriority($y) 
    {
        return $this->height - $y;
    }
    
    private function getXOfEdge($coastLine, $y)
	{
        $leftChild = $this->getLeftChild($coastLine);
        $rightChild = $this->getRightChild($coastLine);
        
        $leftSite = $leftChild->site;
        $rightSite = $rightChild->site;
        	
        $dp = 2 * ($leftSite->y - $y);
        $a1 = 1 / $dp;
        $b1 = -2 * $leftSite->x / $dp;
        $c1 = $y + $dp / 4 + $leftSite->x * $leftSite->x / $dp;
        	
        $dp = 2 * ($rightSite->y - $y);
        $a2 = 1 / $dp;
        $b2 = -2 * $rightSite->x / $dp;
        $c2 = $y + $dp / 4 + $rightSite->x * $rightSite->x / $dp;
        
        $a = $a1 - $a2;
        $b = $b1 - $b2;
        $c = $c1 - $c2;
        
        $d = $b * $b - 4 * $a * $c;
        $intersection0 = (-$b + sqrt($d)) / (2 * $a);
        $intersection1 = (-$b - sqrt($d)) / (2 * $a);
        
        return ($leftSite->y < $rightChild->y) ? max($intersection0, $intersection1) : min($intersection0, $intersection1);
	}
}
