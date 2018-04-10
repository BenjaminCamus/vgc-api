<?php

namespace GameBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ContactAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('zipcode')
            ->add('city')
            ->add('updatedAt')
            ->add('createdAt');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('email')
            ->add('city')
            ->add('updatedAt')
            ->add('createdAt')
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
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('zipcode')
            ->add('city');
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('zipcode')
            ->add('city')
            ->add('updatedAt')
            ->add('createdAt');
    }
}
