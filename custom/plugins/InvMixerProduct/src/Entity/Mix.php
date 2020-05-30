<?php declare(strict_types=1);

namespace InvMixerProduct\Entity;

class Mix {

    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $modifiedAt;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $containerDesign;

    /**
     * @var float
     */
    private $containerMaxWeight;

}
