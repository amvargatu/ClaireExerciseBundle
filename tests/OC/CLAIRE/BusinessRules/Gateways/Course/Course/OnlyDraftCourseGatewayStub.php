<?php

namespace OC\CLAIRE\BusinessRules\Gateways\Course\Course;

use OC\CLAIRE\BusinessRules\Entities\Course\Course\DraftCourseStub;
use OC\CLAIRE\BusinessRules\Exceptions\Course\Course\CourseNotFoundException;
use SimpleIT\ApiResourcesBundle\Course\CourseResource;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class OnlyDraftCourseGatewayStub implements CourseGateway
{
    /**
     * @return CourseResource[]
     */
    public function findAllStatus($courseIdentifier)
    {
        return array(new DraftCourseStub());
    }

    /**
     * @return CourseResource
     */
    public function findDraft($courseId)
    {
        return new DraftCourseStub();
    }

    /**
     * @return CourseResource
     */
    public function findWaitingForPublication($courseId)
    {
        return null;
    }

    /**
     * @return CourseResource
     */
    public function findPublished($courseIdentifier)
    {
        return null;
    }

    public function updateToWaitingForPublication($courseId)
    {
        return null;
    }

    public function updateWaitingForPublicationToPublished($courseId)
    {
        return null;
    }

    public function updateDraftToPublished($courseId)
    {
        return null;
    }

    public function updateDraft($courseId, CourseResource $course)
    {
        return null;
    }

    public function deleteWaitingForPublication($courseId)
    {
        throw new CourseNotFoundException();
    }

}