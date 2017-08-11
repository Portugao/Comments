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

namespace MU\CommentsModule\Block\Base;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\Core\AbstractBundle;
use MU\CommentsModule\Block\Form\Type\ItemListBlockType;

/**
 * Generic item list block base class.
 */
abstract class AbstractItemListBlock extends AbstractBlockHandler
{
    /**
     * Display the block content.
     *
     * @param array $properties The block properties array
     *
     * @return array|string
     */
    public function display(array $properties)
    {
        // only show block content if the user has the required permissions
        if (!$this->hasPermission('MUCommentsModule:ItemListBlock:', "$properties[title]::", ACCESS_OVERVIEW)) {
            return false;
        }
    
        // set default values for all params which are not properly set
        $defaults = $this->getDefaults();
        $properties = array_merge($defaults, $properties);
    
        $controllerHelper = $this->get('mu_comments_module.controller_helper');
        $contextArgs = ['name' => 'list'];
        if (!isset($properties['objectType']) || !in_array($properties['objectType'], $controllerHelper->getObjectTypes('block', $contextArgs))) {
            $properties['objectType'] = $controllerHelper->getDefaultObjectType('block', $contextArgs);
        }
    
        $objectType = $properties['objectType'];
    
        $repository = $this->get('mu_comments_module.entity_factory')->getRepository($objectType);
    
        // create query
        $orderBy = $this->get('mu_comments_module.model_helper')->resolveSortParameter($objectType, $properties['sorting']);
        $qb = $repository->genericBaseQuery($properties['filter'], $orderBy);
    
        // get objects from database
        $currentPage = 1;
        $resultsPerPage = $properties['amount'];
        $query = $repository->getSelectWherePaginatedQuery($qb, $currentPage, $resultsPerPage);
        try {
            list($entities, $objectCount) = $repository->retrieveCollectionResult($query, true);
        } catch (\Exception $exception) {
            $entities = [];
            $objectCount = 0;
        }
    
        // set a block title
        if (empty($properties['title'])) {
            $properties['title'] = $this->__('MUCommentsModule items');
        }
    
        $template = $this->getDisplayTemplate($properties);
    
        $templateParameters = [
            'vars' => $properties,
            'objectType' => $objectType,
            'items' => $entities
        ];
    
        $templateParameters = $this->get('mu_comments_module.controller_helper')->addTemplateParameters($properties['objectType'], $templateParameters, 'block', []);
    
        return $this->renderView($template, $templateParameters);
    }
    
    /**
     * Returns the template used for output.
     *
     * @param array $properties The block properties array
     *
     * @return string the template path
     */
    protected function getDisplayTemplate(array $properties)
    {
        $templateFile = $properties['template'];
        if ($templateFile == 'custom') {
            $templateFile = $properties['customTemplate'];
        }
    
        $templateForObjectType = str_replace('itemlist_', 'itemlist_' . $properties['objectType'] . '_', $templateFile);
        $templating = $this->get('templating');
    
        $templateOptions = [
            'ContentType/' . $templateForObjectType,
            'Block/' . $templateForObjectType,
            'ContentType/' . $templateFile,
            'Block/' . $templateFile,
            'Block/itemlist.html.twig'
        ];
    
        $template = '';
        foreach ($templateOptions as $templatePath) {
            if ($templating->exists('@MUCommentsModule/' . $templatePath)) {
                $template = '@MUCommentsModule/' . $templatePath;
                break;
            }
        }
    
        return $template;
    }
    
    /**
     * Returns the fully qualified class name of the block's form class.
     *
     * @return string Template path
     */
    public function getFormClassName()
    {
        return ItemListBlockType::class;
    }
    
    /**
     * Returns any array of form options.
     *
     * @return array Options array
     */
    public function getFormOptions()
    {
        $objectType = 'comment';
    
        $request = $this->get('request_stack')->getCurrentRequest();
        if ($request->attributes->has('blockEntity')) {
            $blockEntity = $request->attributes->get('blockEntity');
            if (is_object($blockEntity) && method_exists($blockEntity, 'getProperties')) {
                $blockProperties = $blockEntity->getProperties();
                if (isset($blockProperties['objectType'])) {
                    $objectType = $blockProperties['objectType'];
                } else {
                    // set default options for new block creation
                    $blockEntity->setProperties($this->getDefaults());
                }
            }
        }
    
        return [
            'object_type' => $objectType
        ];
    }
    
    /**
     * Returns the template used for rendering the editing form.
     *
     * @return string Template path
     */
    public function getFormTemplate()
    {
        return '@MUCommentsModule/Block/itemlist_modify.html.twig';
    }
    
    /**
     * Returns default settings for this block.
     *
     * @return array The default settings
     */
    protected function getDefaults()
    {
        return [
            'objectType' => 'comment',
            'sorting' => 'default',
            'amount' => 5,
            'template' => 'itemlist_display.html.twig',
            'customTemplate' => '',
            'filter' => ''
        ];
    }
    
}
