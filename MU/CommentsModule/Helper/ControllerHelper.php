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

namespace MU\CommentsModule\Helper;

use MU\CommentsModule\Helper\Base\AbstractControllerHelper;
use Zikula\UsersModule\Collector\ProfileModuleCollector;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use MU\CommentsModule\Entity\Factory\EntityFactory;
use MU\CommentsModule\Helper\CollectionFilterHelper;
use MU\CommentsModule\Helper\ModelHelper;

/**
 * Helper implementation class for controller layer methods.
 */
class ControllerHelper extends AbstractControllerHelper
{
	/**
	 * @var ProfileModuleCollector
	 */
	protected $collector;
    /**
     * ControllerHelper constructor.
     *
     * @param RequestStack        $requestStack    RequestStack service instance
     * @param Routerinterface     $router          Router service instance
     * @param FormFactoryInterface $formFactory    FormFactory service instance
     * @param VariableApiInterface $variableApi     VariableApi service instance
     * @param EntityFactory       $entityFactory   EntityFactory service instance
     * @param CollectionFilterHelper $collectionFilterHelper CollectionFilterHelper service instance
     * @param ModelHelper         $modelHelper     ModelHelper service instance
     * @param ProfileModuleCollector $collector          ProfileModuleCollector
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        VariableApiInterface $variableApi,
        EntityFactory $entityFactory,
        CollectionFilterHelper $collectionFilterHelper,
        ModelHelper $modelHelper,
    	ProfileModuleCollector $collector
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->variableApi = $variableApi;
        $this->entityFactory = $entityFactory;
        $this->collectionFilterHelper = $collectionFilterHelper;
        $this->modelHelper = $modelHelper;
        $this->collector = $collector;
    }
    
    /**
     * Processes the parameters for a view action.
     * This includes handling pagination, quick navigation forms and other aspects.
     *
     * @param string          $objectType         Name of treated entity type
     * @param SortableColumns $sortableColumns    Used SortableColumns instance
     * @param array           $templateParameters Template data
     * @param boolean         $hasHookSubscriber  Whether hook subscribers are supported or not
     *
     * @return array Enriched template parameters used for creating the response
     */
    public function processViewActionParameters($objectType, SortableColumns $sortableColumns, array $templateParameters = [], $hasHookSubscriber = false)
    {
    	$templateParameters = parent::processViewActionParameters($objectType, $sortableColumns, $templateParameters);

    	if ($templateParameters['routeArea'] == '') {
    		$templateParameters['own'] = 1;
    	}
    	
    	return $templateParameters;
    }
    
    public function getProfileLink($uid)
    {
    	$selected = $this->collector->getSelected();
    	$link = $selected->getProfileUrl($uid);
    	return $link;
    }
    
    public function getAvatar($uid)
    {
    	$selected = $this->collector->getSelected();
    	$avatar = $selected->getAvatar($uid);
    	return $avatar;
    }
}
