<?php

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseModel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\SerializationContext;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModelMetadataFactory;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidModelException;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\KnowledgeServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ClassificationConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Group;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Model as GroupItems;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\ObjectBlock as GIObjectBlock;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\Model as MultipleChoice;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\QuestionBlock as MCQuestionBlock;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\Model as OpenEnded;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\QuestionBlock as OEQuestionBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\Model as OrderItems;
use
    SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\ObjectBlock as OIObjectBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\Model as PairItems;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\PairBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId;
use SimpleIT\CoreBundle\Exception\NonExistingObjectException;
use SimpleIT\CoreBundle\Services\TransactionalService;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModelFactory;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Exception\NoAuthorException;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\ExerciseModel\ExerciseModelRepository;
use
    SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseResource\ExerciseResourceServiceInterface;
use SimpleIT\ClaireExerciseBundle\Service\User\UserService;
use SimpleIT\Utils\Collection\CollectionInformation;
use SimpleIT\Utils\Collection\PaginatorInterface;
use SimpleIT\CoreBundle\Annotation\Transactional;

/**
 * Service which manages the exercise generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModelService extends TransactionalService implements ExerciseModelServiceInterface
{
    /**
     * @var ExerciseModelRepository $exerciseModelRepository
     */
    private $exerciseModelRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ExerciseResourceServiceInterface
     */
    private $exerciseResourceService;

    /**
     * @var KnowledgeServiceInterface
     */
    private $knowledgeService;

    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Set exerciseModelRepository
     *
     * @param ExerciseModelRepository $exerciseModelRepository
     */
    public function setExerciseModelRepository($exerciseModelRepository)
    {
        $this->exerciseModelRepository = $exerciseModelRepository;
    }

    /**
     * Set userService
     *
     * @param UserService $userService
     */
    public function setUserService($userService)
    {
        $this->userService = $userService;
    }

    /**
     * Set exerciseResourceService
     *
     * @param ExerciseResourceServiceInterface $exerciseResourceService
     */
    public function setExerciseResourceService($exerciseResourceService)
    {
        $this->exerciseResourceService = $exerciseResourceService;
    }

    /**
     * Set metadataService
     *
     * @param \SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseModel\MetadataService $metadataService
     */
    public function setMetadataService($metadataService)
    {
        $this->metadataService = $metadataService;
    }

    /**
     * Set knowledgeService
     *
     * @param \SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge\KnowledgeServiceInterface $knowledgeService
     */
    public function setKnowledgeService($knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Get an Exercise Model entity
     *
     * @param int $exerciseModelId
     *
     * @return ExerciseModel
     * @throws NonExistingObjectException
     */
    public function get($exerciseModelId)
    {
        $exerciseModel = $this->exerciseModelRepository->find($exerciseModelId);
        if (is_null($exerciseModel)) {
            throw new NonExistingObjectException();
        }

        return $exerciseModel;
    }

    /**
     * Get an exercise Model (business object, no entity)
     *
     * @param int $exerciseModelId
     *
     * @return object
     * @throws \LogicException
     */
    public function getModel($exerciseModelId)
    {
        $entity = $this->get($exerciseModelId);

        return $this->getModelFromEntity($entity);

    }

    /**
     * Get an exercise model from an entity
     *
     * @param ExerciseModel $entity
     *
     * @return CommonModel
     * @throws \LogicException
     */
    public function getModelFromEntity(ExerciseModel $entity)
    {
        // deserialize to get an object
        switch ($entity->getType()) {
            case CommonExercise::MULTIPLE_CHOICE:
                $class = ExerciseModelResource::MULTIPLE_CHOICE_MODEL_CLASS;
                break;
            case CommonExercise::GROUP_ITEMS:
                $class = ExerciseModelResource::GROUP_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::ORDER_ITEMS:
                $class = ExerciseModelResource::ORDER_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::PAIR_ITEMS:
                $class = ExerciseModelResource::PAIR_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::OPEN_ENDED_QUESTION:
                $class = ExerciseModelResource::OPEN_ENDED_QUESTION_CLASS;
                break;
            default:
                throw new \LogicException('Unknown type of model');
        }

        return $this->serializer->jmsDeserialize($entity->getContent(), $class, 'json');
    }

    /**
     * Get a list of Exercise Model
     *
     * @param CollectionInformation $collectionInformation The collection information
     * @param int                   $ownerId
     * @param int                   $authorId
     * @param int                   $parentModelId
     * @param int                   $forkFromModelId
     * @param boolean               $isRoot
     * @param boolean               $isPointer
     *
     * @return PaginatorInterface
     */
    public function getAll(
        $collectionInformation = null,
        $ownerId = null,
        $authorId = null,
        $parentModelId = null,
        $forkFromModelId = null,
        $isRoot = null,
        $isPointer = null
    )
    {
        $owner = null;
        if (!is_null($ownerId)) {
            $owner = $this->userService->get($ownerId);
        }

        $author = null;
        if (!is_null($authorId)) {
            $author = $this->userService->get($authorId);
        }

        $parentModel = null;
        if (!is_null($parentModelId)) {
            $parentModel = $this->get($parentModelId);
        }

        $forkFromModel = null;
        if (!is_null($forkFromModelId)) {
            $forkFromModel = $this->get($forkFromModelId);
        }

        return $this->exerciseModelRepository->findAll(
            $collectionInformation,
            $owner,
            $author,
            $parentModel,
            $forkFromModel,
            $isRoot,
            $isPointer
        );
    }

    /**
     * Create an ExerciseModel entity from a resource
     *
     * @param ExerciseModelResource $modelResource
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NoAuthorException
     * @return ExerciseModel
     */
    public function createFromResource(
        ExerciseModelResource $modelResource
    )
    {
        $modelResource->setComplete(
            $this->checkModelComplete(
                $modelResource->getType(),
                $modelResource->getParent(),
                $modelResource->getContent()
            )
        );

        $model = ExerciseModelFactory::createFromResource($modelResource);

        // author
        if (is_null($modelResource->getAuthor())) {
            throw new NoAuthorException();
        } else {
            $authorId = $modelResource->getAuthor();
        }
        $model->setAuthor(
            $this->userService->get($authorId)
        );

        // owner
        if (is_null($modelResource->getOwner())) {
            throw new NoAuthorException('No owner for this model...');
        } else {
            $ownerId = $modelResource->getOwner();
        }
        $model->setOwner(
            $this->userService->get($ownerId)
        );

        // parent model
        if (!is_null($modelResource->getParent())) {
            $model->setParent(
                $this->get($modelResource->getParent())
            );
        }

        // fork from
        if (!is_null($modelResource->getForkFrom())) {
            $model->setForkFrom(
                $this->get($modelResource->getForkFrom())
            );
        }

        // required resources
        $reqResources = array();
        foreach ($modelResource->getRequiredExerciseResources() as $reqRes) {
            $reqResources[] = $this->exerciseResourceService->get($reqRes);
        }
        $model->setRequiredExerciseResources(new ArrayCollection($reqResources));

        // required resources
        $reqKnowledges = array();
        foreach ($modelResource->getRequiredKnowledges() as $reqKnowledge) {
            $reqKnowledges[] = $this->knowledgeService->get($reqKnowledge);
        }
        $model->setRequiredKnowledges(new ArrayCollection($reqKnowledges));

        // metadata
        $metadata = array();
        $resMetadata = $modelResource->getMetadata();
        if (!empty($resMetadata)) {
            foreach ($resMetadata as $key => $value) {
                $md = ExerciseModelMetadataFactory::create($key, $value);
                $md->setExerciseModel($model);
                $metadata[] = $md;
            }
        }
        $model->setMetadata(new ArrayCollection($metadata));

        return $model;
    }

    /**
     * Create and add an exercise model from a resource
     *
     * @param ExerciseModelResource $modelResource
     *
     * @return ExerciseModel
     */
    public function createAndAdd(
        ExerciseModelResource $modelResource
    )
    {
        $model = $this->createFromResource($modelResource);

        return $this->add($model);
    }

    /**
     * Add a model from a Resource
     *
     * @param ExerciseModel $model
     *
     * @return ExerciseModel
     * @Transactional
     */
    public function add(
        ExerciseModel $model
    )
    {
        $this->exerciseModelRepository->insert($model);

        return $model;
    }

    /**
     * Update an ExerciseResource object from a ResourceResource
     *
     * @param ExerciseModelResource $modelResource
     * @param ExerciseModel         $model
     *
     * @throws NoAuthorException
     * @return ExerciseModel
     */
    public function updateFromResource(
        ExerciseModelResource $modelResource,
        $model
    )
    {
        if (!is_null($modelResource->getRequiredExerciseResources())) {
            $reqResources = array();
            foreach ($modelResource->getRequiredExerciseResources() as $reqRes) {
                $reqResources[] = $this->exerciseResourceService->get($reqRes);
            }
            $model->setRequiredExerciseResources(new ArrayCollection($reqResources));
        }

        if (!is_null($modelResource->getRequiredKnowledges())) {
            $reqKnowledges = array();
            foreach ($modelResource->getRequiredKnowledges() as $reqKnowledge) {
                $reqKnowledges[] = $this->knowledgeService->get($reqKnowledge);
            }
            $model->setRequiredKnowledges(new ArrayCollection($reqKnowledges));
        }

        if (!is_null($modelResource->getTitle())) {
            $model->setTitle($modelResource->getTitle());
        }

        if (!is_null($modelResource->getType())) {
            $model->setType($modelResource->getType());
        }

        if (!is_null($modelResource->getDraft())) {
            $model->setDraft($modelResource->getDraft());
        }

        if (!is_null($modelResource->getComplete())) {
            $model->setComplete($modelResource->getComplete());
        }

        if (!is_null($modelResource->getPublic())) {
            $model->setPublic($modelResource->getPublic());
        }

        $content = $modelResource->getContent();
        if (!is_null($content)) {
            $this->validateType($content, $model->getType());
            $context = SerializationContext::create();
            $context->setGroups(array('exercise_model_storage', 'Default'));
            $model->setContent(
                $this->serializer->jmsSerialize($content, 'json', $context)
            );

            if ($model->getParent() === null) {
                $parentId = null;
            } else {
                $parentId = $model->getParent()->getId();
            }
            // Check if the model is complete with the new content
            $model->setComplete(
                $this->checkModelComplete(
                    $model->getType(),
                    $parentId,
                    $content
                )
            );
        }

        return $model;
    }

    /**
     * Save a resource given in form of a ResourceResource
     *
     * @param ExerciseModelResource $modelResource
     * @param int                   $modelId
     *
     * @return ExerciseModel
     */
    public function edit(
        ExerciseModelResource $modelResource,
        $modelId
    )
    {
        $model = $this->get($modelId);
        $model = $this->updateFromResource(
            $modelResource,
            $model
        );

        return $this->save($model);
    }

    /**
     * Save a resource
     *
     * @param ExerciseModel $model
     *
     * @return ExerciseModel
     * @Transactional
     */
    public function save(ExerciseModel $model)
    {
        return $this->exerciseModelRepository->update($model);
    }

    /**
     * Delete a resource
     *
     * @param $modelId
     *
     * @Transactional
     */
    public function remove($modelId)
    {
        $resource = $this->exerciseModelRepository->find($modelId);
        $this->exerciseModelRepository->delete($resource);
    }

    /**
     * Edit all the metadata of an exercise model
     *
     * @param int             $exerciseModelId
     * @param ArrayCollection $metadatas
     *
     * @return Collection
     * @Transactional
     */
    public function editMetadata($exerciseModelId, ArrayCollection $metadatas)
    {
        $exerciseModel = $this->exerciseModelRepository->find($exerciseModelId);

        $this->metadataService->deleteAllByExerciseModel($exerciseModelId);

        $metadataCollection = array();
        foreach ($metadatas as $key => $value) {
            $md = ExerciseModelMetadataFactory::create($key, $value);
            $md->setExerciseModel($exerciseModel);
            $metadataCollection[] = $md;
        }
        $exerciseModel->setMetadata(new ArrayCollection($metadataCollection));

        return $this->save($exerciseModel)->getMetadata();
    }

    /**
     * Add a requiredResource to an exercise model
     *
     * @param $exerciseModelId
     * @param $reqResId
     *
     * @return ExerciseModel
     */
    public function addRequiredResource(
        $exerciseModelId,
        $reqResId
    )
    {
        $reqRes = $this->exerciseResourceService->get($reqResId);
        $this->exerciseModelRepository->addRequiredResource($exerciseModelId, $reqRes);

        return $this->get($exerciseModelId);
    }

    /**
     * Delete a required resource
     *
     * @param $exerciseModelId
     * @param $reqResId
     *
     * @return ExerciseModel
     */
    public function deleteRequiredResource(
        $exerciseModelId,
        $reqResId
    )
    {
        $reqRes = $this->exerciseResourceService->get($reqResId);
        $this->exerciseModelRepository->deleteRequiredResource($exerciseModelId, $reqRes);
    }

    /**
     * Edit the required resources
     *
     * @param int             $exerciseModelId
     * @param ArrayCollection $requiredResources
     *
     * @return ExerciseModel
     */
    public function editRequiredResource(
        $exerciseModelId,
        ArrayCollection $requiredResources
    )
    {
        $exerciseModel = $this->exerciseModelRepository->find($exerciseModelId);

        $resourcesCollection = array();
        foreach ($requiredResources as $rr) {
            $resourcesCollection[] = $this->exerciseResourceService->get($rr);
        }
        $exerciseModel->setRequiredExerciseResources(new ArrayCollection($resourcesCollection));

        return $this->save($exerciseModel)->getRequiredExerciseResources();
    }

    /**
     * Add a required knowledge to an exercise model
     *
     * @param $exerciseModelId
     * @param $reqKnoId
     *
     * @return ExerciseModel
     */
    public function addRequiredKnowledge(
        $exerciseModelId,
        $reqKnoId
    )
    {
        $reqKno = $this->knowledgeService->get($reqKnoId);
        $this->exerciseModelRepository->addRequiredKnowledge($exerciseModelId, $reqKno);

        return $this->get($exerciseModelId);
    }

    /**
     * Delete a required knowledge
     *
     * @param $exerciseModelId
     * @param $reqKnoId
     *
     * @return ExerciseModel
     */
    public function deleteRequiredKnowledge(
        $exerciseModelId,
        $reqKnoId
    )
    {
        $reqKno = $this->knowledgeService->get($reqKnoId);
        $this->exerciseModelRepository->deleteRequiredKnowledge($exerciseModelId, $reqKno);
    }

    /**
     * Edit the required knowledges
     *
     * @param int             $exerciseModelId
     * @param ArrayCollection $requiredKnowledges
     *
     * @return ExerciseModel
     */
    public function editRequiredKnowledges(
        $exerciseModelId,
        ArrayCollection $requiredKnowledges
    )
    {
        $exerciseModel = $this->exerciseModelRepository->find($exerciseModelId);

        $reqKnowledgeCollection = array();
        foreach ($requiredKnowledges as $rk) {
            $reqKnowledgeCollection[] = $this->knowledgeService->get($rk);
        }
        $exerciseModel->setRequiredKnowledges(new ArrayCollection($reqKnowledgeCollection));

        return $this->save($exerciseModel)->getRequiredKnowledges();
    }

    /**
     * Check if the content of an exercise model is sufficient to generate exercises.
     *
     * @param string      $type
     * @param int         $parentModelId
     * @param CommonModel $content
     *
     * @throws \LogicException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidModelException
     * @return boolean True if the model is complete
     */
    private function checkModelComplete(
        $type,
        $parentModelId,
        $content
    )
    {
        if ($parentModelId === null) {
            switch ($type) {
                case CommonModel::MULTIPLE_CHOICE:
                    /** @var MultipleChoice $content */
                    return $this->checkMCComplete($content);
                    break;
                case CommonModel::PAIR_ITEMS:
                    /** @var PairItems $content */
                    return $this->checkPIComplete($content);
                    break;
                case CommonModel::GROUP_ITEMS:
                    /** @var GroupItems $content */
                    return $this->checkGIComplete($content);
                    break;
                case CommonModel::ORDER_ITEMS:
                    /** @var OrderItems $content */
                    return $this->checkOIComplete($content);
                    break;
                case CommonModel::OPEN_ENDED_QUESTION:
                    /** @var OpenEnded $content */
                    return $this->checkOEQComplete($content);
                    break;
                default:
                    throw new \LogicException('Invalid type');
            }
        } else {
            if ($content !== null) {
                throw new InvalidModelException('A model must be a pointer OR have a content');
            }
            try {

                $parentModel = $this->get($parentModelId);
            } catch (NonExistingObjectException $neoe) {
                throw new InvalidModelException('The parent model cannot be found.');
            }

            return $parentModel->getPublic();
        }
    }

    /**
     * Check if a multiple choice content is complete
     *
     * @param MultipleChoice $content
     *
     * @return boolean
     */
    private function checkMCComplete(
        MultipleChoice $content
    )
    {
        if (is_null($content->isShuffleQuestionsOrder())) {
            return false;
        }
        $questionBlocks = $content->getQuestionBlocks();
        if (!count($questionBlocks) > 0) {
            return false;
        }
        /** @var MCQuestionBlock $questionBlock */
        foreach ($questionBlocks as $questionBlock) {
            if (!($questionBlock->getMaxNumberOfPropositions() >= 0
                && $questionBlock->getMaxNumberOfRightPropositions() >= 0)
            ) {
                return false;
            }

            if (!$this->checkBlockComplete(
                $questionBlock,
                array(CommonResource::MULTIPLE_CHOICE_QUESTION)
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a pair items content is complete
     *
     * @param PairItems $content
     *
     * @return bool
     */
    private function checkPIComplete(
        PairItems $content
    )
    {
        $pairBlocks = $content->getPairBlocks();
        if (!count($pairBlocks) > 0) {
            return false;
        }

        /** @var PairBlock $pairBlock */
        foreach ($pairBlocks as $pairBlock) {
            if ($pairBlock->getPairMetaKey() == null) {
                return false;
            }

            if (!$this->checkBlockComplete(
                $pairBlock,
                array(
                    CommonResource::PICTURE,
                    CommonResource::TEXT
                )
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a group items model is complete
     *
     * @param GroupItems $content
     *
     * @return bool
     */
    private function checkGIComplete(
        GroupItems $content
    )
    {
        if ($content->getDisplayGroupNames() != GroupItems::ASK
            && $content->getDisplayGroupNames() != GroupItems::HIDE
            && $content->getDisplayGroupNames() != GroupItems::SHOW
        ) {
            return false;
        }

        $globalClassification = false;
        if ($content->getClassifConstr() != null) {
            if (!$this->checkClassifConstr($content->getClassifConstr())) {
                return false;
            }
            $globalClassification = true;
        }

        $objectBlocks = $content->getObjectBlocks();
        if (!count($objectBlocks) > 0) {
            return false;
        }

        /** @var GIObjectBlock $objectBlock */
        foreach ($objectBlocks as $objectBlock) {
            if (!$globalClassification &&
                (
                    $objectBlock->getClassifConstr() == null
                    || !$this->checkClassifConstr($objectBlock->getClassifConstr())
                )
            ) {
                return false;
            }

            if (!$this->checkBlockComplete(
                $objectBlock,
                array(
                    CommonResource::TEXT,
                    CommonResource::PICTURE
                )
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an order items model is complete
     *
     * @param OrderItems $content
     *
     * @return bool
     */
    private function checkOIComplete(
        OrderItems $content
    )
    {
        if ($content->isGiveFirst() === null || $content->isGiveLast() === null) {
            return false;
        }

        $sequenceBlock = $content->getSequenceBlock();
        $objectBlocks = $content->getObjectBlocks();
        // both cannot be empty or filled
        if (empty($sequenceBlock) == empty($objectBlocks)) {
            return false;
        }

        if ($sequenceBlock !== null) {
            if ($sequenceBlock->isKeepAll() === null) {
                return false;
            }

            if (!$sequenceBlock->isKeepAll() &&
                ($sequenceBlock->isUseFirst() === null || $sequenceBlock->isUseLast() === null)
            ) {
                return false;
            }

            if (!$this->checkBlockComplete($sequenceBlock, array(CommonResource::SEQUENCE))) {
                return false;
            }
        } else {
            if ($content->getOrder() != OrderItems::ASCENDENT
                && $content->getOrder() != OrderItems::DESCENDENT
            ) {
                return false;
            }

            if (is_null($content->getShowValues())) {
                return false;
            }

            /** @var OIObjectBlock $objectBlock */
            foreach ($objectBlocks as $objectBlock) {
                if ($objectBlock->getMetaKey() === null) {
                    return false;
                }

                if (
                !$this->checkBlockComplete(
                    $objectBlock,
                    array(
                        CommonResource::PICTURE,
                        CommonResource::TEXT
                    )
                )
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if an open ended question model is complete
     *
     * @param OpenEnded $content
     *
     * @return bool
     */
    private function checkOEQComplete(
        OpenEnded $content
    )
    {
        if (is_null($content->isShuffleQuestionsOrder())) {
            return false;
        }
        $questionBlocks = $content->getQuestionBlocks();
        if (!count($questionBlocks) > 0) {
            return false;
        }

        /** @var OEQuestionBlock $questionBlock */
        foreach ($questionBlocks as $questionBlock) {
            if (!$this->checkBlockComplete(
                $questionBlock,
                array(CommonResource::OPEN_ENDED_QUESTION)
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a resource block is complete
     *
     * @param ResourceBlock $block
     * @param array         $resourceTypes
     *
     * @return boolean
     */
    private function checkBlockComplete(
        ResourceBlock $block,
        array $resourceTypes
    )
    {
        if (!($block->getNumberOfOccurrences() >= 0)) {
            return false;
        }

        if (count($block->getResources()) == 0 && $block->getResourceConstraint() === null) {
            return false;
        }

        /** @var ObjectId $resource */
        foreach ($block->getResources() as $resource) {
            if (!$this->checkObjectId($resource, $resourceTypes)) {
                return false;
            }
        }

        if ($block->getResourceConstraint() !== null
            && !$this->checkConstraintsComplete($block->getResourceConstraint(), $resourceTypes)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if and object constraints object is complete
     *
     * @param ObjectConstraints $resourceConstraints
     * @param array             $resourceTypes
     *
     * @return boolean
     */
    private function checkConstraintsComplete(
        ObjectConstraints $resourceConstraints,
        array $resourceTypes = array()
    )
    {
        if (!empty($resourceTypes) && !is_null($resourceConstraints->getType()) &&
            !in_array($resourceConstraints->getType(), $resourceTypes)
        ) {
            return false;
        }
        if (count($resourceConstraints->getMetadataConstraints()) == 0) {
            return false;
        }

        /** @var MetadataConstraint $mdc */
        foreach ($resourceConstraints->getMetadataConstraints() as $mdc) {
            if (!$this->checkMetadataConstraintComplete($mdc)) {
                return false;
            }
        }

        /** @var ObjectId $excluded */
        foreach ($resourceConstraints->getExcluded() as $excluded) {
            if (!$this->checkObjectId($excluded)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an obect Id is valid (and exists)
     *
     * @param ObjectId $objectId
     * @param array    $resourceTypes
     *
     * @return bool
     */
    private function checkObjectId(
        ObjectId $objectId,
        array $resourceTypes = array()
    )
    {
        if (is_null($objectId->getId())) {
            return false;
        }
        try {
            $resource = $this->exerciseResourceService->get($objectId->getId());
        } catch (NonExistingObjectException $neoe) {
            return false;
        }

        if (!empty($resourceTypes) && !in_array($resource->getType(), $resourceTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a metadata constraint is complete
     *
     * @param MetadataConstraint $mdc
     *
     * @return bool
     */
    private function checkMetadataConstraintComplete(
        MetadataConstraint $mdc
    )
    {
        if ($mdc->getKey() == null || $mdc->getComparator() == null) {
            return false;
        }

        return true;
    }

    /**
     * Check if a classification constraint is complete
     *
     * @param ClassificationConstraints $classifConstr
     *
     * @return bool
     */
    private function checkClassifConstr(
        ClassificationConstraints $classifConstr
    )
    {
        if ($classifConstr->getOther() != ClassificationConstraints::MISC
            && $classifConstr->getOther() != ClassificationConstraints::OWN
            && $classifConstr->getOther() != ClassificationConstraints::REJECT
        ) {
            return false;
        }

        if (count($classifConstr->getMetaKeys()) == 0) {
            return false;
        }

        /** @var Group $group */
        foreach ($classifConstr->getGroups() as $group) {
            $name = $group->getName();
            if (empty($name)) {
                return false;
            }

            if (count($group->getMDConstraints()) == 0) {
                return false;
            }

            /** @var MetadataConstraint $mdc */
            foreach ($group->getMDConstraints() as $mdc) {
                if (!$this->checkMetadataConstraintComplete($mdc)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Throws an exception if the content does not match the type
     *
     * @param $content
     * @param $type
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     */
    private function validateType(
        $content,
        $type
    )
    {
        if (($type === CommonModel::MULTIPLE_CHOICE &&
                get_class($content) !== ExerciseModelResource::MULTIPLE_CHOICE_MODEL_CLASS)
            || ($type === CommonModel::GROUP_ITEMS
                && get_class($content) !== ExerciseModelResource::GROUP_ITEMS_MODEL_CLASS)
            || ($type === CommonModel::ORDER_ITEMS &&
                get_class($content) !== ExerciseModelResource::ORDER_ITEMS_MODEL_CLASS)
            || ($type === CommonModel::PAIR_ITEMS &&
                get_class($content) !== ExerciseModelResource::PAIR_ITEMS_MODEL_CLASS)
            || ($type === CommonModel::OPEN_ENDED_QUESTION &&
                get_class($content) !== ExerciseModelResource::OPEN_ENDED_QUESTION_CLASS)
        ) {
            throw new InvalidTypeException('Content does not match exercise model type');
        }
    }
}
