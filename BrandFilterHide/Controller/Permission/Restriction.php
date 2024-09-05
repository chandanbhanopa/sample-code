<?php
namespace Helm\BrandFilterHide\Controller\Permission;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Restriction implements ActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $httpContext
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $httpContext,
        PageFactory $pageFactory
        )
    {
        $this->httpContext   = $httpContext;
        $this->pageFactory   = $pageFactory;
    }
    /**
     * @return json
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}