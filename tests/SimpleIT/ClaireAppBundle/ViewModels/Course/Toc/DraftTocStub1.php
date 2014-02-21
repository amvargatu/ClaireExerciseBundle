<?php

namespace SimpleIT\ClaireAppBundle\ViewModels\Course\Toc;

use OC\CLAIRE\BusinessRules\Entities\Course\Course\DisplayLevel;
use OC\CLAIRE\BusinessRules\Entities\Course\Course\Status;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class DraftTocStub1 extends TocStub1
{
    const STATUS = Status::DRAFT;

    const DISPLAY_LEVEL = DisplayLevel::MEDIUM;

    protected $status = self::STATUS;

    protected $displayLevel = self::DISPLAY_LEVEL;
}