<?php

declare(strict_types=1);

namespace Tanmar\NgInfiniteScrolling\Components;

use Shopware\Core\Framework\Struct\Struct;

class TanmarNgInfiniteScrollingData extends Struct {

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var array
     */
    protected $data;
    
    /**
     * @var array
     */
    protected $pages;
    

    public function __construct() {
        $this->active = false;
        $this->data = [];
    }

    public function getActive(): bool {
        return $this->active;
    }
    
    public function getData(): array {
        return $this->data;
    }
    
    public function getPages(): int {
        return $this->pages;
    }
    

}
