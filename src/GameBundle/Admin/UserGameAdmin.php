<?php

namespace GameBundle\Admin;

use GameBundle\Entity\UserGame;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class UserGameAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user')
            ->add('game')
            ->add('platform')
            ->add('rating')
            ->add('completeness')
            ->add('version')
            ->add('priceAsked')
            ->add('pricePaid')
            ->add('priceResale')
            ->add('priceSold')
            ->add('purchaseDate')
            ->add('saleDate')
            ->add('progress')
            ->add('cond')
            ->add('note')
            ->add('updatedAt')
            ->add('createdAt');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('user')
            ->add('game')
            ->add('platform')
            ->add('rating')
            ->add('completeness', 'choice', ['choices' => array_flip(UserGame::COMPLETENESS_LIST)])
            ->add('version')
            ->add('priceAsked')
            ->add('pricePaid')
            ->add('priceResale')
            ->add('priceSold')
            ->add('purchaseDate')
            ->add('saleDate')
            ->add('progress', 'choice', ['choices' => array_flip(UserGame::PROGRESS_LIST)])
            ->add('cond', 'choice', ['choices' => array_flip(UserGame::COND_LIST)])
            ->add('note')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('user')
            ->add('game')
            ->add('platform')
            ->add('rating')
            ->add('completeness', 'choice', ['choices' => UserGame::COMPLETENESS_LIST])
            ->add('version')
            ->add('priceAsked')
            ->add('pricePaid')
            ->add('priceResale')
            ->add('priceSold')
            ->add('purchaseDate')
            ->add('saleDate')
            ->add('progress', 'choice', ['choices' => UserGame::PROGRESS_LIST])
            ->add('cond', 'choice', ['choices' => UserGame::COND_LIST])
            ->add('note');
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('user')
            ->add('game')
            ->add('platform')
            ->add('rating')
            ->add('completeness')
            ->add('version')
            ->add('priceAsked')
            ->add('pricePaid')
            ->add('priceResale')
            ->add('priceSold')
            ->add('purchaseDate')
            ->add('purchaseContact')
            ->add('purchasePlace')
            ->add('saleDate')
            ->add('saleContact')
            ->add('salePlace')
            ->add('progress')
            ->add('cond')
            ->add('note')
            ->add('updatedAt')
            ->add('createdAt');
    }
}
