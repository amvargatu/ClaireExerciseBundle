<?php
namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\CommonKnowledge;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class KnowledgeResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class KnowledgeResource extends SharedResource
{
    /**
     * @const RESOURCE_NAME = 'Knowledge'
     */
    const RESOURCE_NAME = 'Knowledge';

    /**
     * @const MULTIPLE_CHOICE_QUESTION = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoiceQuestionResource'
     */
    const FORMULA_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula';

    /**
     * @var int $id Id of knowledge
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create","edit"})
     */
    protected $id;

    /**
     * @var string $type
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Blank(groups={"edit"})
     */
    protected $type;

    /**
     * @var string $title
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\NotBlank(groups={"create"})
     */
    protected $title;

    /**
     * @var CommonKnowledge $content
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\CommonKnowledge")
     * @Serializer\Groups({"details"})
     * @Assert\Blank(groups={"appCreate"})
     * @Assert\Valid
     */
    protected $content;

    /**
     * @var boolean $draft
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Serializer\Type("boolean")
     * @Assert\NotNull(groups={"create"})
     */
    protected $draft;

    /**
     * @var boolean $complete
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Serializer\Type("boolean")
     * @Assert\Null(groups={"create"})
     */
    protected $complete;

    /**
     * @var array $requiredKnowledges
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     * @Assert\Null()
     */
    private $requiredKnowledges;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $author;

    /**
     * @var int $owner
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $owner;

    /**
     * @var bool $public
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list", "knowledge_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $public;

    /**
     * @var bool $archived
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list", "knowledge_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $archived;

    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource>")
     * @Serializer\Groups({"details"})
     */
    protected $metadata;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     */
    protected $keywords;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details"})
     */
    protected $parent;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "resource_list"})
     */
    protected $forkFrom;

    /**
     * Set content
     *
     * @param CommonKnowledge $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return CommonKnowledge
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set requiredKnowledges
     *
     * @param array $requiredKnowledges
     */
    public function setRequiredKnowledges($requiredKnowledges)
    {
        $this->requiredKnowledges = $requiredKnowledges;
    }

    /**
     * Get requiredKnowledges
     *
     * @return array
     */
    public function getRequiredKnowledges()
    {
        return $this->requiredKnowledges;
    }

    /**
     * Return the item serialization class corresponding to the type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    public function getSerializationClass($type)
    {
        switch ($type) {
            case CommonKnowledge::FORMULA:
                $class = self::FORMULA_CLASS;
                break;
            default:
                throw new \LogicException('Unknown type');
        }

        return $class;
    }

    /**
     * Return the item serialization class corresponding to the type of the object
     *
     * @return string
     * @throws \LogicException
     */
    public function getClass(){
        return self::getSerializationClass($this->type);
    }
}
