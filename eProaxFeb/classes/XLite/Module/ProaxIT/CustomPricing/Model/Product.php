<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomPricing\Model;

use XLite\Core\Exception;

class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{

    /**
     * Get clear price: this price can be overwritten by modules
     *
     * @return float
     */
    public function getClearPrice()
    {
        $profile = \XLite\Core\Auth::getInstance()->getProfile();
        if ($profile) {
            $ID = $profile->getCustomerId();
            if ($ID) {
                $dbPrice = $this->queryPrice($ID);
                $price = $dbPrice ? : $this->getPrice();
            } else {
                $price = $this->getPrice();
            }
        } else {
            $price = $this->getPrice();
        }

        return $price;
    }

    // Attempt at security by obscurity
    const a = "proaxselect", o = "xcart!123", u = "proax", e = "proax!xcart!", i = "proaxxcart";
    private $cred = [self::a, self::o, self::i, self::e, self::u];
    private $cachedPrice;


    // persistent connection
    private function dbInit() {
        if (! isset($GLOBALS[$this->cred[2]])) {
            $GLOBALS[$this->cred[2]] = odbc_pconnect($this->cred[4], $this->cred[2], $this->cred[0]);
            if (! $GLOBALS[$this->cred[2]] ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Queries SQL to return custom price
     *
     * @param $custID
     * @return int
     */
    private function queryPrice($custID)
    {
        if ($custID){
            $productSku = $this->getSku();
            if (isset($this->cachedPrice)) {
                $price = $this->cachedPrice;
            } elseif($this->dbInit()) {
                $res = odbc_exec($GLOBALS[$this->cred[2]], "sp_mall_itemprice '{$productSku}', '{$custID}'");

                $array = odbc_fetch_array($res);
                $price = $array["price"];
                $this->cachedPrice = $price;

                unset($array);
                odbc_free_result($res);
            } else {
                $price = $this->getPrice();
            }
            return $price ? : $this->getPrice();
        }  else {
            return $this->getPrice();
        }
    }
}