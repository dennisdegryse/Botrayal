<?php

namespace Botrayal\Map\Voronoi;

/**
 * An action in Fortune's algorithm.
 *
 * @version 1.0
 * @author Dennis
 */
class Action
{
    const REMOVED = 0x00;
    const INITIALIZE_COASTLINE = 0x01;
    const FINALIZE_COASTLINE = 0x02;

    public $point;
    public $type;
    public $coastLine;
    
    public function __construct($point, $type) {
        $this->type = $type;
        $this->point = $point;
        $this->arch = null;
    }
}
