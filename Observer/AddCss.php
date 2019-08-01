<?php

namespace Adele\AddCss\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Customer\Model\Session;


class AddCss implements ObserverInterface
{
    protected $_assetRepo;
    protected $_publisher;
    protected $_customerSession;

    public function __construct(Repository $assetRepo,
                                Publisher $publisher,
                                Session $_customerSession
    ) {
        $this->_customerSession = $_customerSession;
        $this->_assetRepo = $assetRepo;
        $this->_publisher = $publisher;
    }

    /**
     * solution like https://magento.stackexchange.com/questions/235177/how-to-check-whether-the-customer-logged-in-using-events-observer
     * doesn't work with enables FPC. So, we use controller_front_send_response_before event
     */

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return;
        }
        $asset  = $this->_assetRepo->createAsset('css/add-css.css', ['module' => 'Adele_AddCss', 'area' => 'frontend']);
        $this->_publisher->publish($asset);
        $url    = $asset->getSourceUrl();
        $response = $observer->getEvent()->getData('response');
        $head = '<link  rel="stylesheet" type="text/css"  media="all" href="' . $url . '" />';
        $response->setBody(preg_replace('/<\/head>/', $head . '</head>', $response->getBody(), 1));
    }
}