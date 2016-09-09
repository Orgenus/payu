<?php
namespace Orgenus\Payu;
/**
 * Created by PhpStorm.
 * User: orhangazibasli2
 * Date: 5.09.2016
 * Time: 13:58
 */
 
require_once dirname(__FILE__) . '/PayU/Alu/Exceptions/ClientException.php';
require_once dirname(__FILE__) . '/PayU/Alu/Exceptions/ConnectionException.php';
require_once dirname(__FILE__) . '/PayU/Alu/AbstractCommonAddress.php';
require_once dirname(__FILE__) . '/PayU/Alu/MerchantConfig.php';
require_once dirname(__FILE__) . '/PayU/Alu/User.php';
require_once dirname(__FILE__) . '/PayU/Alu/HashService.php';
require_once dirname(__FILE__) . '/PayU/Alu/HTTPClient.php';
require_once dirname(__FILE__) . '/PayU/Alu/Card.php';
require_once dirname(__FILE__) . '/PayU/Alu/CardToken.php';
require_once dirname(__FILE__) . '/PayU/Alu/Billing.php';
require_once dirname(__FILE__) . '/PayU/Alu/Delivery.php';
require_once dirname(__FILE__) . '/PayU/Alu/Product.php';
require_once dirname(__FILE__) . '/PayU/Alu/Order.php';
require_once dirname(__FILE__) . '/PayU/Alu/Request.php';
require_once dirname(__FILE__) . '/PayU/Alu/Response.php';
require_once dirname(__FILE__) . '/PayU/Alu/ResponseWireAccount.php';
require_once dirname(__FILE__) . '/PayU/Alu/Client.php';

use PayU\Alu\Billing;
use PayU\Alu\Card;
use PayU\Alu\Client;
use PayU\Alu\Delivery;
use PayU\Alu\MerchantConfig;
use PayU\Alu\Order;
use PayU\Alu\Product;
use PayU\Alu\Request;
use PayU\Alu\User;
use PayU\Alu\Exceptions\ConnectionException;
use PayU\Alu\Exceptions\ClientException;


class Payu
{
    private $aluCfg;
    private $aluUser;
    private $aluOrder;
    private $aluBilling;
    private $aluDelivery;
    private $aluCard;
    private $aluRequest;
    private $aluClient;

    public function __construct($county = "TR", $timeOut = 1000, $paymentMethod = "CCVISAMC")
    {
        $this->aluCfg = new MerchantConfig(config('payu.MERCHANT_ID'), config('payu.SECRET'), $county);
        $this->aluUser = new User(\Illuminate\Http\Request::getClientIP());
        $this->aluOrder = new Order();
        $this->aluBilling = new Billing();
        $this->aluDelivery = new Delivery();



        $this->aluOrder->withOrderTimeout($timeOut);
        $this->aluOrder->withPayMethod($paymentMethod);

    }

    public function set3DSReturn($url)
    {
        $this->aluOrder->withBackRef($url);
    }

    public function setOrderRef($s)
    {
        $this->aluOrder->withOrderRef($s);
    }

    public function setCurrency($s)
    {
        $this->aluOrder->withCurrency($s);
    }

    public function setOrderDate($s)
    {
        $this->aluOrder->withOrderDate(date('Y-m-d H:i:s', strtotime($s)));
    }

   public function addProduct($code, $name, $price, $vat, $quantity)
   {
       $product = new Product();
       $product->withCode($code)
           ->withName($name)
           ->withPrice($price)
           ->withVAT($vat)
           ->withQuantity($quantity);
       $this->aluOrder->addProduct($product);

   }

   public function setBilling($address1, $address2, $city, $county, $email, $firstname, $lastname, $phone, $identityNumber)
   {
       $this->aluBilling->withAddressLine1($address1)
           ->withAddressLine2($address2)
           ->withCity($city)
           ->withCountryCode($county)
           ->withEmail($email)
           ->withFirstName($firstname)
           ->withLastName($lastname)
           ->withPhoneNumber($phone)
           ->withIdentityCardNumber($identityNumber);
   }

    public function setDelivery($address1, $address2, $city, $county, $email, $firstname, $lastname, $phone)
    {
        $this->aluDelivery->withAddressLine1($address1)
            ->withAddressLine2($address2)
            ->withCity($city)
            ->withCountryCode($county)
            ->withEmail($email)
            ->withFirstName($firstname)
            ->withLastName($lastname)
            ->withPhoneNumber($phone);
    }
    public function setCard($cardNumber, $cardExpMo, $cardExpYe, $cardCCV, $cardOwn)
    {
        $this->aluCard = new Card($cardNumber, $cardExpMo, $cardExpYe, $cardCCV, $cardOwn);
    }

    public function buy()
    {
        $this->aluRequest = new Request($this->aluCfg, $this->aluOrder, $this->aluBilling, $this->aluDelivery, $this->aluUser);
        $this->aluRequest->setCard($this->aluCard);

        $this->aluClient = new Client($this->aluCfg);
        try {

            $response = $this->aluClient->pay($this->aluRequest);

            if ($response->isThreeDs()) {
                return ['error' => false, 'threeDS' => $response->getThreeDsUrl()];
            }
            return ['error' => false, 'status' => $response->getStatus(), 'returnCode'=> $response->getReturnCode(), 'returnMessage'=> $response->getReturnMessage()];
        } catch (ConnectionException $exception) {
            return ['error' => true, 'returnMessage'=> $exception->getMessage()];
        } catch (ClientException $exception) {
            return ['error' => true, 'returnMessage'=> $exception->getErrorMessage()];
        }

    }

}
