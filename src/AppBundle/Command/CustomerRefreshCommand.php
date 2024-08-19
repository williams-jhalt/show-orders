<?php

namespace AppBundle\Command;

use AppBundle\Entity\Customer;
use AppBundle\Entity\SalesPerson;
use AppBundle\Service\ErpConnector;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerRefreshCommand extends Command {

    /**
     * @var ErpConnector
     */
    private $erp;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(ErpConnector $erp, EntityManager $em) {
        $this->erp = $erp;
        $this->em = $em;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('app:customer-refresh')
                ->setDescription('Imports customer data from ERP')
                ->setHelp('This command refreshes existing customer information with data from ERP');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $repo = $this->em->getRepository(Customer::class);
        $spRepo = $this->em->getRepository(SalesPerson::class);
        
        $customers = $repo->findAll();

        foreach ($customers as $customer) {

            $customerData = $this->erp->getCustomer($customer->getCustomerNumber());

            if (empty($customerData)) {
                continue;
            }

            $customer->setCustomerNumber($customerData->customerNumber);
            $customer->setCompany($customerData->name);

            $salesPerson = $spRepo->findOneBySalesPersonNumber($customerData->salespersonId);

            if ($salesPerson == null) {

                $spData = $this->erp->getSalesPerson($customerData->salespersonId);

                $salesPerson = new SalesPerson();
                $salesPerson->setSalesPersonNumber($spData->id);
                $salesPerson->setName($spData->name);

                $this->em->persist($salesPerson);
                $this->em->flush($salesPerson);
            }

            $customer->setSalesPerson($salesPerson);

            $this->em->persist($customer);
        }
        
        $this->em->flush();
        
    }

}
