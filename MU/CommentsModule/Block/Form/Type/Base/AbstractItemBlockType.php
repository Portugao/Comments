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

namespace MU\CommentsModule\Block\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use MU\CommentsModule\Entity\Factory\EntityFactory;
use MU\CommentsModule\Helper\EntityDisplayHelper;

/**
 * Detail block form type base class.
 */
abstract class AbstractItemBlockType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;

    /**
     * ItemBlockType constructor.
     *
     * @param TranslatorInterface $translator          Translator service instance
     * @param EntityFactory       $entityFactory       EntityFactory service instance
     * @param EntityDisplayHelper $entityDisplayHelper EntityDisplayHelper service instance
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityFactory $entityFactory,
        EntityDisplayHelper $entityDisplayHelper
    ) {
        $this->setTranslator($translator);
        $this->entityFactory = $entityFactory;
        $this->entityDisplayHelper = $entityDisplayHelper;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addObjectTypeField($builder, $options);
        $this->addIdField($builder, $options);
        $this->addTemplateField($builder, $options);
    }

    /**
     * Adds an object type field.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addObjectTypeField(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('objectType', HiddenType::class, [
            'label' => $this->__('Object type') . ':',
            'empty_data' => 'comment',
            'attr' => [
                'title' => $this->__('If you change this please save the block once to reload the parameters below.')
            ],
            'help' => $this->__('If you change this please save the block once to reload the parameters below.')    ]);
    }

    /**
     * Adds a item identifier field.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addIdField(FormBuilderInterface $builder, array $options = [])
    {
        $repository = $this->entityFactory->getRepository($options['object_type']);
        // select without joins
        $entities = $repository->selectWhere('', '', false);
    
        $choices = [];
        foreach ($entities as $entity) {
            $choices[$this->entityDisplayHelper->getFormattedTitle($entity)] = $entity->getKey();
        }
        ksort($choices);
    
        $builder->add('id', ChoiceType::class, [
            'multiple' => false,
            'expanded' => false,
            'choices' => $choices,
            'choices_as_values' => true,
            'required' => true,
            'label' => $this->__('Entry to display') . ':'
        ]);
    }

    /**
     * Adds template fields.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addTemplateField(FormBuilderInterface $builder, array $options = [])
    {
        $builder
            ->add('customTemplate', TextType::class, [
                'label' => $this->__('Custom template') . ':',
                'required' => false,
                'attr' => [
                    'maxlength' => 80,
                    'title' => $this->__('Example') . ': displaySpecial.html.twig'
                ],
                'help' => [
                    $this->__('Example') . ': <em>displaySpecial.html.twig</em>',
                    $this->__('Needs to be located in the "External/YourEntity/" directory.')
                ]
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'mucommentsmodule_detailblock';
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'object_type' => 'comment'
            ])
            ->setRequired(['object_type'])
            ->setAllowedTypes('object_type', 'string')
        ;
    }
}