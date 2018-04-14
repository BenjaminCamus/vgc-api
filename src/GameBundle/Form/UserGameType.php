<?php

namespace GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserGameType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user')
            ->add('game')
            ->add('platform')
            ->add('releaseDate')
            ->add('rating')
            /* TODO: delete box and manual */
            ->add('box')
            ->add('manual')
            ->add('completeness')
            ->add('version')
            ->add('priceAsked')
            ->add('pricePaid')
            ->add('priceResale')
            ->add('priceSold')
            ->add('purchaseDate', 'date', array(
                'widget' => 'single_text',
                'input' => 'datetime'
            ))
            ->add('saleDate', 'date', array(
                'widget' => 'single_text',
                'input' => 'datetime'
            ))
            ->add('progress')
            ->add('cond')
            ->add('note')
            ->add('purchaseContact')
            ->add('saleContact')
            ->add('purchasePlace')
            ->add('salePlace');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GameBundle\Entity\UserGame',
            'csrf_protection' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'gamebundle_usergame';
    }


}
