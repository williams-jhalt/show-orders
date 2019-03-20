<?php

namespace AppBundle\Service;

class ErpConnector {

    private $hostname;
    private $token;
    private $company;

    public function __construct($hostname, $token, $company) {
        $this->hostname = $hostname;
        $this->token = $token;
        $this->company = $company;
    }

    public function getCustomers() {

        $ch = curl_init("https://" . $this->hostname . "/api/" . $this->company . "/customer");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-AUTH-TOKEN: ' . $this->token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public function getCustomer($customerNumber) {


        $ch = curl_init("https://" . $this->hostname . "/api/" . $this->company . "/customer/" . $customerNumber);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-AUTH-TOKEN: ' . $this->token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public function getSalesPeople() {

        $ch = curl_init("https://" . $this->hostname . "/api/" . $this->company . "/salesperson");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-AUTH-TOKEN: ' . $this->token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public function getSalesPerson($salesPersonNumber) {
        $ch = curl_init("https://" . $this->hostname . "/api/" . $this->company . "/salesperson/" . $salesPersonNumber);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-AUTH-TOKEN: ' . $this->token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

}
