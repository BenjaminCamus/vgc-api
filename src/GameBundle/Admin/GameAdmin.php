<?php

namespace GameBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class GameAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('rating')
            ->add('ratingCount')
            ->add('igdbId')
            ->add('igdbUrl')
            ->add('igdbCreatedAt')
            ->add('igdbUpdatedAt')
            ->add('createdAt')
            ->add('updatedAt');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('slug')
            ->add('rating')
            ->add('ratingCount')
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
            ->add('id', null, array('disabled' => true, 'required' => false))
            ->add('name')
            ->add('series')
            ->add('developers')
            ->add('publishers');
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('rating')
            ->add('ratingCount')
            ->add('igdbId')
            ->add('igdbUrl')
            ->add('igdbCreatedAt')
            ->add('igdbUpdatedAt')
            ->add('series')
            ->add('developers')
            ->add('publishers')
            ->add('updatedAt')
            ->add('createdAt');
    }
}
