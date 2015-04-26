<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Model\Payment;

/**
 * Payment transaction
 *
 * @Entity
 * @Table  (name="payment_transactions",
 *      indexes={
 *          @Index (name="status", columns={"status"}),
 *          @Index (name="o", columns={"order_id","status"}),
 *          @Index (name="pm", columns={"method_id","status"}),
 *          @Index (name="publicTxnId", columns={"publicTxnId"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class Transaction extends \XLite\Model\AEntity
{
    /**
     * Transaction status codes
     */

    const STATUS_INITIALIZED = 'I';
    const STATUS_INPROGRESS  = 'P';
    const STATUS_SUCCESS     = 'S';
    const STATUS_PENDING     = 'W';
    const STATUS_FAILED      = 'F';
    const STATUS_CANCELED    = 'C';
    const STATUS_VOID        = 'V';

    /**
     * Transaction initialization result
     */

    const PROLONGATION = 'R';
    const COMPLETED    = 'C';
    const SILENT       = 'S';
    const SEPARATE     = 'E';


    /**
     * Public token length
     */
    const PUBLIC_TOKEN_LENGTH = 16;

    /**
     * Token characters list
     *
     * @var array
     */
    protected static $chars = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
        'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
        'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z', '_',
    );

    /**
     * Primary key
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $transaction_id;

    /**
     * Transaction creation timestamp
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $date;

    /**
     * Public transaction id
     *
     * @var string
     *
     * @Column (type="string", length=16, nullable=true)
     */
    protected $publicTxnId;

    /**
     * Payment method name
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $method_name;

    /**
     * Payment method localized name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $method_local_name = '';

    /**
     * Status
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $status = self::STATUS_INITIALIZED;

    /**
     * Transaction value
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $value = 0.0000;

    /**
     * Customer message
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $note = '';

    /**
     * Transaction type
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE;

    /**
     * Public transaction ID
     *
     * @var string
     *
     * @Column (type="string")
     */
    protected $public_id = '';

    /**
     * Order
     *
     * @var \XLite\Model\Order
     *
     * @ManyToOne  (targetEntity="XLite\Model\Order", inversedBy="payment_transactions")
     * @JoinColumn (name="order_id", referencedColumnName="order_id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * Payment method
     *
     * @var \XLite\Model\Payment\Method
     *
     * @ManyToOne  (targetEntity="XLite\Model\Payment\Method")
     * @JoinColumn (name="method_id", referencedColumnName="method_id", onDelete="SET NULL")
     */
    protected $payment_method;

    /**
     * Transaction data
     *
     * @var \XLite\Model\Payment\TransactionData
     *
     * @OneToMany (targetEntity="XLite\Model\Payment\TransactionData", mappedBy="transaction", cascade={"all"})
     */
    protected $data;

    /**
     * Related backend transactions
     *
     * @var \XLite\Model\Payment\BackendTransaction
     *
     * @OneToMany (targetEntity="XLite\Model\Payment\BackendTransaction", mappedBy="payment_transaction", cascade={"all"})
     */
    protected $backend_transactions;

    /**
     * Currency
     *
     * @var \XLite\Model\Currency
     *
     * @ManyToOne  (targetEntity="XLite\Model\Currency", cascade={"merge","detach"})
     * @JoinColumn (name="currency_id", referencedColumnName="currency_id")
     */
    protected $currency;

    /**
     * Readable statuses
     *
     * @var array
     */
    protected $readableStatuses = array(
        self::STATUS_INITIALIZED => 'Initialized',
        self::STATUS_INPROGRESS  => 'In progress',
        self::STATUS_SUCCESS     => 'Completed',
        self::STATUS_PENDING     => 'Pending',
        self::STATUS_FAILED      => 'Failed',
        self::STATUS_CANCELED    => 'Canceled',
        self::STATUS_VOID        => 'Voided',
    );

    /**
     * Get statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return array(
            static::STATUS_INITIALIZED => 'Initialized',
            static::STATUS_INPROGRESS  => 'In progress',
            static::STATUS_SUCCESS     => 'Success',
            static::STATUS_PENDING     => 'Pending',
            static::STATUS_FAILED      => 'Faild',
            static::STATUS_CANCELED    => 'Canceled',
            static::STATUS_VOID        => 'Void',
        );
    }

    /**
     * Get profile
     *
     * @return \XLite\Model\Profile
     */
    public function getProfile()
    {
        return $this->getOrder() ? $this->getOrder()->getProfile() : null;
    }

    /**
     * Get original profile
     *
     * @return \XLite\Model\Profile
     */
    public function getOrigProfile()
    {
        $profile = $this->getOrder() ? $this->getOrder()->getOrigProfile() : null;

        return $profile ?: $this->getProfile();
    }

    /**
     * Prepare creation date
     *
     * @return void
     *
     * @PrePersist
     */
    public function prepareBeforeCreate()
    {
        if (!$this->getPublicTxnId()) {
            $this->setPublicTxnId(
                \XLite\Core\Operator::getInstance()->generateToken(static::PUBLIC_TOKEN_LENGTH, static::$chars)
            );
        }

        if (!$this->getDate()) {
            $this->setDate(\XLite\Core\Converter::time());
        }

        if (!$this->getPublicId()) {
            if ($this->getPaymentMethod()) {
                $this->renewTransactionId();

            } else {
                $this->setPublicId($this->getPublicTxnId());
            }
        }
    }

    /**
     * Renew transaction ID
     *
     * @return void
     */
    public function renewTransactionId()
    {
        if (!$this->getPublicTxnId()) {
            $this->setPublicTxnId(
                \XLite\Core\Operator::getInstance()->generateToken(static::PUBLIC_TOKEN_LENGTH, static::$chars)
            );
        }

        $this->setPublicId(
            $this->getPaymentMethod()->getProcessor()->generateTransactionId($this)
        );
    }

    /**
     * Set transaction value
     *
     * @param float $value Transaction value
     *
     * @return \XLite\Model\Payment\Transaction
     */
    public function setValue($value)
    {
        $this->value = $this->getOrder() ? $this->getOrder()->getCurrency()->roundValue($value) : $value;

        return $this;
    }

    /**
     * Set payment method
     *
     * @param \XLite\Model\Payment\Method $method Payment method OPTIONAL
     *
     * @return void
     */
    public function setPaymentMethod(\XLite\Model\Payment\Method $method = null)
    {
        $this->payment_method = $method;

        if ($method) {
            $this->setMethodName($method->getServiceName());
            $this->setMethodLocalName($method->getName());
            $this->renewTransactionId();
        }
    }

    /**
     * Update value
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return \XLite\Model\Payment\Transaction
     */
    public function updateValue(\XLite\Model\Order $order)
    {
        return $this->setValue($order->getOpenTotal());
    }

    /**
     * Process checkout action
     *
     * @return string
     */
    public function handleCheckoutAction()
    {
        $this->setStatus(self::STATUS_INPROGRESS);
        \XLite\Core\Database::getEM()->flush();

        $data = is_array(\XLite\Core\Request::getInstance()->payment)
            ? \XLite\Core\Request::getInstance()->payment
            : array();

        $result = $this->getPaymentMethod()->getProcessor()->pay($this, $data);

        $return = self::COMPLETED;

        switch ($result) {
            case \XLite\Model\Payment\Base\Processor::PROLONGATION:
                $return = self::PROLONGATION;
                break;

            case \XLite\Model\Payment\Base\Processor::SILENT:
                $return = self::SILENT;
                break;

            case \XLite\Model\Payment\Base\Processor::SEPARATE:
                $return = self::SEPARATE;
                break;

            case \XLite\Model\Payment\Base\Processor::COMPLETED:
                $this->setStatus(self::STATUS_SUCCESS);
                break;

            case \XLite\Model\Payment\Base\Processor::PENDING:
                $this->setStatus(self::STATUS_PENDING);
                break;

            default:
                $this->setStatus(self::STATUS_FAILED);
        }

        $this->registerTransactionInOrderHistory();

        return $return;
    }

    /**
     * Get charge value modifier
     *
     * @return float
     */
    public function getChargeValueModifier()
    {
        $value = 0;

        if ($this->isCompleted() || $this->isPending()) {
            $value += $this->getValue();
        }

        if ($this->getBackendTransactions()) {
            foreach ($this->getBackendTransactions() as $transaction) {
                if ($transaction->isRefund() && $transaction->isSucceed()) {
                    $value -= abs($transaction->getValue());
                }
            }
        }

        return max(0, $value);
    }

    /**
     * Check - transaction is open or not
     *
     * @return boolean
     */
    public function isOpen()
    {
        return static::STATUS_INITIALIZED == $this->getStatus();
    }

    /**
     * Check - transaction is canceled or not
     *
     * @return boolean
     */
    public function isCanceled()
    {
        return static::STATUS_CANCELED == $this->getStatus();
    }

    /**
     * Check - transaction is failed or not
     *
     * @return boolean
     */
    public function isFailed()
    {
        return static::STATUS_FAILED == $this->getStatus();
    }

    /**
     * Check - order is completed or not
     *
     * @return boolean
     */
    public function isCompleted()
    {
        return static::STATUS_SUCCESS == $this->getStatus();
    }

    /**
     * Check - order is in progress state or not
     *
     * @return boolean
     */
    public function isInProgress()
    {
        return static::STATUS_INPROGRESS == $this->getStatus();
    }

    /**
     * Return true if transaction is in PENDING status
     *
     * @return boolean
     */
    public function isPending()
    {
        return static::STATUS_PENDING == $this->getStatus();
    }

    /**
     * Return true if transaction is in VOID status
     *
     * @return boolean
     */
    public function isVoid()
    {
        return static::STATUS_VOID == $this->getStatus();
    }

    /**
     * Returns true if successful payment has type AUTH
     *
     * @return boolean
     */
    public function isAuthorized()
    {
        $result = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH == $this->getType() && $this->isCompleted();

        if ($result && $this->getBackendTransactions()) {
            foreach ($this->getBackendTransactions() as $transaction) {
                if (
                    $transaction->isVoid()
                    && $transaction->isSucceed()
                ) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Returns true if successful payment has type SALE or has successful CAPTURE transaction
     *
     * @return boolean
     */
    public function isCaptured()
    {
        $result = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE == $this->getType() && $this->isCompleted();

        if ($this->getBackendTransactions()) {

            foreach ($this->getBackendTransactions() as $transaction) {
                if (
                    $transaction->isCapture()
                    && $transaction->isSucceed()
                ) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns true if payment has successful REFUND transaction
     *
     * @return boolean
     */
    public function isRefunded()
    {
        $result = false;

        if ($this->getBackendTransactions()) {
            foreach ($this->getBackendTransactions() as $transaction) {
                if (
                    $transaction->isRefund()
                    && $transaction->isSucceed()
                ) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns true if CAPTURE transaction is allowed for this payment
     *
     * @return boolean
     */
    public function isCaptureTransactionAllowed()
    {
        return $this->isAuthorized() && !$this->isCaptured() && !$this->isRefunded();
    }

    /**
     * Returns true if VOID transaction is allowed for this payment
     *
     * @return boolean
     */
    public function isVoidTransactionAllowed()
    {
        return $this->isCaptureTransactionAllowed();
    }

    /**
     * Returns true if REFUND transaction is allowed for this payment
     *
     * @return boolean
     */
    public function isRefundTransactionAllowed()
    {
        return $this->isCaptured() && !$this->isRefunded();
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->data = new \Doctrine\Common\Collections\ArrayCollection();
        $this->backend_transactions = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get human-readable status
     *
     * @param string $status Transaction status
     *
     * @return string
     */
    public function getReadableStatus($status = null)
    {
        if (!isset($status)) {
            $status = $this->getStatus();
        }

        return isset($this->readableStatuses[$status])
            ? $this->readableStatuses[$status]
            : 'Unknown (' . $status . ')';
    }

    // {{{ Data operations

    /**
     * Set data cell
     *
     * @param string $name  Data cell name
     * @param string $value Value
     * @param string $label Public name OPTIONAL
     * @param string $accessLevel access level OPTIONAL
     *
     * @return void
     */
    public function setDataCell($name, $value, $label = null, $accessLevel = null)
    {
        $data = null;

        foreach ($this->getData() as $cell) {
            if ($cell->getName() == $name) {
                $data = $cell;
                break;
            }
        }

        if (!$data) {
            $data = new \XLite\Model\Payment\TransactionData;
            $data->setName($name);
            $this->addData($data);
            $data->setTransaction($this);
        }

        if (!$data->getLabel() && $label) {
            $data->setLabel($label);
        }

        $data->setValue($value);

        // If access level was specified, and it dosn't match original one
        // Then update it
        if (
            $accessLevel
            && $data->getAccessLevel() != $accessLevel
        ) {
            $data->setAccessLevel($accessLevel);
        }
    }

    /**
     * Get data cell object by name
     *
     * @param string $name Name of data cell
     *
     * @return \XLite\Model\Payment\TransactionData
     */
    public function getDataCell($name)
    {
        $value = null;

        foreach ($this->getData() as $cell) {
            if ($cell->getName() == $name) {
                if (
                    \XLite::isAdminZone()
                    || (!\XLite::isAdminZone() && $cell->getAccessLevel() != 'A')
                ) {
                    $value = $cell;
                    break;
                }
            }
        }

        // TODO: Consider situations if cells with same names have different access levels

        return $value;
    }

    // }}}

    /**
     * Create backend transaction
     *
     * @param string $transactionType Type of backend transaction
     *
     * @return \XLite\Model\Payment\BackendTransaction
     */
    public function createBackendTransaction($transactionType)
    {
        $data = array(
            'date'                => \XLite\Core\Converter::time(),
            'type'                => $transactionType,
            'value'               => $this->getValue(),
            'payment_transaction' => $this,
        );

        $bt = \XLite\Core\Database::getRepo('XLite\Model\Payment\BackendTransaction')->insert($data, false);

        $this->addBackendTransactions($bt);

        return $bt;
    }

    /**
     * Get initial backend transaction (related to the first payment transaction)
     *
     * @return \XLite\Model\Payment\BackendTransaction
     */
    public function getInitialBackendTransaction()
    {
        $bt = null;

        foreach ($this->getBackendTransactions() as $transaction) {
            if ($transaction->isInitial()) {
                $bt = $transaction;
                break;
            }
        }

        return $bt;
    }

    /**
     * Register transaction in order history
     *
     * @param string $suffix Suffix text to add to the end of event description
     *
     * @return void
     */
    public function registerTransactionInOrderHistory($suffix = null)
    {
        $descrSuffix = !empty($suffix) ? ' [' . static::t($suffix) . ']' : '';

        \XLite\Core\OrderHistory::getInstance()->registerTransaction(
            $this->getOrder()->getOrderId(),
            static::t($this->getHistoryEventDescription(), $this->getHistoryEventDescriptionData()) . $descrSuffix,
            $this->getEventData()
        );
    }

    /**
     * Get description of order history event (language label is returned)
     *
     * @return string
     */
    public function getHistoryEventDescription()
    {
        return 'Payment transaction X issued';
    }

    /**
     * Get data for description of order history event (substitution data for language label is returned)
     *
     * @return return
     */
    public function getHistoryEventDescriptionData()
    {
        return array(
            'trx_method' => static::t($this->getPaymentMethod()->getName()),
            'trx_type'   => static::t($this->getType()),
            'trx_value'  => $this->getOrder()->getCurrency()->roundValue($this->getValue()),
            'trx_status' => static::t($this->getReadableStatus()),
        );
    }

    /**
     * getEventData
     *
     * @return void
     */
    public function getEventData()
    {
        $result = array();

        foreach ($this->getData() as $cell) {
            $result[] = array(
                'name'  => $cell->getLabel() ?: $cell->getName(),
                'value' => $cell->getValue()
            );
        }

        return $result;
    }

    /**
     * Check - transaction's method and specified method is equal or not
     *
     * @param \XLite\Model\Payment\Method $method Anothermethod
     *
     * @return boolean
     */
    public function isSameMethod(\XLite\Model\Payment\Method $method)
    {
        return $this->getPaymentMethod() && $this->getPaymentMethod()->getMethodId() == $method->getMethodId();
    }

    /**
     * Clone payment transaction
     *
     * @return \XLite\Model\Payment\Transaction
     */
    public function cloneEntity()
    {
        $newTransaction = parent::cloneEntity();

        $newTransaction->setCurrency($this->getCurrency());
        $newTransaction->setOrder($this->getOrder());
        $newTransaction->setPaymentMethod($this->getPaymentMethod());

        return $newTransaction;
    }
}
