<?php

namespace Shivoham\ValueObjectFormBundle\Form\Extension;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class FormExtension extends AbstractTypeExtension implements DataMapperInterface
{
    private $propertyAccessor;

    private $propertyPathMapper;

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
        $this->propertyPathMapper = new PropertyPathMapper($this->propertyAccessor);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['object_accessor']) || !isset($options['object_mutator'])) {
            return;
        }

        $builder->setDataMapper($this);
    }

    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);
        $parent = current($forms)->getParent();
        $options = $parent->getConfig()->getOptions();

        return $this->propertyAccessor->getValue($data, $options['object_accessor']);
    }

    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);
        $parent = current($forms)->getParent();
        $options = $parent->getConfig()->getOptions();

        $result = [];
        if (isset($options['value_object_class'])) {
            $valueObjectClass = $options['value_object_class'];
            $result = new $valueObjectClass();
            $this->propertyPathMapper->mapFormsToData($forms, $result);
        } else {
            foreach ($forms as $key => $form) {
                $result[$key] = $form->getData();
            }
        }

        try {
            $this->propertyAccessor->setValue($data, $options['object_mutator'], $result);
        } catch (\Symfony\Component\PropertyAccess\Exception\ExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            $parent->addError(new FormError($e->getMessage()));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['object_accessor', 'object_mutator', 'value_object_class']);
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
