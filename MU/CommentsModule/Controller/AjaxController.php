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

namespace MU\CommentsModule\Controller;

use MU\CommentsModule\Controller\Base\AbstractAjaxController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Ajax controller implementation class.
 *
 * @Route("/ajax")
 */
class AjaxController extends AbstractAjaxController
{
    
    /**
     * Searches for entities for auto completion usage.
     *
     * @Route("/getItemListAutoCompletion", options={"expose"=true})
     * @Method("GET")
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     */
    public function getItemListAutoCompletionAction(Request $request)
    {
        return parent::getItemListAutoCompletionAction($request);
    }
    
    /**
     * Attachs a given hook assignment by creating the corresponding assignment data record.
     *
     * @Route("/attachHookObject", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function attachHookObjectAction(Request $request)
    {
        return parent::attachHookObjectAction($request);
    }
    
    /**
     * Detachs a given hook assignment by removing the corresponding assignment data record.
     *
     * @Route("/detachHookObject", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function detachHookObjectAction(Request $request)
    {
        return parent::detachHookObjectAction($request);
    }
    
    /**
     * Detachs a given hook assignment by removing the corresponding assignment data record.
     *
     * @Route("/answer", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function answerAction(Request $request)
    {
    	return $this->answerInternal($request);
    }
    
    /**
     * Attachs a given hook assignment by creating the corresponding assignment data record.
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function answerInternal(Request $request)
    {
    	if (!$this->hasPermission('MUCommentsModule::Ajax', '::', ACCESS_EDIT)) {
    		throw new AccessDeniedException();
    	}
    
    	$subscriberOwner = $request->request->get('owner', '');
    	$subscriberAreaId = $request->request->get('areaId', '');
    	$subscriberObjectId = $request->request->getInt('objectId', 0);
    	//$subscriberUrl = $request->request->get('url', '');
    	$assignedEntity = $request->request->get('assignedEntity', '');
    
    	if (!$subscriberOwner || !$subscriberAreaId || !$subscriberObjectId || !$assignedEntity) {
    		return new JsonResponse($this->__('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
    	}
    
    	//$subscriberUrl = !empty($subscriberUrl) ? unserialize($subscriberUrl) : [];
    	
    	$entityManager = $this->get('mu_comments_module.entity_factory')->getObjectManager();
    	$repository = $this->get('mu_comments_module.entity_factory')->getRepository('comment');
    	$parentEntity = '';
    	$subject = $request->request->get('subject', '');
    	$name = $request->request->get('name', '');
    	$text = $request->request->get('text');
    	$message = $request->request->get('message');
    	$parentid = $request->request->get('parentcomment');
    	if ($parentid > 0) {
    	$parentEntity = $repository->selectById($parentid);
    	if (!is_object($parentEntity)) {
    		return new JsonResponse($this->__('Error: no object.'), JsonResponse::HTTP_BAD_REQUEST);
    	}
    	}
 	
    	$comment = new \MU\CommentsModule\Entity\CommentEntity();
    	if ($subject != '') {
    	    $comment->setSubject($subject);	
    	} else {
    	    $comment->setSubject('');
    	}
    	$comment->setName($name);
    	$comment->setText($text);
    	if (is_Object($parentEntity)) {
    	$comment->setComment($parentEntity);
    	} else {
    		$comment->setComment(NULL);
    	}
    	$comment->setWorkflowState('approved');
    	
    	$qb = $entityManager->persist($comment);
    	$qb = $entityManager->flush();
    	
    	$commentId = $comment->getId();
    
    	$assignment = new \MU\CommentsModule\Entity\HookAssignmentEntity();
    	$assignment->setSubscriberOwner($subscriberOwner);
    	$assignment->setSubscriberAreaId($subscriberAreaId);
    	$assignment->setSubscriberObjectId($subscriberObjectId);
    	//$assignment->setSubscriberUrl($subscriberUrl);
    	$assignment->setAssignedEntity($assignedEntity);
    	$assignment->setAssignedId($commentId);
    	$assignment->setUpdatedDate(new \DateTime()); 

    	$qb = $entityManager->persist($assignment);
    	$qb = $entityManager->flush();
    	
    	$controllerHelper = $this->get('mu_comments_module.controller_helper');
    	$profileLink = $controllerHelper->getProfileLink($comment->getCreatedBy()->getUid());
    	$avatar = $controllerHelper->getAvatar($comment->getCreatedBy()->getUid());
    	$link = '<a href="' . $profileLink . '" >' . $comment->getCreatedBy()->getUname() . '</a>';
    
    	// return response
    	return new JsonResponse([
    			'id' => $commentId,
    			'subject' => $comment->getSubject(),
    			'text' => $comment->getText(),
    			'user' => $comment->getCreatedBy()->getUname(),
    			'avatar' => $avatar,
    			'created' => $comment->getCreatedDate(),
    			'link' => $link
    	]);
    }

    // feel free to add your own ajax controller methods here
}
