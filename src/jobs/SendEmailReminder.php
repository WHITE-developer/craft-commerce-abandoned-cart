<?php
/**
 * @copyright Copyright (c) Myles Derham.
 * @license https://craftcms.github.io/license/
 */

namespace mediabeastnz\abandonedcart\jobs;

use Craft;
use craft\queue\BaseJob;

use mediabeastnz\abandonedcart\AbandonedCart;

class SendEmailReminder extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var int
     */
    public $cartId;

    /**
     * @var int
     */
    public $reminder;

    protected function defaultDescription(): ?string
    {
        return Craft::t('abandoned-cart', 'Send abandoned cart reminder');
    }


    // Public Methods
    // =========================================================================

    public function execute($queue): void
    {
        $totalSteps = 1;
        for ($step = 0; $step < $totalSteps; $step++) { 
              
            $cart = AbandonedCart::$plugin->carts->getAbandonedCartById($this->cartId);

            $firstTemplate = Craft::parseEnv(AbandonedCart::$plugin->getSettings()->firstReminderTemplate);
            $secondTemplate = Craft::parseEnv(AbandonedCart::$plugin->getSettings()->secondReminderTemplate);
            $firstSubject = Craft::parseEnv(AbandonedCart::$plugin->getSettings()->firstReminderSubject);
            $secondSubject = Craft::parseEnv(AbandonedCart::$plugin->getSettings()->secondReminderSubject);
            $secondReminderDisabled = Craft::parseEnv(AbandonedCart::$plugin->getSettings()->disableSecondReminder);
            
            if ($cart && $cart->isRecovered == 0) {
                
                // First Reminder
                if ($this->reminder == 1) {
                    $cart->firstReminder = 1;
                    $cart->isScheduled = 0;
                    $cart->save($cart);

                    AbandonedCart::$plugin->carts->sendMail(
                        $cart, 
                        $firstSubject, 
                        $cart->email, 
                        $firstTemplate
                    );
                }

                // Second Reminder
                if ($this->reminder == 2) {
                    if ($secondReminderDisabled) {
                        $cart->secondReminder = 1;
                        $cart->isScheduled = 0;
                        $cart->save($cart);
                    } else {
                        $cart->secondReminder = 1;
                        $cart->isScheduled = 0;
                        $cart->save($cart);
                        
                        AbandonedCart::$plugin->carts->sendMail(
                            $cart, 
                            $secondSubject, 
                            $cart->email, 
                            $secondTemplate
                        );
                    }
                }
            }

            $this->setProgress($queue, $step / $totalSteps);
        }
    }
}