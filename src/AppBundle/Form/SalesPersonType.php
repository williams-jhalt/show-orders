<?php

namespace AppBundle\Form;

use AppBundle\Service\ErpConnector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesPersonType extends AbstractType
{
    
    /**
     *
     * @var ErpConnector
     */
    private $erp;
    
    public function __construct(ErpConnector $erp) {
        $this->erp = $erp;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $salespeople = [];
        
        $t = $this->erp->getSalesPeople();
        
        foreach ($t as $sp) {
            $salespeople[$sp->name] = $sp->id;
        }
        
        $builder->add('salesPersonNumber', ChoiceType::class, [
            'choices' => $salespeople
        ])->add('name')->add('user');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\SalesPerson'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_salesperson';
    }


}
