<?php
/**
 * Comments.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 * @link https://homepages-mit-zikula.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

namespace MU\CommentsModule\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;
use MU\CommentsModule\Traits\StandardFieldsTrait;
use MU\CommentsModule\Validator\Constraints as CommentsAssert;

/**
 * Entity class that defines the entity structure and behaviours.
 *
 * This is the base entity class for comment entities.
 * The following annotation marks it as a mapped superclass so subclasses
 * inherit orm properties.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractCommentEntity extends EntityAccess
{
    /**
     * Hook standard fields behaviour embedding createdBy, updatedBy, createdDate, updatedDate fields.
     */
    use StandardFieldsTrait;

    /**
     * @var string The tablename this object maps to
     */
    protected $_objectType = 'comment';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", unique=true)
     * @var integer $id
     */
    protected $id = 0;
    
    /**
     * the current workflow state
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     * @CommentsAssert\ListEntry(entityName="comment", propertyName="workflowState", multiple=false)
     * @var string $workflowState
     */
    protected $workflowState = 'initial';
    
    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="255")
     * @var string $title
     */
    protected $title = '';
    
    /**
     * @ORM\Column(length=255)
     * @Assert\NotNull()
     * @Assert\Length(min="0", max="255")
     * @var string $name
     */
    protected $name = '';
    
    /**
     * @ORM\Column(length=255)
     * @Assert\NotNull()
     * @Assert\Length(min="0", max="255")
     * @var string $yourMailAddress
     */
    protected $yourMailAddress = '';
    
    /**
     * @ORM\Column(length=255)
     * @Assert\NotNull()
     * @Assert\Length(min="0", max="255")
     * @Assert\Url(checkDNS=false)
     * @var string $homepage
     */
    protected $homepage = '';
    
    /**
     * @ORM\Column(type="text", length=20000)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="20000")
     * @var text $text
     */
    protected $text = '';
    
    /**
     * @ORM\Column(type="integer")
     * @var integer $parentid
     */
    protected $parentid = 0;
    
    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\NotNull()
     * @Assert\LessThan(value=100000000000)
     * @var integer $mainId
     */
    protected $mainId = 0;
    
    
    /**
     * Unidirectional - One comment [comment] has many comments [comments] (INVERSE SIDE).
     *
     * @ORM\ManyToMany(targetEntity="MU\CommentsModule\Entity\CommentEntity")
     * @ORM\JoinTable(name="mu_comments_commentcomments",
     *      joinColumns={@ORM\JoinColumn(name="id", referencedColumnName="id" )},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parentid", referencedColumnName="id" )}
     * )
     * @var \MU\CommentsModule\Entity\CommentEntity[] $comments
     */
    protected $comments = null;
    
    
    /**
     * CommentEntity constructor.
     *
     * Will not be called by Doctrine and can therefore be used
     * for own implementation purposes. It is also possible to add
     * arbitrary arguments as with every other class method.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }
    
    /**
     * Returns the _object type.
     *
     * @return string
     */
    public function get_objectType()
    {
        return $this->_objectType;
    }
    
    /**
     * Sets the _object type.
     *
     * @param string $_objectType
     *
     * @return void
     */
    public function set_objectType($_objectType)
    {
        if ($this->_objectType != $_objectType) {
            $this->_objectType = $_objectType;
        }
    }
    
    
    /**
     * Returns the id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets the id.
     *
     * @param integer $id
     *
     * @return void
     */
    public function setId($id)
    {
        if (intval($this->id) !== intval($id)) {
            $this->id = intval($id);
        }
    }
    
    /**
     * Returns the workflow state.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->workflowState;
    }
    
    /**
     * Sets the workflow state.
     *
     * @param string $workflowState
     *
     * @return void
     */
    public function setWorkflowState($workflowState)
    {
        if ($this->workflowState !== $workflowState) {
            $this->workflowState = isset($workflowState) ? $workflowState : '';
        }
    }
    
    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle($title)
    {
        if ($this->title !== $title) {
            $this->title = isset($title) ? $title : '';
        }
    }
    
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        if ($this->name !== $name) {
            $this->name = isset($name) ? $name : '';
        }
    }
    
    /**
     * Returns the your mail address.
     *
     * @return string
     */
    public function getYourMailAddress()
    {
        return $this->yourMailAddress;
    }
    
    /**
     * Sets the your mail address.
     *
     * @param string $yourMailAddress
     *
     * @return void
     */
    public function setYourMailAddress($yourMailAddress)
    {
        if ($this->yourMailAddress !== $yourMailAddress) {
            $this->yourMailAddress = isset($yourMailAddress) ? $yourMailAddress : '';
        }
    }
    
    /**
     * Returns the homepage.
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }
    
    /**
     * Sets the homepage.
     *
     * @param string $homepage
     *
     * @return void
     */
    public function setHomepage($homepage)
    {
        if ($this->homepage !== $homepage) {
            $this->homepage = isset($homepage) ? $homepage : '';
        }
    }
    
    /**
     * Returns the text.
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * Sets the text.
     *
     * @param text $text
     *
     * @return void
     */
    public function setText($text)
    {
        if ($this->text !== $text) {
            $this->text = isset($text) ? $text : '';
        }
    }
    
    /**
     * Returns the parentid.
     *
     * @return integer
     */
    public function getParentid()
    {
        return $this->parentid;
    }
    
    /**
     * Sets the parentid.
     *
     * @param integer $parentid
     *
     * @return void
     */
    public function setParentid($parentid)
    {
        if (intval($this->parentid) !== intval($parentid)) {
            $this->parentid = intval($parentid);
        }
    }
    
    /**
     * Returns the main id.
     *
     * @return integer
     */
    public function getMainId()
    {
        return $this->mainId;
    }
    
    /**
     * Sets the main id.
     *
     * @param integer $mainId
     *
     * @return void
     */
    public function setMainId($mainId)
    {
        if (intval($this->mainId) !== intval($mainId)) {
            $this->mainId = intval($mainId);
        }
    }
    
    
    /**
     * Returns the comments.
     *
     * @return \MU\CommentsModule\Entity\CommentEntity[]
     */
    public function getComments()
    {
        return $this->comments;
    }
    
    /**
     * Sets the comments.
     *
     * @param \MU\CommentsModule\Entity\CommentEntity[] $comments
     *
     * @return void
     */
    public function setComments($comments)
    {
        foreach ($this->comments as $commentSingle) {
            $this->removeComments($commentSingle);
        }
        foreach ($comments as $commentSingle) {
            $this->addComments($commentSingle);
        }
    }
    
    /**
     * Adds an instance of \MU\CommentsModule\Entity\CommentEntity to the list of comments.
     *
     * @param \MU\CommentsModule\Entity\CommentEntity $comment The instance to be added to the collection
     *
     * @return void
     */
    public function addComments(\MU\CommentsModule\Entity\CommentEntity $comment)
    {
        $this->comments->add($comment);
    }
    
    /**
     * Removes an instance of \MU\CommentsModule\Entity\CommentEntity from the list of comments.
     *
     * @param \MU\CommentsModule\Entity\CommentEntity $comment The instance to be removed from the collection
     *
     * @return void
     */
    public function removeComments(\MU\CommentsModule\Entity\CommentEntity $comment)
    {
        $this->comments->removeElement($comment);
    }
    
    
    
    /**
     * Creates url arguments array for easy creation of display urls.
     *
     * @return array The resulting arguments list
     */
    public function createUrlArgs()
    {
        return [
            'id' => $this->getId()
        ];
    }
    
    /**
     * Returns the primary key.
     *
     * @return integer The identifier
     */
    public function getKey()
    {
        return $this->getId();
    }
    
    /**
     * Determines whether this entity supports hook subscribers or not.
     *
     * @return boolean
     */
    public function supportsHookSubscribers()
    {
        return true;
    }
    
    /**
     * Return lower case name of multiple items needed for hook areas.
     *
     * @return string
     */
    public function getHookAreaPrefix()
    {
        return 'mucommentsmodule.ui_hooks.comments';
    }
    
    /**
     * Returns an array of all related objects that need to be persisted after clone.
     * 
     * @param array $objects The objects are added to this array. Default: []
     * 
     * @return array of entity objects
     */
    public function getRelatedObjectsToPersist(&$objects = []) 
    {
        return [];
    }
    
    /**
     * ToString interceptor implementation.
     * This method is useful for debugging purposes.
     *
     * @return string The output string for this entity
     */
    public function __toString()
    {
        return 'Comment ' . $this->getKey() . ': ' . $this->getTitle();
    }
    
    /**
     * Clone interceptor implementation.
     * This method is for example called by the reuse functionality.
     * Performs a quite simple shallow copy.
     *
     * See also:
     * (1) http://docs.doctrine-project.org/en/latest/cookbook/implementing-wakeup-or-clone.html
     * (2) http://www.php.net/manual/en/language.oop5.cloning.php
     * (3) http://stackoverflow.com/questions/185934/how-do-i-create-a-copy-of-an-object-in-php
     */
    public function __clone()
    {
        // if the entity has no identity do nothing, do NOT throw an exception
        if (!$this->id) {
            return;
        }
    
        // otherwise proceed
    
        // unset identifier
        $this->setId(0);
    
        // reset workflow
        $this->setWorkflowState('initial');
    
        $this->setCreatedBy(null);
        $this->setCreatedDate(null);
        $this->setUpdatedBy(null);
        $this->setUpdatedDate(null);
    
    }
}
