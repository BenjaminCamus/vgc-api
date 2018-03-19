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
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('phone')
            ->add('address')
            ->add('zipcode')
            ->add('city');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('phone')
            ->add('address')
            ->add('zipcode')
            ->add('city')
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
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
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
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('nickname')
            ->add('phone')
            ->add('address')
            ->add('zipcode')
            ->add('city');
    }
}
