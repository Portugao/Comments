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
namespace MU\CommentsModule\Form\Handler\Comment;

use MU\CommentsModule\Form\Handler\Comment\Base\AbstractEditHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RuntimeException;

/**
 * This handler class handles the page events of editing forms.
 * It aims on the comment object type.
 */
class EditHandler extends AbstractEditHandler {
	/**
	 * Initialise form handler.
	 *
	 * This method takes care of all necessary initialisation of our data and form states.
	 *
	 * @param array $templateParameters
	 *        	List of preassigned template variables
	 *        	
	 * @return boolean False in case of initialisation errors, otherwise true
	 */
	public function processForm(array $templateParameters = []) {
		$this->objectType = 'comment';
		$this->objectTypeCapital = 'Comment';
		$this->objectTypeLower = 'comment';
		
		$this->hasPageLockSupport = true;
		
		$result = parent::processForm ($templateParameters);
		if ($result instanceof RedirectResponse) {
			return $result;
		}
		
		if ($this->templateParameters ['mode'] == 'create') {
			if (! $this->modelHelper->canBeCreated ($this->objectType)) {
				$this->request->getSession()->getFlashBag()->add('error', $this->__ ( 'Sorry, but you can not create the comment yet as other items are required which must be created before!'));
				$logArgs = [ 
						'app' => 'MUCommentsModule',
						'user' => $this->currentUserApi->get ('uname'),
						'entity' => $this->objectType 
				];
				$this->logger->notice ('{app}: User {user} tried to create a new {entity}, but failed as it other items are required which must be created before.', $logArgs);
				
				return new RedirectResponse ($this->getRedirectUrl([ 
						'commandName' => '' 
				]), 302);
			}
		}
		
		$request = $this->requestStack->getCurrentRequest();
		$entityData = $this->entityRef->toArray ();
		$owner = $request->attributes->get ('owner', '');
		$this->templateParameters ['owner'] = $owner;
		$area = $request->attributes->get ('area', '');
		$this->templateParameters ['area'] = $area;
		$object = $request->attributes->get ('object', '');
		$this->templateParameters ['object'] = $object;
		
		// assign data to template as array (for additions like standard fields)
		$this->templateParameters [$this->objectTypeLower] = $entityData;
		
		return $result;
	}
	
	/**
	 * @inheritDoc
	 * @throws RuntimeException Thrown if concurrent editing is recognised or another error occurs
	 */
	public function applyAction(array $args = []) {
		// get treated entity reference from persisted member var
		$entity = $this->entityRef;
		
		$kindOfModeration = '';

		$spam = $this->variableApi->get('MUCommentsModule', 'enableInternSpamHandling');
		// if spam is enalbled content has to be empty
		if ($spam == 1) {
		    if ($entity['content'] != '') {
		        return false;
		    }
		    
		    $toModeration = $this->variableApi->get('MUCommentsModule', 'toModeration');
		    if ($toModeration != '') {
		        $toModeration = explode(',', $toModeration);
		        foreach ($toModeration as $moderation) {
		            if(strpos($entity['subject'], $moderation) !== false || strpos($entity['name'], $moderation) !== false || strpos($entity['text'], $moderation) !== false) {
		                $kindOfModeration = 'moderate';
		                break;
		            }
		        }
		    }
		    $toNotSaved = $this->variableApi->get('MUCommentsModule', 'toNotSaved');
		    if ($toNotSaved != '') {
		        $toNotSaved = explode(',', $toNotSaved);
		        foreach ($toNotSaved as $notsaved) {
		            if(strpos($entity['subject'], $notsaved) !== false || strpos($entity['name'], $notsaved) !== false || strpos($entity['text'], $notsaved) !== false) {
		                $kindOfModeration = 'block';
		                break;
		            }
		        }
		    }
		}
				
		$action = $args ['commandName'];
		
		if ($kindOfModeration != 'block') {
		    if ($kindOfModeration == 'moderate') {
		        $entity['workflowState'] = 'waiting';
		    } else {
                $entity['workflowState'] = 'approved';
            }

        } else {
            //$action = 'delete';
		}
		
		$success = false;
		$flashBag = $this->requestStack->getCurrentRequest()
		->getSession()
		->getFlashBag();
		try {
		    // execute the workflow action
		    $success = $this->workflowHelper->executeAction($entity, $action);
		} catch (\Exception $exception) {
		    $flashBag->add('error', $this->__f('Sorry, but an error occured during the %action% action. Please apply the changes again!', [
		        '%action%' => $action
		    ]) . ' ' . $exception->getMessage());
		    $logArgs = [
		        'app' => 'MUCommentsModule',
		        'user' => $this->currentUserApi->get('uname'),
		        'entity' => 'comment',
		        'id' => $entity->getKey(),
		        'errorMessage' => $exception->getMessage()
		    ];
		    $this->logger->error('{app}: User {user} tried to edit the {entity} with id {id}, but failed. Error details: {errorMessage}.', $logArgs);
		}
		
		$this->addDefaultMessage ( $args, $success );
		$request = $this->requestStack->getCurrentRequest();
		
		if ($success && $this->templateParameters ['mode'] == 'create') {
			// store new identifier
			$this->idValue = $entity->getKey ();
			$owner = $request->request->get('owner', '');
			$area = $request->request->get('area', '');
			$object = $request->request->get('object', '');
			
			$entityManager = $this->entityFactory->getObjectManager ();
			
			$entityClass = 'MU\\CommentsModule\\Entity\\HookAssignmentEntity';
			
			$date = date('Y-m-d H:i:s');
			
			$assignment = new $entityClass ();
			;
			$assignment->setSubscriberOwner ($owner);
			$assignment->setSubscriberAreaId($area);
			$assignment->setSubscriberObjectId($object);
			$assignment->setAssignedEntity('comment');
			$assignment->setUpdatedDate($date);
			if ($this->templateParameters ['mode'] == 'create') {
				$assignment->setAssignedId ($this->idValue);
			} else {
				// nothing to do
			}
			$entityManager->persist ( $assignment );
			$entityManager->flush ();
		}
		
		return $success;
	}
}
