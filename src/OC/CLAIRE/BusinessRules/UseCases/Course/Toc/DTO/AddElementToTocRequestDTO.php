<?php

namespace OC\CLAIRE\BusinessRules\UseCases\Course\Toc\DTO;

use OC\CLAIRE\BusinessRules\Requestors\Course\Toc\AddElementToTocRequest;

/**
 * Class AddElementToTocRequestDTO
 *
 * @author Romain Kuzniak <romain.kuzniak@simple-it.fr>
 */
class AddElementToTocRequestDTO implements AddElementToTocRequest
{
    /**
     * @var string
     */
    public $courseId;

    /**
     * @var string
     */
    public $parentId;

    public function __construct($courseId, $parentId)
    {
        $this->courseId = $courseId;
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * @return string
     */
    public function getParentId()
    {
        return $this->parentId;
    }
}