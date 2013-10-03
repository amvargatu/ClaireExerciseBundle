<?php


namespace SimpleIT\ClaireAppBundle\Controller\Course\Component;

use SimpleIT\AppBundle\Annotation\Cache;
use SimpleIT\ApiResourcesBundle\Course\CourseResource;
use SimpleIT\ApiResourcesBundle\Course\MetadataResource;
use SimpleIT\Utils\ArrayUtils;
use SimpleIT\AppBundle\Controller\AppController;
use SimpleIT\Utils\Collection\CollectionInformation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CourseController
 *
 * @author Romain Kuzniak <romain.kuzniak@simple-it.fr>
 */
class CourseController extends AppController
{

    /**
     * List courses
     *
     * @param CollectionInformation $collectionInformation Collection Information
     * @param string                $paginationUrl         Pagination url
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache
     */
    public function listAction(
        CollectionInformation $collectionInformation,
        $paginationUrl
    )
    {
        $courses = $this->get('simple_it.claire.course.course')->getAll($collectionInformation);

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:searchList.html.twig',
            array(
                'courses'               => $courses,
                'collectionInformation' => $collectionInformation,
                'paginationUrl'         => $paginationUrl
            )
        );
    }

    /**
     * List courses
     *
     * @param CollectionInformation $collectionInformation Collection Information
     * @param string                $paginationUrl         Pagination url
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache
     */
    public function searchListAction(
        Request $request,
        CollectionInformation $collectionInformation,
        $paginationUrl
    )
    {
        $courses = $this->get('simple_it.claire.course.course')->getAll($collectionInformation);

        if ($request->isXmlHttpRequest()) {
            return new Response($this->searchListJson($courses));
        }

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:searchList.html.twig',
            array(
                'courses'               => $courses,
                'collectionInformation' => $collectionInformation,
                'paginationUrl'         => $paginationUrl
            )
        );
    }

    /**
     * Prepare result course list in Json format
     *
     * @param $courses
     *
     * @return string
     */
    protected function searchListJson($courses)
    {
        $search = array();
        foreach ($courses as $course) {
            $search[] = array(
                'title' => $course->getTitle(),
                'image' => ArrayUtils::getValue(
                    $course->getMetadatas(),
                    MetadataResource::COURSE_METADATA_IMAGE
                ),
                'url' => $this->generateUrl('simple_it_claire_course_course_view', array(
                        'categoryIdentifier' => $course->getCategory()->getSlug(),
                        'courseIdentifier' => $course->getSlug()
                    ))
            );
        }

        return json_encode($search);
    }

    /**
     * View introduction
     *
     * @param int | string $courseIdentifier Course id | slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache (namespacePrefix="claire_app_course_course", namespaceAttribute="courseIdentifier", lifetime=0)
     */
    public function viewIntroductionAction($courseIdentifier)
    {
        $introduction = $this->get('simple_it.claire.course.course')->getIntroduction(
            $courseIdentifier
        );

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:viewContent.html.twig',
            array('content' => $introduction)
        );
    }

    /**
     * View timeline
     *
     * @param int | string $courseIdentifier   Course id | slug
     * @param int          $displayLevel       Display level
     * @param int | string $categoryIdentifier Category id | slug
     * @param int | string $partIdentifier     Current part id | slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache (namespacePrefix="claire_app_course_course", namespaceAttribute="courseIdentifier", lifetime=0)
     */
    public function viewTimelineAction(
        $courseIdentifier,
        $displayLevel,
        $categoryIdentifier,
        $partIdentifier = null
    )
    {
        $toc = $this->get('simple_it.claire.course.course')->getToc($courseIdentifier);

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:viewTimeline.html.twig',
            array(
                'toc'                => $toc,
                'displayLevel'       => $displayLevel,
                'partIdentifier'     => $partIdentifier,
                'courseIdentifier'   => $courseIdentifier,
                'categoryIdentifier' => $categoryIdentifier
            )
        );
    }

    /**
     * View table of content
     *
     * @param int | string $courseIdentifier   Course id | slug
     * @param int          $displayLevel       Display level
     * @param int | string $categoryIdentifier Category id | slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache (namespacePrefix="claire_app_course_course", namespaceAttribute="courseIdentifier", lifetime=0)
     */
    public function viewTocAction($courseIdentifier, $displayLevel, $categoryIdentifier)
    {
        $toc = $this->get('simple_it.claire.course.course')->getToc($courseIdentifier);

        if ($displayLevel == CourseResource::DISPLAY_LEVEL_MEDIUM) {
            $template = 'SimpleITClaireAppBundle:Course/Course/Component:viewTocMedium.html.twig';
        } else {
            $template = 'SimpleITClaireAppBundle:Course/Course/Component:viewTocBig.html.twig';
        }

        return $this->render(
            $template,
            array(
                'toc'                => $toc,
                'displayLevel'       => $displayLevel,
                'courseIdentifier'   => $courseIdentifier,
                'categoryIdentifier' => $categoryIdentifier
            )
        );
    }

    /**
     * View TocAside
     *
     * @param int | string $courseIdentifier   Course id | slug
     * @param int          $displayLevel       Display level
     * @param int | string $categoryIdentifier Category id | slug
     * @param int | string $partIdentifier     Current part id | slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache (namespacePrefix="claire_app_course_course", namespaceAttribute="courseIdentifier", lifetime=0)
     */
    public function viewTocAsideAction(
        $courseIdentifier,
        $displayLevel,
        $categoryIdentifier,
        $partIdentifier = null
    )
    {
        $toc = $this->get('simple_it.claire.course.course')->getToc($courseIdentifier);

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:viewTocAside.html.twig',
            array(
                'toc'                => $toc,
                'displayLevel'       => $displayLevel,
                'partIdentifier'     => $partIdentifier,
                'courseIdentifier'   => $courseIdentifier,
                'categoryIdentifier' => $categoryIdentifier
            )
        );
    }

    /**
     * View pagination
     *
     * @param int | string $courseIdentifier   Course id | slug
     * @param int | string $categoryIdentifier Category id | slug
     * @param int | string $partIdentifier     Part id | slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache (namespacePrefix="claire_app_course_course", namespaceAttribute="courseIdentifier", lifetime=0)
     */
    public function viewPaginationAction(
        $courseIdentifier,
        $categoryIdentifier,
        $partIdentifier = null
    )
    {
        $pagination = $this->get('simple_it.claire.course.course')->getPagination(
            $courseIdentifier,
            $partIdentifier
        );

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:viewPagination.html.twig',
            array(
                'courseIdentifier'   => $courseIdentifier,
                'categoryIdentifier' => $categoryIdentifier,
                'previous'           => $pagination['previous'],
                'next'               => $pagination['next']
            )
        );
    }

    /**
     * View content
     *
     * @param int | string $courseIdentifier Course id | slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Cache (namespacePrefix="claire_app_course_course", namespaceAttribute="courseIdentifier", lifetime=0)
     */
    public function viewContentAction($courseIdentifier)
    {
        $content = $this->get('simple_it.claire.course.course')->getContent($courseIdentifier);

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:viewContent.html.twig',
            array('content' => $content)
        );
    }

    /**
     * Edit a course
     *
     * @param Request $request  Request
     * @param int     $courseId Course id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $courseId)
    {
        $parameters = $request->attributes->all();
        $course = $this->get('simple_it.claire.course.course')->getCourseToEdit(
            $courseId,
            $parameters
        );

        return $this->render(
            'SimpleITClaireAppBundle:Course/Course/Component:edit.html.twig',
            array('course' => $course)
        );
    }

    /**
     * Edit a course status to waiting for publication
     *
     * @param Request $request  Request
     * @param int     $courseId Course id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function editStatusToWaitingForPublicationAction(Request $request, $courseId)
    {
        $initialStatus = $request->get(CourseResource::STATUS);
        if (is_null($initialStatus)) {
            throw new HttpException(400);
        }
        $this->get('simple_it.claire.course.course')->changeStatus(
            $courseId,
            $initialStatus,
            CourseResource::STATUS_WAITING_FOR_PUBLICATION
        );

        return new Response();
    }

    /**
     * Edit a course status
     *
     * @param Request $request  Request
     * @param int     $courseId Course id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function editStatusToPublishedAction(Request $request, $courseId)
    {
        $initialStatus = $request->get(CourseResource::STATUS);
        if (is_null($initialStatus)) {
            throw new HttpException(400);
        }
        $this->get('simple_it.claire.course.course')->changeStatus(
            $courseId,
            $initialStatus,
            CourseResource::STATUS_PUBLISHED
        );

        return new Response();
    }
}
