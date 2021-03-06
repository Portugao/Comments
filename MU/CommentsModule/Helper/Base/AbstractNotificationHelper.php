<?php
/**
 * Comments.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 * @link https://homepages-mit-zikula.de
 * @link https://ziku.la
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

namespace MU\CommentsModule\Helper\Base;

use Swift_Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use MU\CommentsModule\Helper\EntityDisplayHelper;
use MU\CommentsModule\Helper\WorkflowHelper;

/**
 * Notification helper base class.
 */
abstract class AbstractNotificationHelper
{
    use TranslatorTrait;
    
    /**
     * @var RouterInterface
     */
    protected $router;
    
    /**
     * @var ZikulaHttpKernelInterface
     */
    protected $kernel;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var Twig_Environment
     */
    protected $templating;
    
    /**
     * @var MailerApiInterface
     */
    protected $mailer;
    
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;
    
    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;
    
    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;
    
    /**
     * List of notification recipients.
     *
     * @var array $recipients
     */
    protected $recipients = [];
    
    /**
     * Which type of recipient is used ("creator", "moderator" or "superModerator").
     *
     * @var string recipientType
     */
    protected $recipientType = '';
    
    /**
     * The entity which has been changed before.
     *
     * @var EntityAccess entity
     */
    protected $entity = '';
    
    /**
     * Name of workflow action which is being performed.
     *
     * @var string action
     */
    protected $action = '';
    
    /**
     * Name of the application.
     *
     * @var string
     */
    protected $name;
    
    /**
     * NotificationHelper constructor.
     *
     * @param ZikulaHttpKernelInterface $kernel              Kernel service instance
     * @param TranslatorInterface       $translator          Translator service instance
     * @param Routerinterface           $router              Router service instance
     * @param RequestStack              $requestStack        RequestStack service instance
     * @param VariableApiInterface      $variableApi         VariableApi service instance
     * @param Twig_Environment          $twig                Twig service instance
     * @param MailerApiInterface        $mailerApi           MailerApi service instance
     * @param GroupRepositoryInterface  $groupRepository     GroupRepository service instance
     * @param EntityDisplayHelper       $entityDisplayHelper EntityDisplayHelper service instance
     * @param WorkflowHelper            $workflowHelper      WorkflowHelper service instance
     */
    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        Twig_Environment $twig,
        MailerApiInterface $mailerApi,
        GroupRepositoryInterface $groupRepository,
        EntityDisplayHelper $entityDisplayHelper,
        WorkflowHelper $workflowHelper
    ) {
        $this->kernel = $kernel;
        $this->setTranslator($translator);
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->templating = $twig;
        $this->mailerApi = $mailerApi;
        $this->groupRepository = $groupRepository;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->workflowHelper = $workflowHelper;
        $this->name = 'MUCommentsModule';
    }
    
    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Sends a mail to either an item's creator or a group of moderators.
     *
     * @return boolean
     */
    public function process($args)
    {
        if (!isset($args['recipientType']) || !$args['recipientType']) {
            return false;
        }
    
        if (!isset($args['action']) || !$args['action']) {
            return false;
        }
    
        if (!isset($args['entity']) || !$args['entity']) {
            return false;
        }
    
        $this->recipientType = $args['recipientType'];
        $this->action = $args['action'];
        $this->entity = $args['entity'];
    
        $debug = isset($args['debug']) && $args['debug'];
        $this->collectRecipients($debug);
    
        if (!count($this->recipients)) {
            return true;
        }
    
        $request = $this->requestStack->getCurrentRequest();
        $session = null !== $request ? $request->getSession() : null;
    
        if (null === $this->kernel->getModule('ZikulaMailerModule')) {
            if (null !== $session) {
                $session->getFlashBag()->add('error', $this->__('Could not inform other persons about your amendments, because the Mailer module is not available - please contact an administrator about that!'));
            }
    
            return false;
        }
    
        $result = $this->sendMails();
    
        if (null !== $session) {
            $session->remove($this->name . 'AdditionalNotificationRemarks');
        }
    
        return $result;
    }
    
    /**
     * Collects the recipients.
     *
     * @param boolean $debug Whether to add the admin or not
     */
    protected function collectRecipients($debug = false)
    {
        $this->recipients = [];
    
        if ($this->recipientType == 'moderator' || $this->recipientType == 'superModerator') {
            $modVarSuffixes = [
                'comment' => 'Comments'
            ];
            $modVarSuffix = $modVarSuffixes[$this->entity['_objectType']];
    
            $moderatorGroupId = $this->variableApi->get('MUCommentsModule', 'moderationGroupFor' . $modVarSuffix, GroupsConstant::GROUP_ID_ADMIN);
            if ($this->recipientType == 'superModerator') {
                $moderatorGroupId = $this->variableApi->get('MUCommentsModule', 'superModerationGroupFor' . $modVarSuffix, GroupsConstant::GROUP_ID_ADMIN);
            }
    
            $moderatorGroup = $this->groupRepository->find($moderatorGroupId);
            if (null !== $moderatorGroup) {
                foreach ($moderatorGroup['users'] as $user) {
                    $this->addRecipient($user);
                }
            }
        } elseif ($this->recipientType == 'creator' && method_exists($this->entity, 'getCreatedBy')) {
            $this->addRecipient($this->entity->getCreatedBy());
        } elseif ($this->usesDesignatedEntityFields()) {
            $this->addRecipient();
        }
    
        if ($debug) {
            // add the admin, too
            $this->addRecipient(UsersConstant::USER_ID_ADMIN);
        }
    }
    
    /**
     * Collects data for building the recipients array.
     *
     * @param UserEntity $user Recipient user record
     */
    protected function addRecipient(UserEntity $user = null)
    {
        if ($this->usesDesignatedEntityFields()) {
            $recipientTypeParts = explode('-', $this->recipientType);
            if (count($recipientTypeParts) != 2) {
                return;
            }
            $fieldNames = explode('^', $recipientTypeParts[1]);
            if (count($fieldNames) != 2) {
                return;
            }
    
            $this->recipients[] = [
                'name' => $this->entity[$fieldNames[1]],
                'email' => $this->entity[$fieldNames[0]]
            ];
    
            return;
    	}
    
        if (null === $user) {
            return;
        }
    
        $userAttributes = $user->getAttributes();
    
        $this->recipients[] = [
            'name' => isset($userAttributes['name']) && !empty($userAttributes['name']) ? $userAttributes['name'] : $user->getUname(),
            'email' => $user->getEmail()
        ];
    }
    
    /**
     * Performs the actual mailing.
     */
    protected function sendMails()
    {
        $objectType = $this->entity['_objectType'];
        $siteName = $this->variableApi->getSystemVar('sitename');
        $adminMail = $this->variableApi->getSystemVar('adminmail');
    
        $templateType = '';
        if (strpos($this->recipientType,'field-') === 0) {
            $templateType = $this->recipientType;
    	} else {
            $templateType = $this->recipientType == 'creator' ? 'Creator' : 'Moderator';
        }
        $template = 'Email/notify' . ucfirst($objectType) . $templateType .  '.html.twig';
    
        $mailData = $this->prepareEmailData();
        $subject = $this->getMailSubject();
    
        // send one mail per recipient
        $totalResult = true;
        foreach ($this->recipients as $recipient) {
            if (!isset($recipient['name']) || !$recipient['name']) {
                continue;
            }
            if (!isset($recipient['email']) || !$recipient['email']) {
                continue;
            }
    
            $body = $this->templating->render('@MUCommentsModule/' . $template, [
                'recipient' => $recipient,
                'mailData' => $mailData
            ]);
            $altBody = '';
            $html = true;
    
            // create new message instance
            /** @var Swift_Message */
            $message = Swift_Message::newInstance();
    
            $message->setFrom([$adminMail => $siteName]);
            $message->setTo([$recipient['email'] => $recipient['name']]);
    
            $totalResult &= $this->mailerApi->sendMessage($message, $subject, $body, $altBody, $html);
        }
    
        return $totalResult;
    }
    
    /**
     * Returns the subject used for the emails to be sent.
     *
     * @return string
     */
    protected function getMailSubject()
    {
        $mailSubject = '';
        if ($this->recipientType == 'moderator' || $this->recipientType == 'superModerator' || $this->usesDesignatedEntityFields()) {
            if ($this->action == 'submit') {
                $mailSubject = $this->__('New content has been submitted');
            } elseif ($this->action == 'demote') {
                $mailSubject = $this->__('Content has been demoted');
            } elseif ($this->action == 'accept') {
                $mailSubject = $this->__('Content has been accepted');
            } elseif ($this->action == 'approve') {
                $mailSubject = $this->__('Content has been approved');
            } elseif ($this->action == 'delete') {
                $mailSubject = $this->__('Content has been deleted');
            } else {
                $mailSubject = $this->__('Content has been updated');
            }
        } elseif ($this->recipientType == 'creator') {
            if ($this->action == 'accept') {
                $mailSubject = $this->__('Your submission has been accepted');
            } elseif ($this->action == 'approve') {
                $mailSubject = $this->__('Your submission has been approved');
            } elseif ($this->action == 'reject') {
                $mailSubject = $this->__('Your submission has been rejected');
            } elseif ($this->action == 'delete') {
                $mailSubject = $this->__('Your submission has been deleted');
            } else {
                $mailSubject = $this->__('Your submission has been updated');
            }
        }
    
        return $mailSubject;
    }
    
    /**
     * Collects data used by the email templates.
     *
     * @return array Email template data
     */
    protected function prepareEmailData()
    {
        $objectType = $this->entity->get_objectType();
        $state = $this->entity->getWorkflowState();
        $stateInfo = $this->workflowHelper->getStateInfo($state);
    
        $request = $this->requestStack->getCurrentRequest();
        $session = null !== $request ? $request->getSession() : null;
        $remarks = null !== $session ? $session->get($this->name . 'AdditionalNotificationRemarks', '') : '';
    
        $hasDisplayAction = in_array($objectType, ['comment']);
        $hasEditAction = in_array($objectType, ['comment']);
        $routeArea = in_array($this->recipientType, ['moderator', 'superModerator']) ? 'admin' : '';
        $routePrefix = 'mucommentsmodule_' . strtolower($objectType) . '_' . $routeArea;
    
        $urlArgs = $this->entity->createUrlArgs();
        $displayUrl = $hasDisplayAction ? $this->router->generate($routePrefix . 'display', $urlArgs, UrlGeneratorInterface::ABSOLUTE_URL) : '';
    
        $editUrl = $hasEditAction ? $this->router->generate($routePrefix . 'edit', $urlArgs, UrlGeneratorInterface::ABSOLUTE_URL) : '';
    
        return [
            'name' => $this->entityDisplayHelper->getFormattedTitle($this->entity),
            'newState' => $stateInfo['text'],
            'remarks' => $remarks,
            'displayUrl' => $displayUrl,
            'editUrl' => $editUrl
        ];
    }
    
    /**
     * Checks whether a special notification type is used or not.
     *
     * @return boolean
     */
    protected function usesDesignatedEntityFields()
    {
        return strpos($this->recipientType, 'field-') === 0;
    }
}
