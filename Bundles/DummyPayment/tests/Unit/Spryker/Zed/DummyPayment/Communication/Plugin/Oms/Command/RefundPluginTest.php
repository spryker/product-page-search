<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\DummyPayment\Communication\Plugin\Oms\Command;

use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Spryker\Zed\DummyPayment\Business\DummyPaymentFacade;
use Spryker\Zed\DummyPayment\Communication\Plugin\Oms\Command\RefundPlugin;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;

/**
 * @group Spryker
 * @group Zed
 * @group DummyPayment
 * @group Communication
 * @group RefundPlugin
 */
class RefundPluginTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testRunShouldDelegateToFacade()
    {
        $dummyPaymentFacadeMock = $this->getDummyPaymentFacadeMock();
        $refundPlugin = new RefundPlugin();
        $refundPlugin->setFacade($dummyPaymentFacadeMock);

        $refundPlugin->run([], new SpySalesOrder(), new ReadOnlyArrayObject());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\DummyPayment\Business\DummyPaymentFacadeInterface|\Spryker\Zed\Kernel\Business\AbstractFacade
     */
    protected function getDummyPaymentFacadeMock()
    {
        $dummyPaymentFacadeMock = $this->getMock(DummyPaymentFacade::class);
        $dummyPaymentFacadeMock->expects($this->once())->method('refund');

        return $dummyPaymentFacadeMock;
    }

}
